<?php
/**
 * ============================================================================
 * CONTROLADOR: LessonController (MVC: Capa Controladora)
 * Gestiona el listado de lecciones (Próximas / Historial) y la cancelación atómica
 * Cumple con Jira: M01-15, M01-16
 * ============================================================================
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Leccion.php';
require_once __DIR__ . '/../models/Estudiante.php';

class LessonController extends BaseController {
    private Leccion $leccionModel;
    private Estudiante $estudianteModel;

    public function __construct() {
        parent::__construct();
        $this->leccionModel = new Leccion($this->pdo);
        $this->estudianteModel = new Estudiante($this->pdo);
    }

    public function handleRequest(): void {
        $this->initHeaders();
        $input = $this->getRequestBody();
        $action = $_GET['action'] ?? ($input['action'] ?? 'list');

        switch ($action) {
            case 'list':
            case 'summary':
                $studentId = (int)($_GET['student_id'] ?? ($input['student_id'] ?? 0));
                $matricula = trim($_GET['matricula'] ?? ($input['matricula'] ?? ''));
                $this->listLessons($studentId, $matricula);
                break;
            case 'cancel':
                $this->cancelLesson($input);
                break;
            case 'complete':
                $this->completeLesson($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Acción no permitida en lecciones'], 400);
        }
    }

    /**
     * Retorna lecciones próximas e historial de un estudiante
     */
    private function listLessons(int $studentId, string $matricula): void {
        if ($studentId <= 0 && !empty($matricula)) {
            $student = $this->estudianteModel->findByIdentifier($matricula);
            if ($student) {
                $studentId = (int)$student['id'];
            }
        }

        if ($studentId <= 0) {
            $first = $this->estudianteModel->getFirstStudent();
            $studentId = $first ? (int)$first['id'] : 1;
        }

        try {
            $proximas = $this->leccionModel->getUpcoming($studentId);
            $historial = $this->leccionModel->getHistory($studentId);

            $this->jsonResponse([
                'success' => true,
                'student_id' => $studentId,
                'proximas' => $proximas,
                'historial' => $historial,
                'counts' => [
                    'proximas' => count($proximas),
                    'historial' => count($historial)
                ]
            ], 200);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener lecciones: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancelación atómica y liberación en tiempo real del horario
     */
    private function cancelLesson(array $body): void {
        $lessonId = isset($body['lesson_id']) ? (int)$body['lesson_id'] : 0;
        $lessonUuid = $body['uuid'] ?? '';
        $studentId = isset($body['student_id']) ? (int)$body['student_id'] : 0;

        if ($lessonId <= 0 && empty($lessonUuid)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Identificador de lección requerido para cancelación.'
            ], 400);
        }

        $res = $this->leccionModel->cancel($lessonId, $lessonUuid, $studentId);
        $this->jsonResponse($res, $res['code'] ?? 200);
    }

    /**
     * Aprueba y completa una lección (promoción de nivel A1 -> A2)
     */
    private function completeLesson(array $body): void {
        $lessonId = isset($body['lesson_id']) ? (int)$body['lesson_id'] : (int)($_GET['lesson_id'] ?? 0);
        $studentId = isset($body['student_id']) ? (int)$body['student_id'] : (int)($_GET['student_id'] ?? 0);

        if ($lessonId <= 0) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Identificador de lección requerido para marcar como aprobada.'
            ], 400);
        }

        $res = $this->leccionModel->complete($lessonId, $studentId);
        $this->jsonResponse($res, $res['code'] ?? 200);
    }
}
