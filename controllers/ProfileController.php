<?php
/**
 * ============================================================================
 * CONTROLADOR: ProfileController (MVC: Capa Controladora)
 * Gestiona la visualización del perfil del estudiante y la edición de datos
 * Cumple con Jira: M01-6, M01-7
 * ============================================================================
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Estudiante.php';

class ProfileController extends BaseController {
    private Estudiante $estudianteModel;

    public function __construct() {
        parent::__construct();
        $this->estudianteModel = new Estudiante($this->pdo);
    }

    public function handleRequest(): void {
        $this->initHeaders();
        $input = $this->getRequestBody();
        $action = $_GET['action'] ?? ($input['action'] ?? 'get');

        switch ($action) {
            case 'get':
                $studentId = (int)($_GET['student_id'] ?? ($input['student_id'] ?? 0));
                $matricula = trim($_GET['matricula'] ?? ($input['matricula'] ?? ''));
                $this->getProfile($studentId, $matricula);
                break;
            case 'update':
                $this->updateProfile($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Acción no permitida en perfil'], 400);
        }
    }

    /**
     * Obtiene datos completos del perfil, estadísticas y metadatos CEFR
     */
    private function getProfile(int $studentId, string $matricula): void {
        $student = null;
        if ($studentId > 0) {
            $student = $this->estudianteModel->findById($studentId);
        } elseif (!empty($matricula)) {
            $student = $this->estudianteModel->findByIdentifier($matricula);
        }

        if (!$student) {
            $student = $this->estudianteModel->getFirstStudent();
        }

        if (!$student) {
            $this->jsonResponse(['success' => false, 'message' => 'Estudiante no encontrado'], 404);
        }

        $sid = (int)$student['id'];
        $student['matricula'] = $student['username'];

        // Estadísticas
        $stats = $this->estudianteModel->getStats($sid);

        // Metadatos pedagógicos del nivel CEFR
        $nivel = $student['nivel_actual'] ?: 'A1';
        $cefrData = [
            'A1' => ['nombre' => 'Principiante', 'progreso' => 20, 'siguiente' => 'A2 Básico', 'descripcion' => 'Comprensión de frases cotidianas básicas y vocabulario esencial.'],
            'A2' => ['nombre' => 'Básico', 'progreso' => 40, 'siguiente' => 'B1 Intermedio', 'descripcion' => 'Comunicación en situaciones sencillas y expresión de necesidades inmediatas.'],
            'B1' => ['nombre' => 'Intermedio', 'progreso' => 60, 'siguiente' => 'B2 Intermedio Alto', 'descripcion' => 'Desenvolvimiento en viajes, temas laborales y conversaciones fluidas.'],
            'B2' => ['nombre' => 'Avanzado', 'progreso' => 80, 'siguiente' => 'C1 Fluido Nativo', 'descripcion' => 'Fluidez y espontaneidad natural con hablantes nativos.'],
            'C1' => ['nombre' => 'Dominio Operativo Eficaz', 'progreso' => 100, 'siguiente' => 'C2 Maestría', 'descripcion' => 'Uso flexible y efectivo del inglés en ámbitos sociales, académicos y profesionales.']
        ];
        $cefr = $cefrData[$nivel] ?? $cefrData['A1'];

        $this->jsonResponse([
            'success' => true,
            'student' => $student,
            'stats' => [
                'completadas' => $stats['lecciones_completadas'],
                'horas_acumuladas' => $stats['horas_acumuladas'],
                'pendientes' => $stats['lecciones_pendientes'],
                'canceladas' => $stats['lecciones_canceladas']
            ],
            'cefr' => [
                'codigo' => $nivel,
                'nombre' => $cefr['nombre'],
                'progreso_porcentaje' => $cefr['progreso'],
                'siguiente_hito' => $cefr['siguiente'],
                'descripcion' => $cefr['descripcion']
            ]
        ], 200);
    }

    /**
     * Actualiza información de contacto del estudiante
     */
    private function updateProfile(array $input): void {
        $studentId = (int)($input['student_id'] ?? 0);
        $nombre = trim($input['nombre'] ?? '');
        $telefono = trim($input['telefono'] ?? '');

        if ($studentId <= 0) {
            $this->jsonResponse(['success' => false, 'message' => 'ID de estudiante no válido.'], 400);
        }

        if (empty($nombre)) {
            $this->jsonResponse(['success' => false, 'message' => 'El nombre completo es obligatorio.'], 400);
        }

        if (strlen($nombre) < 3) {
            $this->jsonResponse(['success' => false, 'message' => 'El nombre debe tener al menos 3 caracteres.'], 400);
        }

        try {
            $this->estudianteModel->updateProfile($studentId, $nombre, $telefono);
            $updated = $this->estudianteModel->findById($studentId);
            if ($updated) {
                $updated['matricula'] = $updated['username'];
            }

            $this->jsonResponse([
                'success' => true,
                'message' => '¡Perfil actualizado correctamente!',
                'student' => $updated
            ], 200);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al actualizar perfil: ' . $e->getMessage()
            ], 500);
        }
    }
}
