<?php
/**
 * ============================================================================
 * API: Dashboard & Resumen del Estudiante
 * Cumple con Jira: M01-8 (Epic), M01-9 (Próxima Lección), M01-10 (Contadores)
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

$action = $_GET['action'] ?? ($_POST['action'] ?? 'summary');
$studentId = $_GET['student_id'] ?? ($_POST['student_id'] ?? null);
$matricula = $_GET['matricula'] ?? ($_POST['matricula'] ?? null);

switch ($action) {
    case 'summary':
    default:
        // Si no se proporcionó student_id directo, intentar resolver por matrícula o username
        $student = null;
        if ($studentId) {
            $stmt = $pdo->prepare("SELECT id, uuid, username, nombre, email, nivel_actual, telefono FROM estudiantes WHERE id = ? LIMIT 1");
            $stmt->execute([$studentId]);
            $student = $stmt->fetch();
        } elseif ($matricula) {
            $stmt = $pdo->prepare("SELECT id, uuid, username, nombre, email, nivel_actual, telefono FROM estudiantes WHERE username = ? OR uuid = ? LIMIT 1");
            $stmt->execute([$matricula, $matricula]);
            $student = $stmt->fetch();
        }

        if (!$student) {
            // Si aún no hay ID (modo demo o usuario genérico), tomar el primer estudiante disponible o construir respuesta por defecto
            $stmt = $pdo->query("SELECT id, uuid, username, nombre, email, nivel_actual, telefono FROM estudiantes ORDER BY id ASC LIMIT 1");
            $student = $stmt->fetch();
        }

        $sid = $student ? $student['id'] : 1;

        // 1. Obtener la Próxima Lección Programada
        $lessonStmt = $pdo->prepare("
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
        $lessonStmt->execute([$sid]);
        $nextLesson = $lessonStmt->fetch();

        // 2. Contadores y Estadísticas (M01-10)
        $countStmt = $pdo->prepare("
            SELECT 
                SUM(CASE WHEN estado = 'COMPLETADA' THEN 1 ELSE 0 END) AS completadas,
                SUM(CASE WHEN estado = 'PROGRAMADA' THEN 1 ELSE 0 END) AS pendientes,
                SUM(CASE WHEN estado = 'CANCELADA' THEN 1 ELSE 0 END) AS canceladas,
                COUNT(*) AS total
            FROM lecciones 
            WHERE estudiante_id = ?
        ");
        $countStmt->execute([$sid]);
        $counts = $countStmt->fetch();

        $completadas = (int)($counts['completadas'] ?? 0);
        $pendientes = (int)($counts['pendientes'] ?? 0);
        $horasAcumuladas = round($completadas * 1.0, 1);

        // 3. Catálogo de Cursos Disponibles para Acceso Rápido
        $coursesStmt = $pdo->query("SELECT id, codigo, nombre, descripcion, icono FROM cursos WHERE activo = 1 ORDER BY id ASC");
        $cursos = $coursesStmt->fetchAll();

        echo json_encode([
            'success' => true,
            'student' => $student ? [
                'id' => $student['id'],
                'uuid' => $student['uuid'],
                'username' => $student['username'],
                'matricula' => $student['username'],
                'nombre' => $student['nombre'],
                'email' => $student['email'],
                'nivel_actual' => $student['nivel_actual'] ?? 'A1'
            ] : null,
            'next_lesson' => $nextLesson ? [
                'id' => $nextLesson['id'],
                'uuid' => $nextLesson['uuid'],
                'fecha' => $nextLesson['fecha'],
                'hora_inicio' => substr($nextLesson['hora_inicio'], 0, 5),
                'hora_fin' => substr($nextLesson['hora_fin'], 0, 5),
                'profesor' => $nextLesson['profesor_nombre'],
                'especialidad' => $nextLesson['profesor_especialidad'],
                'rating' => $nextLesson['profesor_rating'],
                'curso' => $nextLesson['curso_nombre'],
                'nivel' => $nextLesson['nivel_codigo'] . ' - ' . $nextLesson['nivel_nombre'],
                'enlace_sesion' => $nextLesson['enlace_sesion'],
                'estado' => $nextLesson['estado']
            ] : null,
            'stats' => [
                'lecciones_completadas' => $completadas,
                'lecciones_pendientes' => $pendientes,
                'horas_acumuladas' => $horasAcumuladas,
                'nivel' => $student['nivel_actual'] ?? 'A1'
            ],
            'cursos' => $cursos
        ]);
        break;
}
