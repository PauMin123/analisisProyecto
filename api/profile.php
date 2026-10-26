<?php
/**
 * ============================================================================
 * API: Perfil del Estudiante (Pantalla 6)
 * Endpoints:
 *   - GET  ?action=get&student_id=X  (Obtener datos completos del perfil)
 *   - POST action=update             (Actualizar nombre, teléfono y preferencias)
 * ============================================================================
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config.php';

try {
    $db = DatabaseConnection::getInstance();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error de conexión a la base de datos'
    ]);
    exit();
}

$rawInput = file_get_contents('php://input');
$body = json_decode($rawInput, true) ?? [];
$action = $_GET['action'] ?? ($body['action'] ?? ($_POST['action'] ?? 'get'));

switch ($action) {
    /**
     * Obtener Información Completa del Perfil del Estudiante
     */
    case 'get':
        $studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : (int)($body['student_id'] ?? 0);
        $matricula = trim($_GET['matricula'] ?? ($body['matricula'] ?? ''));

        if ($studentId <= 0 && empty($matricula)) {
            // Tomar el primer estudiante si no se envía parámetro
            $stmtFirst = $pdo->query("SELECT id FROM estudiantes ORDER BY id ASC LIMIT 1");
            $studentId = (int)$stmtFirst->fetchColumn() ?: 1;
        }

        try {
            if ($studentId > 0) {
                $stmt = $pdo->prepare("SELECT id, uuid, username, nombre, email, nivel_actual, telefono, fecha_registro FROM estudiantes WHERE id = ? LIMIT 1");
                $stmt->execute([$studentId]);
            } else {
                $stmt = $pdo->prepare("SELECT id, uuid, username, nombre, email, nivel_actual, telefono, fecha_registro FROM estudiantes WHERE username = ? OR email = ? LIMIT 1");
                $stmt->execute([$matricula, $matricula]);
            }

            $student = $stmt->fetch();

            if (!$student) {
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado']);
                exit();
            }

            $student['matricula'] = $student['username'];

            // Estadísticas de Lecciones
            $stmtStats = $pdo->prepare("
                SELECT 
                    COUNT(CASE WHEN estado = 'COMPLETADA' THEN 1 END) AS completadas,
                    COUNT(CASE WHEN estado = 'PROGRAMADA' OR estado = 'EN_CURSO' THEN 1 END) AS pendientes,
                    COUNT(CASE WHEN estado = 'CANCELADA' THEN 1 END) AS canceladas,
                    COUNT(*) AS total_reservas
                FROM lecciones
                WHERE estudiante_id = ?
            ");
            $stmtStats->execute([$student['id']]);
            $statsRow = $stmtStats->fetch() ?: [];

            $completadas = (int)($statsRow['completadas'] ?? 0);
            $pendientes = (int)($statsRow['pendientes'] ?? 0);
            $canceladas = (int)($statsRow['canceladas'] ?? 0);
            $horasAcumuladas = $completadas * 1; // 1 hora por lección

            // Metadatos pedagógicos del nivel CEFR
            $nivel = $student['nivel_actual'] ?: 'A1';
            $cefrData = [
                'A1' => ['nombre' => 'Principiante', 'progreso' => 20, 'siguiente' => 'A2 Básico', 'descripcion' => 'Comprensión de frases cotidianas básicas y vocabulario esencial.'],
                'A2' => ['nombre' => 'Básico', 'progreso' => 40, 'siguiente' => 'B1 Intermedio', 'descripcion' => 'Comunicación en situaciones sencillas y expresión de necesidades inmediatas.'],
                'B1' => ['nombre' => 'Intermedio', 'progreso' => 60, 'siguiente' => 'B2 Intermedio Alto', 'descripcion' => 'Desenvolvimiento en viajes, temas laborales y conversaciones fluidas.'],
                'B2' => ['nombre' => 'Avanzado', 'progreso' => 80, 'siguiente' => 'C1 Fluido Nativo', 'descripcion' => 'Fluidez y espontaneidad natural con hablantes nativos.'],
                'C1' => ['nombre' => 'Dominio Operativo Eficaz', 'progreso' => 100, 'siguiente' => 'C2 Maestría', 'descripcion' => 'Uso flexible y efectivo del inglés en ámbitos sociales, académicos y profesionales.']
            ];

            $cefr = $cefrData[$nivel] ?? $cefrData['A1'];

            echo json_encode([
                'success' => true,
                'student' => [
                    'id' => (int)$student['id'],
                    'uuid' => $student['uuid'],
                    'nombre' => $student['nombre'],
                    'username' => $student['username'],
                    'matricula' => $student['matricula'],
                    'email' => $student['email'],
                    'telefono' => $student['telefono'] ?: '',
                    'nivel_actual' => $nivel,
                    'fecha_registro' => $student['fecha_registro']
                ],
                'nivel_info' => [
                    'codigo' => $nivel,
                    'nombre' => $cefr['nombre'],
                    'progreso_porcentaje' => $cefr['progreso'],
                    'siguiente_nivel' => $cefr['siguiente'],
                    'descripcion' => $cefr['descripcion']
                ],
                'estadisticas' => [
                    'clases_completadas' => $completadas,
                    'horas_acumuladas' => $horasAcumuladas,
                    'clases_pendientes' => $pendientes,
                    'clases_canceladas' => $canceladas,
                    'total_reservas' => (int)($statsRow['total_reservas'] ?? 0)
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al obtener perfil: ' . $e->getMessage()]);
        }
        break;

    /**
     * Actualizar Información Personal del Estudiante (Nombre, Teléfono)
     */
    case 'update':
        if (empty($body)) {
            $body = $_POST;
        }

        $studentId = isset($body['student_id']) ? (int)$body['student_id'] : 0;
        $nombre = trim($body['nombre'] ?? '');
        $telefono = trim($body['telefono'] ?? '');

        if ($studentId <= 0) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'ID de estudiante no válido.']);
            exit();
        }

        if (empty($nombre)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El nombre completo es obligatorio.']);
            exit();
        }

        if (strlen($nombre) < 3) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.']);
            exit();
        }

        try {
            $stmtUpdate = $pdo->prepare("
                UPDATE estudiantes 
                SET nombre = ?, telefono = ? 
                WHERE id = ?
            ");
            $stmtUpdate->execute([$nombre, $telefono, $studentId]);

            // Recuperar registro actualizado
            $stmtFresh = $pdo->prepare("SELECT id, uuid, username, nombre, email, nivel_actual, telefono, fecha_registro FROM estudiantes WHERE id = ?");
            $stmtFresh->execute([$studentId]);
            $updatedStudent = $stmtFresh->fetch();
            $updatedStudent['matricula'] = $updatedStudent['username'];

            echo json_encode([
                'success' => true,
                'message' => '¡Perfil actualizado correctamente!',
                'student' => $updatedStudent
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error al actualizar perfil: ' . $e->getMessage()]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida en api/profile.php']);
        break;
}
