<?php
/**
 * ============================================================================
 * ENDPOINT FRONT-CONTROLLER: api/lessons.php
 * Arquitectura MVC: Delega la petición al LessonController
 * Cumple con Jira: M01-15, M01-16
 * ============================================================================
 */

require_once __DIR__ . '/../controllers/LessonController.php';

$controller = new LessonController();
$controller->handleRequest();
