<?php
/**
 * ============================================================================
 * ENDPOINT FRONT-CONTROLLER: api/profile.php
 * Arquitectura MVC: Delega la petición al ProfileController
 * Cumple con Jira: M01-6, M01-7
 * ============================================================================
 */

require_once __DIR__ . '/../controllers/ProfileController.php';

$controller = new ProfileController();
$controller->handleRequest();
