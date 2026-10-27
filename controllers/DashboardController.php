<?php
/**
 * ============================================================================
 * CONTROLADOR: DashboardController (MVC: Capa Controladora)
 * Agrega métricas, próxima lección en vivo y catálogo de cursos de la academia
 * ============================================================================
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Estudiante.php';
require_once __DIR__ . '/../models/Curso.php';
require_once __DIR__ . '/../models/Leccion.php';

class DashboardController extends BaseController {
    private Estudiante $estudianteModel;
    private Curso $cursoModel;
    private Leccion $leccionModel;

    public function __construct() {
        parent::__construct();
        $this->estudianteModel = new Estudiante($this->pdo);
        $this->cursoModel = new Curso($this->pdo);
        $this->leccionModel = new Leccion($this->pdo);
    }

    public function handleRequest(): void {
        $this->initHeaders();
        $studentId = $_GET['student_id'] ?? ($_POST['student_id'] ?? null);
        $matricula = $_GET['matricula'] ?? ($_POST['matricula'] ?? null);

        $this->summary($studentId ? (int)$studentId : null, $matricula);
    }

    /**
     * Resumen completo del dashboard del estudiante
     */
    public function summary(?int $studentId, ?string $matricula): void {
        $student = null;
        if ($studentId) {
            $student = $this->estudianteModel->findById($studentId);
        } elseif ($matricula) {
            $student = $this->estudianteModel->findByIdentifier($matricula);
        }

        if (!$student) {
            $student = $this->estudianteModel->getFirstStudent();
        }

        $sid = $student ? (int)$student['id'] : 1;

        // 1. Próxima Lección Programada (M01-9)
        $nextLesson = $this->leccionModel->getNextLesson($sid);

        // 2. Contadores y Estadísticas (M01-10)
        $stats = $this->estudianteModel->getStats($sid);

        // 3. Catálogo de Cursos Disponibles
        $cursos = $this->cursoModel->getActiveCourses();

        $this->jsonResponse([
            'success' => true,
            'student' => $student ? [
                'id' => (int)$student['id'],
                'uuid' => $student['uuid'],
                'nombre' => $student['nombre'],
                'username' => $student['username'],
                'email' => $student['email'],
                'nivel_actual' => $student['nivel_actual'] ?? 'A1',
                'telefono' => $student['telefono'] ?? ''
            ] : null,
            'stats' => $stats,
            'next_lesson' => $nextLesson,
            'courses' => $cursos
        ], 200);
    }
}
