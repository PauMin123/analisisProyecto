<?php
/**
 * ============================================================================
 * MODELO: Nivel (MVC: Capa del Modelo)
 * Representa los niveles pedagógicos del MCER (A1 a C1)
 * ============================================================================
 */

require_once __DIR__ . '/../api/config.php';

class Nivel {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? DatabaseConnection::getInstance()->getConnection();
    }

    /**
     * Retorna todos los niveles disponibles
     */
    public function getAll(): array {
        $stmt = $this->pdo->query("
            SELECT id, curso_id, codigo, nombre, descripcion 
            FROM niveles 
            ORDER BY curso_id ASC, id ASC
        ");
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Retorna niveles asociados a un curso específico
     */
    public function getByCourseId(int $courseId): array {
        $stmt = $this->pdo->prepare("
            SELECT id, curso_id, codigo, nombre, descripcion 
            FROM niveles 
            WHERE curso_id = ? 
            ORDER BY id ASC
        ");
        $stmt->execute([$courseId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Busca nivel por ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id, curso_id, codigo, nombre, descripcion 
            FROM niveles 
            WHERE id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
