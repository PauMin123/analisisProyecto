<?php
/**
 * ============================================================================
 * CONTROLADOR: BookingController (MVC: Capa Controladora)
 * Gestiona el filtro en cascada, calendario de slots y la reserva atómica
 * Cumple con Jira: M01-11, M01-12, M01-13, M01-14, M01-18 (HTTP 409)
 * ============================================================================
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Curso.php';
require_once __DIR__ . '/../models/Nivel.php';
require_once __DIR__ . '/../models/Profesor.php';
require_once __DIR__ . '/../models/Horario.php';
require_once __DIR__ . '/../models/Leccion.php';

class BookingController extends BaseController {
    private Curso $cursoModel;
    private Nivel $nivelModel;
    private Profesor $profesorModel;
    private Horario $horarioModel;
    private Leccion $leccionModel;

    public function __construct() {
        parent::__construct();
        $this->cursoModel = new Curso($this->pdo);
        $this->nivelModel = new Nivel($this->pdo);
        $this->profesorModel = new Profesor($this->pdo);
        $this->horarioModel = new Horario($this->pdo);
        $this->leccionModel = new Leccion($this->pdo);
    }

    public function handleRequest(): void {
        $this->initHeaders();
        $input = $this->getRequestBody();
        $action = $_GET['action'] ?? ($input['action'] ?? 'cascade_bootstrap');

        switch ($action) {
            case 'cascade_bootstrap':
                $this->cascadeBootstrap();
                break;
            case 'levels':
                $cursoId = (int)($_GET['curso_id'] ?? ($input['curso_id'] ?? 0));
                $this->levels($cursoId);
                break;
            case 'professors':
                $cursoId = (int)($_GET['curso_id'] ?? ($input['curso_id'] ?? 0));
                $this->professors($cursoId);
                break;
            case 'available_dates':
                $profesorId = (int)($_GET['profesor_id'] ?? ($input['profesor_id'] ?? 0));
                $this->availableDates($profesorId);
                break;
            case 'slots':
                $profesorId = (int)($_GET['profesor_id'] ?? ($input['profesor_id'] ?? 0));
                $fecha = trim($_GET['fecha'] ?? ($input['fecha'] ?? date('Y-m-d')));
                $this->slots($profesorId, $fecha);
                break;
            case 'reserve':
                $this->reserve($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Acción de agendamiento no reconocida'], 400);
        }
    }

    /**
     * Devuelve toda la información para el filtro en cascada en una sola llamada optimizada
     */
    private function cascadeBootstrap(): void {
        try {
            $cursos = $this->cursoModel->getActiveCourses();
            $niveles = $this->nivelModel->getAll();
            $profesores = $this->profesorModel->getAllWithCourses();

            $this->jsonResponse([
                'success' => true,
                'data' => [
                    'cursos' => $cursos,
                    'niveles' => $niveles,
                    'profesores' => $profesores
                ]
            ], 200);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al obtener datos de agendamiento: ' . $e->getMessage()
            ], 500);
        }
    }

    private function levels(int $cursoId): void {
        if ($cursoId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'curso_id inválido', 'levels' => []], 400);
        }
        $levels = $this->nivelModel->getByCourseId($cursoId);
        $this->jsonResponse(['success' => true, 'levels' => $levels], 200);
    }

    private function professors(int $cursoId): void {
        if ($cursoId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'curso_id inválido', 'professors' => []], 400);
        }
        $professors = $this->profesorModel->getByCourseId($cursoId);
        $this->jsonResponse(['success' => true, 'professors' => $professors], 200);
    }

    private function availableDates(int $profesorId): void {
        if ($profesorId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'profesor_id requerido', 'dates' => []], 400);
        }
        $dates = $this->horarioModel->getAvailableDates($profesorId);
        $this->jsonResponse([
            'success' => true,
            'profesor_id' => $profesorId,
            'total' => count($dates),
            'dates' => $dates
        ], 200);
    }

    private function slots(int $profesorId, string $fecha): void {
        if ($profesorId <= 0 || empty($fecha)) {
            $this->jsonResponse(['success' => false, 'message' => 'profesor_id y fecha requeridos', 'slots' => []], 400);
        }
        $slots = $this->horarioModel->getSlotsByDate($profesorId, $fecha);
        $this->jsonResponse([
            'success' => true,
            'fecha' => $fecha,
            'profesor_id' => $profesorId,
            'total' => count($slots),
            'slots' => $slots
        ], 200);
    }

    /**
     * Reserva Atómica con Bloqueo Pesimista y Detección de Conflicto Concurrente (HTTP 409)
     */
    private function reserve(array $body): void {
        $studentId = isset($body['student_id']) ? (int)$body['student_id'] : 0;
        $horarioId = isset($body['horario_id']) ? (int)$body['horario_id'] : 0;
        $cursoId = isset($body['curso_id']) ? (int)$body['curso_id'] : 0;
        $nivelId = isset($body['nivel_id']) ? (int)$body['nivel_id'] : 0;
        $profesorId = isset($body['profesor_id']) ? (int)$body['profesor_id'] : 0;

        if ($studentId <= 0 || $horarioId <= 0 || $cursoId <= 0 || $nivelId <= 0 || $profesorId <= 0) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Parámetros de reserva incompletos o inválidos.'
            ], 400);
        }

        try {
            $this->pdo->beginTransaction();

            // Bloqueo pesimista FOR UPDATE en el horario seleccionado
            $slot = $this->horarioModel->lockSlotForUpdate($horarioId);
            if (!$slot) {
                $this->pdo->rollBack();
                $this->jsonResponse(['success' => false, 'message' => 'Horario no encontrado.'], 404);
            }

            // DETECCIÓN DE COLISIÓN CONCURRENTE (HTTP 409 CONFLICT - M01-18)
            $isSimulated = !empty($body['simulate_collision']);
            if ($slot['estado'] !== 'DISPONIBLE' || $isSimulated) {
                $this->pdo->rollBack();
                $this->jsonResponse([
                    'success' => false,
                    'collision' => true,
                    'code' => 409,
                    'simulated' => $isSimulated,
                    'message' => '¡Lo sentimos! Este horario acaba de ser reservado por otro estudiante. Por favor, selecciona un horario alternativo.'
                ], 409);
            }

            // Marcar slot como RESERVADO
            $this->horarioModel->reserveSlot($horarioId);

            // Generar UUID único y sala Google Meet
            $lessonUuid = 'LEC-' . strtoupper(substr(uniqid(), -6));
            $meetUrl = 'https://meet.google.com/emd-' . strtolower(substr(uniqid(), -4) . '-' . substr(uniqid(), -3));

            $newLessonId = $this->leccionModel->create(
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
            );

            $this->pdo->commit();

            $this->jsonResponse([
                'success' => true,
                'message' => '¡Lección agendada con éxito!',
                'lesson' => [
                    'id' => $newLessonId,
                    'uuid' => $lessonUuid,
                    'fecha' => $slot['fecha'],
                    'hora_inicio' => substr($slot['hora_inicio'], 0, 5),
                    'hora_fin' => substr($slot['hora_fin'], 0, 5),
                    'enlace_sesion' => $meetUrl,
                    'estado' => 'PROGRAMADA'
                ]
            ], 201);
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error interno al procesar reserva: ' . $e->getMessage()
            ], 500);
        }
    }
}
