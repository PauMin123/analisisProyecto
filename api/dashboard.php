<?php
/**
 * ============================================================================
 * ENDPOINT FRONT-CONTROLLER: api/dashboard.php
 * Arquitectura MVC: Delega la petición al DashboardController
 * ============================================================================
 */

require_once __DIR__ . '/../controllers/DashboardController.php';

$controller = new DashboardController();
$controller->handleRequest();
