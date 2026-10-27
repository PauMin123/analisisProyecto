<?php
/**
 * ============================================================================
 * ENDPOINT FRONT-CONTROLLER: api/booking.php
 * Arquitectura MVC: Delega la petición al BookingController
 * Cumple con Jira: M01-11, M01-12, M01-13, M01-14, M01-18 (HTTP 409)
 * ============================================================================
 */

require_once __DIR__ . '/../controllers/BookingController.php';

$controller = new BookingController();
$controller->handleRequest();
