<?php
/**
 * ============================================================================
 * CONTROLADOR BASE (MVC: Capa Controladora)
 * Proporciona métodos comunes para recepción de peticiones y respuestas JSON
 * ============================================================================
 */

require_once __DIR__ . '/../api/config.php';

abstract class BaseController {
    protected ?PDO $pdo;

    public function __construct() {
        $db = DatabaseConnection::getInstance();
        $this->pdo = $db->getConnection();
    }

    /**
     * Configura cabeceras CORS y Content-Type estándar
     */
    protected function initHeaders(): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Obtiene el cuerpo de la petición parseado de JSON o POST
     */
    protected function getRequestBody(): array {
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        if (is_array($json) && !empty($json)) {
            return $json;
        }
        return $_POST ?? [];
    }

    /**
     * Emite una respuesta JSON formateada y finaliza el script
     */
    protected function jsonResponse(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit();
    }

    /**
     * Punto de entrada principal para despachar acciones
     */
    abstract public function handleRequest(): void;
}
