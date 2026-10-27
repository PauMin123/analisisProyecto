<?php
/**
 * ============================================================================
 * MODELO: Profesor (MVC: Capa del Modelo)
 * Representa a los docentes certificados de la academia y sus especialidades
 * ============================================================================
 */

require_once __DIR__ . '/../api/config.php';

class Profesor {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? DatabaseConnection::getInstance()->getConnection();
    }

    /**
     * Retorna todos los profesores activos con los IDs de cursos asociados y acento
     */
    public function getAllWithCourses(): array {
        $stmt = $this->pdo->query("
            SELECT 
                p.id, p.uuid, p.nombre, p.email, p.especialidad, p.acento, p.biografia, p.avatar_url, p.rating,
                GROUP_CONCAT(pc.curso_id) AS cursos_ids
            FROM profesores p
            LEFT JOIN profesor_cursos pc ON p.id = pc.profesor_id
            WHERE p.activo = 1
            GROUP BY p.id
            ORDER BY p.rating DESC
        ");
        $raw = $stmt->fetchAll() ?: [];

        return array_map(function($prof) {
            $cursosArr = !empty($prof['cursos_ids']) ? array_map('intval', explode(',', $prof['cursos_ids'])) : [];
            unset($prof['cursos_ids']);
            $prof['cursos_asociados'] = $cursosArr;
            $prof['rating'] = (float)$prof['rating'];
            return $prof;
        }, $raw);
    }

    /**
     * Retorna profesores que imparten un curso específico
     */
    public function getByCourseId(int $courseId): array {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.uuid, p.nombre, p.email, p.especialidad, p.acento, p.biografia, p.avatar_url, p.rating
            FROM profesores p
            INNER JOIN profesor_cursos pc ON p.id = pc.profesor_id
            WHERE pc.curso_id = ? AND p.activo = 1
            ORDER BY p.rating DESC
        ");
        $stmt->execute([$courseId]);
        $rows = $stmt->fetchAll() ?: [];
        return array_map(function($prof) {
            $prof['rating'] = (float)$prof['rating'];
            return $prof;
        }, $rows);
    }

    /**
     * Busca profesor por ID
     */
    public function findById(int $id): ?array {
        $stmt = $this->pdo->prepare("
            SELECT p.id, p.uuid, p.nombre, p.email, p.especialidad, p.acento, p.biografia, p.avatar_url, p.rating
            FROM profesores p
            WHERE p.id = ? AND p.activo = 1
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['rating'] = (float)$row['rating'];
            return $row;
        }
        return null;
    }
}
