# EnglishMadeForDummies Mobile (V2)
### Sistema Móvil de Agendamiento de Lecciones de Inglés y Prevención Concurrente de Colisiones

---

**Asignatura:** Ingeniería de Software II / Desarrollo Móvil  
**Punteo:** 15 / 15 Puntos (Rúbrica Oficial)  
**Proyecto Jira:** `M01`  
**Modalidad:** Prototipo Móvil & Demo Presencial  

---

## 🚀 Descripción del Proyecto

Aplicación web móvil para la gestión y agendamiento en tiempo real de lecciones de inglés con profesores certificados, aplicando:
* **7 Pantallas Móviles Responsivas:** Flujo completo desde autenticación hasta emisión de vouchers digitales.
* **Patrones GoF:** **Singleton** (`StudentSessionManager` y `DatabaseConnection`) y **Factory Method** (`CourseServiceFactory`).
* **Manejo Atómico de Concurrencia:** Bloqueo pesimista en MySQL (`SELECT ... FOR UPDATE`) con detección y respuesta **HTTP 409 Conflict**.
* **Complejidad Ciclomática Verificada:** Algoritmo anticolisión puro con $V(G) = 6 \le 8$ demostrado por dos métodos estándar (McCabe y Predicados).
* **Suite de Pruebas Automatizadas:** 12 pruebas unitarias y de concurrencia al 100% de éxito, más pruebas de UI automatizadas de caja negra.

---

## 📱 Las 7 Pantallas del Sistema

1. **Pantalla 1 (Login / Registro):** Autenticación con matrícula `EMD-2026-XXXX` y registro automatizado institucional.
2. **Pantalla 2 (Dashboard / Inicio):** Próxima lección en vivo con Google Meet, contadores de métricas y catálogo de cursos.
3. **Pantalla 3 (Agendar Lección - Cascada):** Filtro reactivo en 3 pasos: Curso $\to$ Nivel $\to$ Profesor con estrellas.
4. **Pantalla 4 (Calendario & Slots):** Tira táctil de 14 días y leyenda de 3 estados: 🟢 Verde, ⚪ Gris y 🔵 Azul.
5. **Pantalla 5 (Mis Lecciones):** Pestañas Próximas vs Historial con cancelación atómica en base de datos.
6. **Pantalla 6 (Perfil del Estudiante):** Datos personales, barra de progreso CEFR y cierre seguro de sesión.
7. **Pantalla 7 (Confirmación & Concurrencia):** Voucher digital con código de reserva único `LEC-XXXXXX` y modal de alerta HTTP 409 ante colisiones concurrentes.

---

## 🧪 Pruebas Automatizadas y Documentación

* **Suite Unitaria y de Mocks:** Abre en el navegador `tests/run_tests.php` (12/12 Pruebas Aprobadas).
* **Pruebas de UI Automatizadas (Caja Negra):** Abre `tests/test_ui.html`.
* **Informe Técnico Completo:** Consulta [DOCUMENTACION_COMPLETA_PROYECTO.md](DOCUMENTACION_COMPLETA_PROYECTO.md) o visualízalo en `documentacion.html`.

---

## 🛠️ Tecnologías Empleadas

* **Frontend:** HTML5 Semántico, CSS3 Liquid Glass Tokens, JavaScript Moderno (ES6 Modules).
* **Backend:** PHP 8.2 con PDO MySQL.
* **Base de Datos:** MySQL / MariaDB (Motor InnoDB con soporte para transacciones y bloqueos de fila).
* **Entorno de Ejecución:** Apache HTTP Server (XAMPP).
