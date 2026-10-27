<?php
/**
 * ============================================================================
 * MODELO: Leccion (MVC: Capa del Modelo)
 * Gestiona el ciclo de vida de las lecciones, reservas y cancelaciones atómicas
 * ============================================================================
 */

require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/Horario.php';

class Leccion {
    private PDO $pdo;

    public function __construct(?PDO $pdo = null) {
        $this->pdo = $pdo ?? DatabaseConnection::getInstance()->getConnection();
    }

    /**
     * Obtiene la próxima lección programada para el estudiante
     */
    public function getNextLesson(int $studentId): ?array {
        $stmt = $this->pdo->prepare("
            SELECT 
                l.id, l.uuid, l.fecha, l.hora_inicio, l.hora_fin, l.estado, l.enlace_sesion,
                p.nombre AS profesor_nombre, p.especialidad AS profesor_especialidad, p.rating AS profesor_rating,
                c.nombre AS curso_nombre, c.codigo AS curso_codigo,
                n.codigo AS nivel_codigo, n.nombre AS nivel_nombre
            FROM lecciones l
            INNER JOIN profesores p ON l.profesor_id = p.id
            INNER JOIN cursos c ON l.curso_id = c.id
            INNER JOIN niveles n ON l.nivel_id = n.id
            WHERE l.estudiante_id = ? AND l.estado IN ('PROGRAMADA', 'EN_CURSO')
            ORDER BY l.fecha ASC, l.hora_inicio ASC
            LIMIT 1
        ");
        $stmt->execute([$studentId]);
        $row = $stmt->fetch();

        if ($row) {
            return [
                'id' => (int)$row['id'],
                'uuid' => $row['uuid'],
                'fecha' => $row['fecha'],
                'hora_inicio' => substr($row['hora_inicio'], 0, 5),
                'hora_fin' => substr($row['hora_fin'], 0, 5),
                'profesor' => $row['profesor_nombre'],
                'especialidad' => $row['profesor_especialidad'],
                'rating' => (float)$row['profesor_rating'],
                'curso' => $row['curso_nombre'],
                'curso_codigo' => $row['curso_codigo'],
                'nivel' => "{$row['nivel_codigo']} - {$row['nivel_nombre']}",
                'enlace_sesion' => $row['enlace_sesion'] ?? 'https://meet.google.com/emd-live-room',
                'estado' => $row['estado']
            ];
        }

        return null;
    }

    /**
     * Retorna todas las lecciones próximas del estudiante
     */
    public function getUpcoming(int $studentId): array {
        $stmt = $this->pdo->prepare("
            SELECT 
                l.id, l.uuid, l.fecha, l.hora_inicio, l.hora_fin, l.estado, l.enlace_sesion, l.creado_en,
                p.id AS profesor_id, p.nombre AS profesor_nombre, p.especialidad AS profesor_especialidad, p.rating AS profesor_rating,
                c.id AS curso_id, c.nombre AS curso_nombre, c.codigo AS curso_codigo, c.icono AS curso_icono,
                n.codigo AS nivel_codigo, n.nombre AS nivel_nombre
            FROM lecciones l
            INNER JOIN profesores p ON l.profesor_id = p.id
            INNER JOIN cursos c ON l.curso_id = c.id
            INNER JOIN niveles n ON l.nivel_id = n.id
            WHERE l.estudiante_id = ? AND l.estado IN ('PROGRAMADA', 'EN_CURSO')
            ORDER BY l.fecha ASC, l.hora_inicio ASC
        ");
        $stmt->execute([$studentId]);
        return $this->formatLessonsList($stmt->fetchAll() ?: []);
    }

    /**
     * Retorna historial de lecciones pasadas o canceladas
     */
    public function getHistory(int $studentId): array {
        $stmt = $this->pdo->prepare("
            SELECT 
                l.id, l.uuid, l.fecha, l.hora_inicio, l.hora_fin, l.estado, l.enlace_sesion, l.creado_en,
                p.id AS profesor_id, p.nombre AS profesor_nombre, p.especialidad AS profesor_especialidad, p.rating AS profesor_rating,
                c.id AS curso_id, c.nombre AS curso_nombre, c.codigo AS curso_codigo, c.icono AS curso_icono,
                n.codigo AS nivel_codigo, n.nombre AS nivel_nombre
            FROM lecciones l
            INNER JOIN profesores p ON l.profesor_id = p.id
            INNER JOIN cursos c ON l.curso_id = c.id
            INNER JOIN niveles n ON l.nivel_id = n.id
            WHERE l.estudiante_id = ? AND l.estado IN ('COMPLETADA', 'CANCELADA')
            ORDER BY l.fecha DESC, l.hora_inicio DESC
        ");
        $stmt->execute([$studentId]);
        return $this->formatLessonsList($stmt->fetchAll() ?: []);
    }

    /**
     * Formatea filas de base de datos a estructura estandarizada de API
     */
    private function formatLessonsList(array $rows): array {
        return array_map(function($l) {
            return [
                'id' => (int)$l['id'],
                'uuid' => $l['uuid'],
                'fecha' => $l['fecha'],
                'hora_inicio' => substr($l['hora_inicio'], 0, 5),
                'hora_fin' => substr($l['hora_fin'], 0, 5),
                'hora_rango' => substr($l['hora_inicio'], 0, 5) . ' - ' . substr($l['hora_fin'], 0, 5),
                'estado' => $l['estado'],
                'enlace_sesion' => $l['enlace_sesion'],
                'profesor' => [
                    'id' => (int)$l['profesor_id'],
                    'nombre' => $l['profesor_nombre'],
                    'especialidad' => $l['profesor_especialidad'],
                    'rating' => (float)$l['profesor_rating']
                ],
                'curso' => [
                    'id' => (int)$l['curso_id'],
                    'nombre' => $l['curso_nombre'],
                    'codigo' => $l['curso_codigo'],
                    'icono' => $l['curso_icono']
                ],
                'nivel' => [
                    'codigo' => $l['nivel_codigo'],
                    'nombre' => $l['nivel_nombre'],
                    'etiqueta' => "{$l['nivel_codigo']} - {$l['nivel_nombre']}"
                ]
            ];
        }, $rows);
    }

    /**
     * Crea lección oficial con UUID y enlace Google Meet
     */
    public function create(
        string $uuid,
        int $studentId,
        int $profesorId,
        int $cursoId,
        int $nivelId,
        int $horarioId,
        string $fecha,
        string $horaInicio,
        string $horaFin,
        string $meetUrl
    ): int {
        $stmt = $this->pdo->prepare("
            INSERT INTO lecciones (
                uuid, estudiante_id, profesor_id, curso_id, nivel_id, horario_id,
                fecha, hora_inicio, hora_fin, estado, enlace_sesion
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PROGRAMADA', ?)
        ");
        $stmt->execute([
            $uuid,
            $studentId,
            $profesorId,
            $cursoId,
            $nivelId,
            $horarioId,
            $fecha,
            $horaInicio,
            $horaFin,
            $meetUrl
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Cancelación atómica de lección y liberación inmediata del slot en base de datos
     */
    public function cancel(int $lessonId, string $lessonUuid = '', int $studentId = 0): array {
        $this->pdo->beginTransaction();
        try {
            // 1. Buscar la lección para update
            if ($lessonId > 0) {
                $stmt = $this->pdo->prepare("
                    SELECT id, uuid, horario_id, estudiante_id, estado, fecha, hora_inicio 
                    FROM lecciones 
                    WHERE id = ? FOR UPDATE
                ");
                $stmt->execute([$lessonId]);
            } else {
                $stmt = $this->pdo->prepare("
                    SELECT id, uuid, horario_id, estudiante_id, estado, fecha, hora_inicio 
                    FROM lecciones 
                    WHERE uuid = ? FOR UPDATE
                ");
                $stmt->execute([$lessonUuid]);
            }

            $lesson = $stmt->fetch();
            if (!$lesson) {
                $this->pdo->rollBack();
                return ['success' => false, 'code' => 404, 'message' => 'Lección no encontrada.'];
            }

            if ($lesson['estado'] === 'CANCELADA') {
                $this->pdo->rollBack();
                return ['success' => false, 'code' => 400, 'message' => 'Esta lección ya había sido cancelada previamente.'];
            }

            // 2. Marcar la lección como CANCELADA
            $updateLesson = $this->pdo->prepare("UPDATE lecciones SET estado = 'CANCELADA' WHERE id = ?");
            $updateLesson->execute([$lesson['id']]);

            // 3. Liberar el horario disponible en tiempo real
            $horarioModel = new Horario($this->pdo);
            $horarioModel->releaseSlot((int)$lesson['horario_id']);

            $this->pdo->commit();

            return [
                'success' => true,
                'code' => 200,
                'message' => 'Lección cancelada exitosamente y horario liberado para otros alumnos.',
                'lesson_id' => (int)$lesson['id'],
                'horario_id' => (int)$lesson['horario_id']
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'code' => 500, 'message' => 'Error al cancelar lección: ' . $e->getMessage()];
        }
    }

    /**
     * Aprueba y completa una lección, promoviendo de nivel al estudiante (A1 -> A2)
     */
    public function complete(int $lessonId, int $studentId = 0): array {
        $this->pdo->beginTransaction();
        try {
            $stmt = $this->pdo->prepare("
                SELECT l.id, l.estudiante_id, l.nivel_id, l.estado, e.nivel_actual, n.codigo AS nivel_codigo
                FROM lecciones l
                INNER JOIN estudiantes e ON l.estudiante_id = e.id
                INNER JOIN niveles n ON l.nivel_id = n.id
                WHERE l.id = ? FOR UPDATE
            ");
            $stmt->execute([$lessonId]);
            $lesson = $stmt->fetch();

            if (!$lesson) {
                $this->pdo->rollBack();
                return ['success' => false, 'code' => 404, 'message' => 'Lección no encontrada.'];
            }

            $sid = (int)$lesson['estudiante_id'];

            // 1. Marcar lección como COMPLETADA
            $updateLesson = $this->pdo->prepare("UPDATE lecciones SET estado = 'COMPLETADA' WHERE id = ?");
            $updateLesson->execute([$lessonId]);

            // 2. Progresión pedagógica de nivel: A1 -> A2 -> B1 -> B2 -> C1
            $currentLevel = $lesson['nivel_actual'] ?: 'A1';
            $levelLadder = [
                'A1' => 'A2',
                'A2' => 'B1',
                'B1' => 'B2',
                'B2' => 'C1'
            ];

            $promoted = false;
            $newLevel = $currentLevel;

            if (isset($levelLadder[$currentLevel])) {
                $newLevel = $levelLadder[$currentLevel];
                $updateStudent = $this->pdo->prepare("UPDATE estudiantes SET nivel_actual = ? WHERE id = ?");
                $updateStudent->execute([$newLevel, $sid]);
                $promoted = true;
            }

            $this->pdo->commit();

            return [
                'success' => true,
                'code' => 200,
                'message' => $promoted 
                    ? "¡Felicitaciones! Has completado tu lección y aprobado el nivel {$currentLevel}. ¡Avanzaste a Nivel {$newLevel}! 🎉"
                    : "¡Lección completada con éxito! Sigue así practicando tu fluidez.",
                'lesson_id' => $lessonId,
                'student_id' => $sid,
                'promoted' => $promoted,
                'old_level' => $currentLevel,
                'new_level' => $newLevel,
                'xp_earned' => 120
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return ['success' => false, 'code' => 500, 'message' => 'Error al completar lección: ' . $e->getMessage()];
        }
    }
}
