<?php
/**
 * ============================================================================
 * API: Mis Lecciones & Cancelación con Liberación de Horarios (Pantalla 5)
 * Cumple con Jira: M01-15 (Listado de Próximas e Historial)
 *                  M01-16 (Cancelación de Lección & Liberación en tiempo real)
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
$action = $_GET['action'] ?? ($body['action'] ?? ($_POST['action'] ?? 'list'));

switch ($action) {
    /**
     * M01-15: Listado de Lecciones del Estudiante (Próximas e Historial)
     */
    case 'list':
        $studentId = isset($_GET['student_id']) ? (int)$_GET['student_id'] : (int)($body['student_id'] ?? 0);
        if ($studentId <= 0) {
            // Tomar el primer estudiante si no se especifica
            $stmtFirst = $pdo->query("SELECT id FROM estudiantes ORDER BY id ASC LIMIT 1");
            $studentId = (int)$stmtFirst->fetchColumn() ?: 1;
        }

        try {
            // 1. Lecciones Próximas (PROGRAMADA, EN_CURSO)
            $stmtProx = $pdo->prepare("
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
            $stmtProx->execute([$studentId]);
            $proximas = array_map(function($l) {
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
            }, $stmtProx->fetchAll());

            // 2. Historial de Lecciones (COMPLETADA, CANCELADA)
            $stmtHist = $pdo->prepare("
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
            $stmtHist->execute([$studentId]);
            $historial = array_map(function($l) {
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
            }, $stmtHist->fetchAll());

            echo json_encode([
                'success' => true,
                'student_id' => $studentId,
                'proximas' => $proximas,
                'historial' => $historial,
                'counts' => [
                    'proximas' => count($proximas),
                    'historial' => count($historial)
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener lecciones: ' . $e->getMessage()
            ]);
        }
        break;

    /**
     * M01-16: Cancelación de Lección con Liberación Atómica del Horario
     */
    case 'cancel':
        if (empty($body)) {
            $body = $_POST;
        }

        $lessonId = isset($body['lesson_id']) ? (int)$body['lesson_id'] : 0;
        $lessonUuid = $body['uuid'] ?? '';
        $studentId = isset($body['student_id']) ? (int)$body['student_id'] : 0;

        if ($lessonId <= 0 && empty($lessonUuid)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Identificador de lección requerido.']);
            exit();
        }

        try {
            $pdo->beginTransaction();

            // Buscar la lección y verificar su estado y horario_id asociado
            if ($lessonId > 0) {
                $stmtCheck = $pdo->prepare("SELECT id, uuid, estudiante_id, horario_id, fecha, hora_inicio, hora_fin, estado FROM lecciones WHERE id = ? FOR UPDATE");
                $stmtCheck->execute([$lessonId]);
            } else {
                $stmtCheck = $pdo->prepare("SELECT id, uuid, estudiante_id, horario_id, fecha, hora_inicio, hora_fin, estado FROM lecciones WHERE uuid = ? FOR UPDATE");
                $stmtCheck->execute([$lessonUuid]);
            }

            $lesson = $stmtCheck->fetch();

            if (!$lesson) {
                $pdo->rollBack();
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'La lección no fue encontrada.']);
                exit();
            }

            if ($lesson['estado'] === 'CANCELADA') {
                $pdo->rollBack();
                echo json_encode(['success' => false, 'message' => 'Esta lección ya ha sido cancelada previamente.']);
                exit();
            }

            if ($lesson['estado'] === 'COMPLETADA') {
                $pdo->rollBack();
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'No es posible cancelar una lección que ya ha sido completada.']);
                exit();
            }

            // 1. Marcar lección como CANCELADA
            $stmtUpdateLesson = $pdo->prepare("UPDATE lecciones SET estado = 'CANCELADA' WHERE id = ?");
            $stmtUpdateLesson->execute([$lesson['id']]);

            // 2. Liberación atómica del horario en horarios_disponibles (M01-16)
            $horarioId = (int)$lesson['horario_id'];
            if ($horarioId > 0) {
                $stmtFreeSlot = $pdo->prepare("
                    UPDATE horarios_disponibles 
                    SET estado = 'DISPONIBLE', version = version + 1, bloqueado_hasta = NULL, bloqueado_por_estudiante_id = NULL
                    WHERE id = ?
                ");
                $stmtFreeSlot->execute([$horarioId]);
            }

            $pdo->commit();

            echo json_encode([
                'success' => true,
                'message' => "Lección {$lesson['uuid']} cancelada con éxito. El horario ha sido liberado inmediatamente.",
                'lesson_id' => (int)$lesson['id'],
                'uuid' => $lesson['uuid'],
                'horario_liberado' => $horarioId
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al cancelar la lección: ' . $e->getMessage()
            ]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida en api/lessons.php']);
        break;
}
