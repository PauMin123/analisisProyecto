<?php
/**
 * ============================================================================
 * API: Agendamiento & Filtros en Cascada (Pantalla 3)
 * Cumple con Jira: M01-11 (Filtro en Cascada: Curso -> Nivel -> Profesor)
 *                  M01-12 (Vista previa del perfil del profesor)
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
$action = $_GET['action'] ?? ($body['action'] ?? ($_POST['action'] ?? 'cascade_bootstrap'));

switch ($action) {
    /**
     * Devuelve toda la información para el filtro en cascada en una sola llamada optimizada (Reglas de Larsson)
     */
    case 'cascade_bootstrap':
        try {
            // 1. Cursos Activos
            $stmtCursos = $pdo->query("SELECT id, codigo, nombre, descripcion, icono FROM cursos WHERE activo = 1 ORDER BY id ASC");
            $cursos = $stmtCursos->fetchAll();

            // 2. Todos los Niveles indexados por curso
            $stmtNiveles = $pdo->query("SELECT id, curso_id, codigo, nombre, descripcion FROM niveles ORDER BY curso_id ASC, id ASC");
            $niveles = $stmtNiveles->fetchAll();

            // 3. Profesores con sus cursos vinculados
            $stmtProf = $pdo->query("
                SELECT 
                    p.id, p.uuid, p.nombre, p.email, p.especialidad, p.biografia, p.avatar_url, p.rating,
                    GROUP_CONCAT(pc.curso_id) AS cursos_ids
                FROM profesores p
                LEFT JOIN profesor_cursos pc ON p.id = pc.profesor_id
                WHERE p.activo = 1
                GROUP BY p.id
                ORDER BY p.rating DESC
            ");
            $profesoresRaw = $stmtProf->fetchAll();

            $profesores = array_map(function($prof) {
                $cursosArr = !empty($prof['cursos_ids']) ? array_map('intval', explode(',', $prof['cursos_ids'])) : [];
                unset($prof['cursos_ids']);
                $prof['cursos_asociados'] = $cursosArr;
                $prof['rating'] = (float)$prof['rating'];
                return $prof;
            }, $profesoresRaw);

            echo json_encode([
                'success' => true,
                'data' => [
                    'cursos' => $cursos,
                    'niveles' => $niveles,
                    'profesores' => $profesores
                ]
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener datos de agendamiento: ' . $e->getMessage()
            ]);
        }
        break;

    /**
     * Niveles específicos para un curso
     */
    case 'levels':
        $cursoId = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 0;
        if ($cursoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'curso_id inválido', 'levels' => []]);
            exit();
        }
        $stmt = $pdo->prepare("SELECT id, curso_id, codigo, nombre, descripcion FROM niveles WHERE curso_id = ? ORDER BY id ASC");
        $stmt->execute([$cursoId]);
        echo json_encode(['success' => true, 'levels' => $stmt->fetchAll()]);
        break;

    /**
     * Profesores asignados a un curso
     */
    case 'professors':
        $cursoId = isset($_GET['curso_id']) ? (int)$_GET['curso_id'] : 0;
        if ($cursoId <= 0) {
            echo json_encode(['success' => false, 'message' => 'curso_id inválido', 'professors' => []]);
            exit();
        }
        $stmt = $pdo->prepare("
            SELECT p.id, p.uuid, p.nombre, p.email, p.especialidad, p.biografia, p.avatar_url, p.rating
            FROM profesores p
            INNER JOIN profesor_cursos pc ON p.id = pc.profesor_id
            WHERE pc.curso_id = ? AND p.activo = 1
            ORDER BY p.rating DESC
        ");
        $stmt->execute([$cursoId]);
        echo json_encode(['success' => true, 'professors' => $stmt->fetchAll()]);
        break;

    /**
     * Fechas con disponibilidad para un profesor (Pantalla 4 - M01-13)
     */
    case 'available_dates':
        $profesorId = isset($_GET['profesor_id']) ? (int)$_GET['profesor_id'] : 0;
        if ($profesorId <= 0) {
            echo json_encode(['success' => false, 'message' => 'profesor_id requerido', 'dates' => []]);
            exit();
        }

        $stmt = $pdo->prepare("
            SELECT 
                fecha,
                COUNT(*) AS total_slots,
                SUM(CASE WHEN estado = 'DISPONIBLE' THEN 1 ELSE 0 END) AS slots_disponibles
            FROM horarios_disponibles
            WHERE profesor_id = ? AND fecha >= CURDATE()
            GROUP BY fecha
            ORDER BY fecha ASC
            LIMIT 14
        ");
        $stmt->execute([$profesorId]);
        $rows = $stmt->fetchAll();

        $diasSemana = ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'];
        $meses = ['', 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

        $dates = array_map(function($r) use ($diasSemana, $meses) {
            $ts = strtotime($r['fecha']);
            $dayOfWeek = (int)date('w', $ts);
            $dayNum = date('j', $ts);
            $monthNum = (int)date('n', $ts);

            return [
                'fecha' => $r['fecha'],
                'dia_semana' => $diasSemana[$dayOfWeek],
                'dia_num' => $dayNum,
                'mes' => $meses[$monthNum],
                'total_slots' => (int)$r['total_slots'],
                'slots_disponibles' => (int)$r['slots_disponibles'],
                'tiene_disponibilidad' => ((int)$r['slots_disponibles']) > 0
            ];
        }, $rows);

        echo json_encode(['success' => true, 'dates' => $dates]);
        break;

    /**
     * Cuadrícula de slots horarios para profesor y fecha seleccionada (Pantalla 4 - M01-14)
     */
    case 'slots':
        $profesorId = isset($_GET['profesor_id']) ? (int)$_GET['profesor_id'] : 0;
        $fecha = $_GET['fecha'] ?? date('Y-m-d');

        if ($profesorId <= 0) {
            echo json_encode(['success' => false, 'message' => 'profesor_id requerido', 'slots' => []]);
            exit();
        }

        $stmt = $pdo->prepare("
            SELECT id, profesor_id, fecha, hora_inicio, hora_fin, estado, version
            FROM horarios_disponibles
            WHERE profesor_id = ? AND fecha = ?
            ORDER BY hora_inicio ASC
        ");
        $stmt->execute([$profesorId, $fecha]);
        $rawSlots = $stmt->fetchAll();

        $slots = array_map(function($s) {
            $hInicio = substr($s['hora_inicio'], 0, 5);
            $hFin = substr($s['hora_fin'], 0, 5);
            $horaInt = (int)substr($s['hora_inicio'], 0, 2);

            $turno = 'morning';
            if ($horaInt >= 12 && $horaInt < 18) {
                $turno = 'afternoon';
            } elseif ($horaInt >= 18) {
                $turno = 'evening';
            }

            return [
                'id' => (int)$s['id'],
                'hora_inicio' => $hInicio,
                'hora_fin' => $hFin,
                'hora_rango' => "{$hInicio} - {$hFin}",
                'turno' => $turno,
                'estado' => $s['estado'],
                'disponible' => ($s['estado'] === 'DISPONIBLE'),
                'version' => (int)$s['version']
            ];
        }, $rawSlots);

        echo json_encode([
            'success' => true,
            'fecha' => $fecha,
            'profesor_id' => $profesorId,
            'total' => count($slots),
            'slots' => $slots
        ]);
        break;

    /**
     * Reserva Atómica con Detección Concurrente de Colisión (M01-18, M01-19, HTTP 409)
     */
    case 'reserve':
        if (empty($body)) {
            $body = $_POST;
        }

        $studentId = isset($body['student_id']) ? (int)$body['student_id'] : 0;
        $horarioId = isset($body['horario_id']) ? (int)$body['horario_id'] : 0;
        $cursoId = isset($body['curso_id']) ? (int)$body['curso_id'] : 0;
        $nivelId = isset($body['nivel_id']) ? (int)$body['nivel_id'] : 0;
        $profesorId = isset($body['profesor_id']) ? (int)$body['profesor_id'] : 0;

        if ($studentId <= 0 || $horarioId <= 0 || $cursoId <= 0 || $nivelId <= 0 || $profesorId <= 0) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Parámetros de reserva incompletos o inválidos.'
            ]);
            exit();
        }

        try {
            $pdo->beginTransaction();

            // Bloqueo pesimista / comprobación atómica del horario
            $stmtLock = $pdo->prepare("SELECT id, profesor_id, fecha, hora_inicio, hora_fin, estado, version FROM horarios_disponibles WHERE id = ? FOR UPDATE");
            $stmtLock->execute([$horarioId]);
            $slot = $stmtLock->fetch();

            if (!$slot) {
                $pdo->rollBack();
                http_response_code(404);
                echo json_encode(['success' => false, 'message' => 'Horario no encontrado.']);
                exit();
            }

            // DETECCIÓN DE COLISIÓN CONCURRENTE (HTTP 409 CONFLICT - M01-18)
            $isSimulated = !empty($body['simulate_collision']);
            if ($slot['estado'] !== 'DISPONIBLE' || $isSimulated) {
                $pdo->rollBack();
                http_response_code(409);
                echo json_encode([
                    'success' => false,
                    'collision' => true,
                    'code' => 409,
                    'simulated' => $isSimulated,
                    'message' => '¡Lo sentimos! Este horario acaba de ser reservado por otro estudiante. Por favor, selecciona un horario alternativo.'
                ]);
                exit();
            }

            // Marcar slot como RESERVADO
            $stmtUpdate = $pdo->prepare("
                UPDATE horarios_disponibles 
                SET estado = 'RESERVADO', version = version + 1, bloqueado_hasta = NULL, bloqueado_por_estudiante_id = NULL
                WHERE id = ? AND estado = 'DISPONIBLE'
            ");
            $stmtUpdate->execute([$horarioId]);

            // Generar UUID único de lección
            $lessonUuid = 'LEC-' . strtoupper(substr(uniqid(), -6));
            $meetUrl = 'https://meet.google.com/emd-' . strtolower(substr(uniqid(), -4) . '-' . substr(uniqid(), -3));

            // Insertar lección oficial
            $stmtLesson = $pdo->prepare("
                INSERT INTO lecciones (
                    uuid, estudiante_id, profesor_id, curso_id, nivel_id, horario_id,
                    fecha, hora_inicio, hora_fin, estado, enlace_sesion
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'PROGRAMADA', ?)
            ");
            $stmtLesson->execute([
                $lessonUuid,
                $studentId,
                $profesorId,
                $cursoId,
                $nivelId,
                $horarioId,
                $slot['fecha'],
                $slot['hora_inicio'],
                $slot['hora_fin'],
                $meetUrl
            ]);
            $newLessonId = $pdo->lastInsertId();

            $pdo->commit();

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'message' => '¡Lección agendada con éxito!',
                'lesson' => [
                    'id' => (int)$newLessonId,
                    'uuid' => $lessonUuid,
                    'fecha' => $slot['fecha'],
                    'hora_inicio' => substr($slot['hora_inicio'], 0, 5),
                    'hora_fin' => substr($slot['hora_fin'], 0, 5),
                    'enlace_sesion' => $meetUrl,
                    'estado' => 'PROGRAMADA'
                ]
            ]);
        } catch (Exception $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error interno al procesar reserva: ' . $e->getMessage()
            ]);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Acción no reconocida']);
        break;
}
