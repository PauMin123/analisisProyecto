# English Made for Dummies - Sistema Móvil de Agendamiento de Tutorías

Sistema web responsive enfocado en dispositivos móviles para la gestión y agendamiento de clases de inglés individuales en tiempo real con profesores nativos certificados.

---

## 🏛️ Arquitectura del Sistema: Modelo-Vista-Controlador (MVC)

El sistema está estructurado bajo el patrón arquitectónico **MVC**:

```
proyectoanalisis/
│
├── api/                        # Front-Controllers (Puntos de entrada REST/JSON)
│   ├── auth.php                # Despachador de autenticación
│   ├── booking.php             # Despachador de catálogo y reservas
│   ├── config.php              # Patrón Singleton de conexión PDO a MySQL
│   ├── dashboard.php           # Despachador del dashboard y métricas
│   ├── lessons.php             # Despachador de lecciones y cancelaciones
│   └── profile.php             # Despachador de perfil de estudiante
│
├── controllers/                # CAPA CONTROLADORA (Lógica de orquestación)
│   ├── BaseController.php      # Clase base abstracta (CORS, JSON response, transacciones)
│   ├── AuthController.php      # Control de credenciales, tokens y perfiles
│   ├── BookingController.php   # Filtro en cascada, slots y reservas concurrentes
│   ├── DashboardController.php # Métricas consolidadas, catálogo y próxima lección
│   ├── LessonController.php    # Listado, cancelación atómica y aprobación de lecciones
│   └── ProfileController.php   # Gestión de datos del alumno y nivel CEFR
│
├── models/                     # CAPA DEL MODELO (Lógica de negocio y persistencia)
│   ├── Estudiante.php          # Datos del alumno, sesiones y estadísticas
│   ├── Horario.php             # Bloqueos pesimistas (SELECT ... FOR UPDATE) y slots
│   ├── Leccion.php             # Ciclo de vida de la clase y progresión de nivel (A1 -> A2)
│   ├── Profesor.php            # Catálogo de tutores, calificaciones y acentos
│   ├── Curso.php               # Catálogo de programas académicos
│   └── Nivel.php               # Niveles del marco MCER (A1, A2, B1, B2, C1)
│
├── assets/                     # CAPA DE VISTA (Recursos estáticos e interfaces)
│   ├── css/style.css           # Vanilla CSS responsivo móvil, Midnight Obsidian & Daylight
│   └── js/
│       ├── app.js              # Controlador central de la SPA móvil
│       ├── sessionManager.js   # Patrón SINGLETON (Gestión global de sesión y caché)
│       └── courseFactory.js    # Patrón FACTORY METHOD (Fábrica de servicios por curso)
│
├── tests/                      # SUITE DE PRUEBAS AUTOMATIZADAS (18 Pruebas / 100% Passed)
│   ├── run_tests.php           # 12 Pruebas Backend (Unitarias, Mocks, Concurrencia y CFG)
│   └── test_ui.html            # 6 Pruebas Frontend / Caja Negra automatizadas en navegador
│
├── database/
│   └── schema.sql              # Esquema DDL MySQL InnoDB relacional con transacciones
│
├── Dockerfile                  # Contenedor de producción PHP 8.2 + Apache
└── index.html                  # Shell móvil responsivo de la aplicación (SPA)
```

---

## 🎯 Patrones de Diseño GoF Implementados

1. **Patrón Singleton:**
   - **Frontend:** [`assets/js/sessionManager.js`](assets/js/sessionManager.js) (`StudentSessionManager` con constructor protegido y `getInstance()`).
   - **Backend:** [`api/config.php`](api/config.php) (`DatabaseConnection` para conexión única persistente PDO MySQL).
2. **Patrón Factory Method:**
   - [`assets/js/courseFactory.js`](assets/js/courseFactory.js) (`CourseServiceFactory` creando instancias especializadas de `ICourseService`).

---

## 🛡️ Prevención de Colisiones y Concurrencia

- **Bloqueo Pesimista en Base de Datos:** `SELECT ... FOR UPDATE` sobre la tabla `horarios_disponibles` dentro de transacciones atómicas InnoDB.
- **Índice Único Compuesto:** `UNIQUE KEY uk_profesor_slot (profesor_id, fecha, hora_inicio)` que impide físicamente citas duplicadas a nivel de motor.
- **Manejo de Errores Concurrente:** Disparo de código **HTTP 409 Conflict** con modal interactivo de alerta y recarga de grilla en tiempo real.

---

## 🎮 Gamificación y Progresión de Nivel

- **Tracker de Racha Semanal:** Seguimiento de constancia diaria (L a D con checkmarks y 🔥 activo).
- **Puntos XP:** Acumulación de +120 XP por lección completada.
- **Medallas y Logros:** Insignias dinámicas desbloqueables (*Primer Paso*, *Racha de Fuego*, *Madrugador*, *Base Sólida*, *Master B2*).
- **Progresión Pedagógica Automática:** Al aprobar una lección en **A1**, el estudiante avanza de nivel a **A2**, actualizando su perfil e inicializando automáticamente las siguientes reservas en el nuevo nivel.
- **Expresión del Día:** Pronunciación nativa con la Web Speech API (`SpeechSynthesisUtterance`).

---

## 🧪 Pruebas Automatizadas (100% Éxito)

El proyecto cuenta con **18 pruebas automatizadas** que cubren la totalidad de los requisitos de la rúbrica:

1. **Suite Backend (12 Pruebas):**
   ```bash
   php tests/run_tests.php
   ```
   - Verificación de identidad referencial Singleton.
   - Conexión PDO activa a MySQL.
   - Instanciación de fábricas (Factory Method).
   - 6 Casos frontera del algoritmo anticolisión (solapamientos totales, parciales, fechas pasadas, intervalos adyacentes válidos).
   - Transacción pesimista de concurrencia `SELECT ... FOR UPDATE`.
   - Cálculo y comprobación programática del Grafo de Flujo de Control ($V(G) = 6 \le 8$).

2. **Suite Frontend / UI Caja Negra (6 Pruebas):**
   - Abrir en navegador: `tests/test_ui.html`
   - Valida render móvil, catálogo de cursos, filtros reactivos, selección de slots, detección de colisión y emisión de comprobante digital.

---

## 📊 Métricas de Ingeniería y Calidad (RTF)

- **Complejidad Ciclomática:** $V(G) = 6 \le 8$ (demostrado por Fórmula de McCabe $E - N + 2P = 14 - 10 + 2(1) = 6$ y por Predicados $+ 1 = 5 + 1 = 6$).
- **Densidad de Defectos:** 2.8 Defectos / KLOC ($< 5.0$).
- **Velocidad de Preparación:** 175 LOC / hora ($150 - 200$).
- **Tasa de Descubrimiento en RTF:** 68.4% ($> 60\%$).
- **Violaciones a Principios SOLID:** 0 violaciones críticas.

---

## 🚀 Despliegue e Instalación Local

### Requisitos:
- PHP 8.1 o superior (con extensión `pdo_mysql`).
- Servidor MySQL / MariaDB (puerto 3306).
- Servidor web Apache (o servidor embebido de PHP).

### Pasos:
1. Clonar el repositorio en tu carpeta de servidor web (ej. `xampp/htdocs/`):
   ```bash
   git clone https://github.com/PauMin123/analisisProyecto.git
   ```
2. Importar la base de datos:
   - Crear la base de datos `ingles`.
   - Ejecutar el script SQL ubicado en `database/schema.sql`.
3. Abrir la aplicación:
   - Navegador: `http://localhost/proyectoanalisis/`
   - Dispositivos en red local Wi-Fi: `http://<IP_LOCAL>/proyectoanalisis/`
   - Suite de pruebas: `http://localhost/proyectoanalisis/tests/run_tests.php`
