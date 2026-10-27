<?php
/**
 * ============================================================================
 * MODELO: Estudiante (MVC: Capa del Modelo)
 * Gestiona la identidad, credenciales, perfil y estadísticas del estudiante
 * ============================================================================
 */

require_once __DIR__ . '/../api/config.php';

class Estudiante {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? DatabaseConnection::getInstance()->getConnection();
    }

    /**
     * Busca estudiante por matrícula, email o nombre de usuario
     */
    public function findByIdentifier(string $identifier): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id, uuid, username, nombre, email, password_hash, nivel_actual, telefono, fecha_registro
            FROM estudiantes
            WHERE LOWER(email) = LOWER(?) OR LOWER(username) = LOWER(?) OR LOWER(uuid) = LOWER(?)
            LIMIT 1
        ");
        $stmt->execute([$identifier, $identifier, $identifier]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Busca por ID de base de datos
     */
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id, uuid, username, nombre, email, password_hash, nivel_actual, telefono, fecha_registro
            FROM estudiantes
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Retorna el primer estudiante (para modo demostración / fallback)
     */
    public function getFirstStudent(): ?array {
        $stmt = $this->pdo->query("
            SELECT id, uuid, username, nombre, email, password_hash, nivel_actual, telefono, fecha_registro
            FROM estudiantes
            ORDER BY id ASC
            LIMIT 1
        ");
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * Autentica estudiante comparando credenciales
     */
    public function authenticate(string $identifier, string $password): ?array {
        $student = $this->findByIdentifier($identifier);
        if (!$student) {
            return null;
        }

        // Si la contraseña coincide con password_verify o con el password por defecto de pruebas
        if (!empty($student['password_hash']) && password_verify($password, $student['password_hash'])) {
            return $student;
        }

        // Claves maestras de prueba académica
        if ($password === 'Estudiante2026*' || $password === 'admin123' || $password === 'demo123') {
            return $student;
        }

        return null;
    }

    /**
     * Registra un nuevo estudiante generando automáticamente matrícula institucional y correo
     */
    public function register(string $nombre, string $password, string $telefono = '', string $aliasInput = ''): array {
        // 1. Generación automática de matrícula institucional (EMD-2026-XXXX)
        $matricula = '';
        $uuid = '';
        do {
            $num = rand(1001, 9999);
            $matricula = "EMD-2026-" . $num;
            $uuid = "STU-" . $num;
            $checkMat = $this->pdo->prepare("SELECT id FROM estudiantes WHERE username = ? OR uuid = ? LIMIT 1");
            $checkMat->execute([$matricula, $uuid]);
        } while ($checkMat->fetch());

        // 2. Asignación de correo con dominio @englishmade.edu
        $baseAlias = '';
        if (!empty($aliasInput)) {
            $baseAlias = strtolower(explode('@', $aliasInput)[0]);
        } else {
            $partes = preg_split('/\s+/', strtolower($nombre));
            if (count($partes) >= 2) {
                $baseAlias = substr($partes[0], 0, 1) . preg_replace('/[^a-z0-9]/', '', $partes[count($partes) - 1]);
            } else {
                $baseAlias = preg_replace('/[^a-z0-9]/', '', $partes[0]);
            }
        }
        $baseAlias = preg_replace('/[^a-z0-9._-]/', '', $baseAlias);
        if (empty($baseAlias) || strlen($baseAlias) < 2) {
            $baseAlias = 'student' . rand(100, 999);
        }

        $institutionalEmail = $baseAlias . '@englishmade.edu';
        $checkEmail = $this->pdo->prepare("SELECT id FROM estudiantes WHERE LOWER(email) = LOWER(?) LIMIT 1");
        $checkEmail->execute([$institutionalEmail]);
        if ($checkEmail->fetch()) {
            $institutionalEmail = $baseAlias . rand(10, 99) . '@englishmade.edu';
        }

        // 3. Insertar registro
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $insertStmt = $this->pdo->prepare("
            INSERT INTO estudiantes (uuid, username, nombre, email, password_hash, nivel_actual, telefono)
            VALUES (?, ?, ?, ?, ?, 'A1', ?)
        ");
        $insertStmt->execute([$uuid, $matricula, $nombre, $institutionalEmail, $hash, $telefono]);
        $newId = (int)$this->pdo->lastInsertId();

        return [
            'id' => $newId,
            'uuid' => $uuid,
            'username' => $matricula,
            'matricula' => $matricula,
            'nombre' => $nombre,
            'email' => $institutionalEmail,
            'nivel_actual' => 'A1',
            'telefono' => $telefono
        ];
    }

    /**
     * Actualiza el perfil del estudiante
     */
    public function updateProfile(int $id, string $nombre, string $telefono = ''): bool {
        $stmt = $this->pdo->prepare("
            UPDATE estudiantes
            SET nombre = ?, telefono = ?
            WHERE id = ?
        ");
        return $stmt->execute([$nombre, $telefono, $id]);
    }

    /**
     * Obtiene contadores y estadísticas de progreso
     */
    public function getStats(int $studentId): array {
        $stmt = $this->pdo->prepare("
            SELECT 
                SUM(CASE WHEN estado = 'COMPLETADA' THEN 1 ELSE 0 END) AS completadas,
                SUM(CASE WHEN estado = 'PROGRAMADA' THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN estado = 'CANCELADA' THEN 1 ELSE 0 END) AS canceladas,
                COUNT(*) AS total
            FROM lecciones 
            WHERE estudiante_id = ?
        ");
        $stmt->execute([$studentId]);
        $counts = $stmt->fetch() ?: [];

        $completadas = (int)($counts['completadas'] ?? 0);
        $pendientes = (int)($counts['pendientes'] ?? 0);
        $canceladas = (int)($counts['canceladas'] ?? 0);
        $horasAcumuladas = round($completadas * 1.0, 1);

        return [
            'lecciones_completadas' => $completadas,
            'horas_acumuladas' => $horasAcumuladas,
            'lecciones_pendientes' => $pendientes,
            'lecciones_canceladas' => $canceladas,
            'total_reservas' => (int)($counts['total'] ?? 0)
        ];
    }

    /**
     * Actualiza el nivel pedagógico oficial del estudiante (A1 -> A2 -> B1 -> etc.)
     */
    public function updateLevel(int $id, string $nuevoNivel): bool {
        $validLevels = ['A1', 'A2', 'B1', 'B2', 'C1'];
        if (!in_array($nuevoNivel, $validLevels, true)) {
            return false;
        }
        $stmt = $this->pdo->prepare("UPDATE estudiantes SET nivel_actual = ? WHERE id = ?");
        return $stmt->execute([$nuevoNivel, $id]);
    }
}
