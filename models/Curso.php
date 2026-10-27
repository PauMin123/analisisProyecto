<?php
/**
 * ============================================================================
 * MODELO: Curso (MVC: Capa del Modelo)
 * Representa los programas académicos de la academia de inglés
 * ============================================================================
 */

require_once __DIR__ . '/../api/config.php';

class Curso {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? DatabaseConnection::getInstance()->getConnection();
    }

    /**
     * Retorna todos los cursos activos ordenados por ID
     */
    public function getActiveCourses(): array {
        $stmt = $this->pdo->query("
            SELECT id, codigo, nombre, descripcion, icono 
            FROM cursos 
            WHERE activo = 1 
            ORDER BY id ASC
        ");
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Busca curso por ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT id, codigo, nombre, descripcion, icono 
            FROM cursos 
            WHERE id = ? AND activo = 1
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }
}
