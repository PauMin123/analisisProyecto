<?php
/**
 * ============================================================================
 * CONTROLADOR: AuthController (MVC: Capa Controladora)
 * Gestiona autenticación, registro de alumnos y recuperación de acceso
 * ============================================================================
 */

require_once __DIR__ . '/BaseController.php';
require_once __DIR__ . '/../models/Estudiante.php';

class AuthController extends BaseController {
    private Estudiante $estudianteModel;

    public function __construct() {
        parent::__construct();
        $this->estudianteModel = new Estudiante($this->pdo);
    }

    public function handleRequest(): void {
        $this->initHeaders();
        $input = $this->getRequestBody();
        $action = $_GET['action'] ?? ($input['action'] ?? 'login');

        switch ($action) {
            case 'login':
                $this->login($input);
                break;
            case 'register':
                $this->register($input);
                break;
            case 'recover':
                $this->recover($input);
                break;
            default:
                $this->jsonResponse(['success' => false, 'message' => 'Acción no permitida'], 400);
        }
    }

    /**
     * Inicia sesión con matrícula o correo y contraseña
     */
    private function login(array $input): void {
        $identifier = trim($input['identifier'] ?? ($input['email'] ?? ($input['username'] ?? '')));
        $password = trim($input['password'] ?? '');

        if (empty($identifier) || empty($password)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Por favor, ingrese su matrícula institucional o correo y su contraseña.'
            ], 400);
        }

        $student = $this->estudianteModel->authenticate($identifier, $password);
        if ($student) {
            $token = base64_encode(json_encode([
                'id' => $student['id'],
                'uuid' => $student['uuid'],
                'username' => $student['username'],
                'matricula' => $student['username'],
                'email' => $student['email'],
                'exp' => time() + (3600 * 24 * 7)
            ]));

            $this->jsonResponse([
                'success' => true,
                'message' => '¡Bienvenido a EnglishMadeForDummies! Sesión iniciada con éxito.',
                'token' => $token,
                'student' => [
                    'id' => (int)$student['id'],
                    'uuid' => $student['uuid'],
                    'username' => $student['username'],
                    'matricula' => $student['username'],
                    'nombre' => $student['nombre'],
                    'email' => $student['email'],
                    'nivel_actual' => $student['nivel_actual'] ?? 'A1',
                    'telefono' => $student['telefono'] ?? ''
                ]
            ], 200);
        }

        $this->jsonResponse([
            'success' => false,
            'message' => 'Credenciales inválidas. Verifique su matrícula o correo y contraseña.'
        ], 401);
    }

    /**
     * Registra estudiante con asignación institucional
     */
    private function register(array $input): void {
        $nombre = trim($input['nombre'] ?? '');
        $password = trim($input['password'] ?? '');
        $telefono = trim($input['telefono'] ?? '');
        $aliasInput = trim($input['alias'] ?? ($input['username'] ?? ($input['email'] ?? '')));

        if (empty($nombre) || empty($password)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Por favor, ingresa tu nombre completo y contraseña.'
            ], 400);
        }

        if (strlen($password) < 6) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'La contraseña debe tener un mínimo de 6 caracteres.'
            ], 400);
        }

        try {
            $newStudent = $this->estudianteModel->register($nombre, $password, $telefono, $aliasInput);
            $token = base64_encode(json_encode([
                'id' => $newStudent['id'],
                'uuid' => $newStudent['uuid'],
                'username' => $newStudent['matricula'],
                'matricula' => $newStudent['matricula'],
                'email' => $newStudent['email'],
                'exp' => time() + (3600 * 24 * 7)
            ]));

            $this->jsonResponse([
                'success' => true,
                'message' => "¡Cuenta creada! Tu matrícula es {$newStudent['matricula']} y tu correo es {$newStudent['email']}",
                'matricula' => $newStudent['matricula'],
                'email' => $newStudent['email'],
                'token' => $token,
                'student' => $newStudent
            ], 201);
        } catch (Exception $e) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Error al registrar estudiante: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Recuperación de acceso
     */
    private function recover(array $input): void {
        $identifier = trim($input['identifier'] ?? ($input['email'] ?? ''));
        if (empty($identifier)) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Por favor, ingrese su correo o usuario.'
            ], 400);
        }

        $this->jsonResponse([
            'success' => true,
            'message' => 'Se ha enviado un correo con las instrucciones de recuperación si la cuenta existe.'
        ], 200);
    }
}
