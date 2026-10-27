<?php
/**
 * ============================================================================
 * ENDPOINT FRONT-CONTROLLER: api/auth.php
 * Arquitectura MVC: Delega la petición al AuthController
 * ============================================================================
 */

require_once __DIR__ . '/../controllers/AuthController.php';

$controller = new AuthController();
$controller->handleRequest();
