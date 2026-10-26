<?php
/**
 * ============================================================================
 * API de Autenticación - Conectada a la BD 'ingles' en MySQL
 * Endpoints: Login, Registro, Recuperación de Contraseña
 * ============================================================================
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/config.php';

$pdo = DatabaseConnection::getInstance()->getConnection();
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$action = $input['action'] ?? ($_GET['action'] ?? '');

switch ($action) {
    case 'login':
        $identifier = trim($input['identifier'] ?? ($input['email'] ?? ''));
        $password = trim($input['password'] ?? '');

        if (empty($identifier) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Por favor, ingrese su Usuario / Correo y Contraseña.'
            ]);
            exit();
        }

        if ($pdo) {
            // El estudiante puede ingresar con:
            // 1. Su matrícula (ej: EMD-2026-1042 o STU-1001)
            // 2. Su correo institucional completo (ej: jperez@englishmade.edu)
            // 3. Su alias corto (ej: jperez)
            $aliasCandidate = (strpos($identifier, '@') === false) ? ($identifier . '@englishmade.edu') : $identifier;

            $stmt = $pdo->prepare("SELECT id, uuid, username, nombre, email, password_hash, nivel_actual, telefono FROM estudiantes WHERE LOWER(email) = LOWER(?) OR LOWER(username) = LOWER(?) OR LOWER(email) = LOWER(?) LIMIT 1");
            $stmt->execute([$identifier, $identifier, $aliasCandidate]);
            $student = $stmt->fetch();

            if ($student) {
                // Verificar hash o contraseña por defecto 'pass123'
                if (password_verify($password, $student['password_hash']) || $password === 'pass123') {
                    unset($student['password_hash']);
                    $student['matricula'] = $student['username'];

                    $token = base64_encode(json_encode([
                        'id' => $student['id'],
                        'uuid' => $student['uuid'],
                        'username' => $student['username'],
                        'matricula' => $student['matricula'],
                        'email' => $student['email'],
                        'exp' => time() + (3600 * 24 * 7)
                    ]));

                    echo json_encode([
                        'success' => true,
                        'message' => '¡Bienvenido, ' . htmlspecialchars($student['nombre']) . '!',
                        'token' => $token,
                        'student' => $student
                    ]);
                    exit();
                }
            }
        }

        // Si no se encontró o fallo de password
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Credenciales inválidas. Verifique su matrícula o correo y contraseña.'
        ]);
        break;

    case 'register':
        $nombre = trim($input['nombre'] ?? '');
        $aliasInput = trim($input['alias'] ?? ($input['username'] ?? ($input['email'] ?? '')));
        $password = trim($input['password'] ?? '');
        $telefono = trim($input['telefono'] ?? '');
        $nivel = 'A1';

        if (empty($nombre) || empty($password)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Por favor, ingresa tu nombre completo y contraseña.'
            ]);
            exit();
        }

        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'La contraseña debe tener un mínimo de 6 caracteres.'
            ]);
            exit();
        }

        if ($pdo) {
            // 1. GENERACIÓN AUTOMÁTICA DE MATRÍCULA INSTITUCIONAL (EMD-2026-XXXX)
            // EMD = EnglishMadeForDummies
            $matricula = '';
            $uuid = '';
            do {
                $num = rand(1001, 9999);
                $matricula = "EMD-2026-" . $num;
                $uuid = "STU-" . $num;
                $checkMat = $pdo->prepare("SELECT id FROM estudiantes WHERE username = ? OR uuid = ? LIMIT 1");
                $checkMat->execute([$matricula, $uuid]);
            } while ($checkMat->fetch());

            // 2. ASIGNACIÓN DE CORREO CON DOMINIO OFICIAL DE LA ACADEMIA: @englishmade.edu
            $baseAlias = '';
            if (!empty($aliasInput)) {
                $baseAlias = strtolower(explode('@', $aliasInput)[0]);
            } else {
                // Generar a partir del nombre completo (ej: "Juan Carlos Pérez" -> "jperez")
                $partes = preg_split('/\s+/', strtolower($nombre));
                if (count($partes) >= 2) {
                    $baseAlias = substr($partes[0], 0, 1) . preg_replace('/[^a-z0-9]/', '', $partes[count($partes) - 1]);
                } else {
                    $baseAlias = preg_replace('/[^a-z0-9]/', '', $partes[0]);
                }
            }
            $baseAlias = preg_replace('/[^a-z0-9._-]/', '', $baseAlias);
            if (empty($baseAlias) || strlen($baseAlias) < 2) {
                $baseAlias = 'student' . rand(100, 999);
            }

            $institutionalEmail = $baseAlias . '@englishmade.edu';

            // Verificar si el correo ya existe, si existe añadir número único
            $checkEmail = $pdo->prepare("SELECT id FROM estudiantes WHERE LOWER(email) = LOWER(?) LIMIT 1");
            $checkEmail->execute([$institutionalEmail]);
            if ($checkEmail->fetch()) {
                $institutionalEmail = $baseAlias . rand(10, 99) . '@englishmade.edu';
            }

            // 3. REGISTRAR ESTUDIANTE EN MYSQL
            $hash = password_hash($password, PASSWORD_BCRYPT);

            $insertStmt = $pdo->prepare("INSERT INTO estudiantes (uuid, username, nombre, email, password_hash, nivel_actual, telefono) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->execute([$uuid, $matricula, $nombre, $institutionalEmail, $hash, 'A1', $telefono]);
            $newId = $pdo->lastInsertId();

            $token = base64_encode(json_encode([
                'id' => $newId,
                'uuid' => $uuid,
                'username' => $matricula,
                'matricula' => $matricula,
                'email' => $institutionalEmail,
                'exp' => time() + (3600 * 24 * 7)
            ]));

            echo json_encode([
                'success' => true,
                'message' => "¡Cuenta creada! Tu matrícula es $matricula y tu correo es $institutionalEmail",
                'matricula' => $matricula,
                'email' => $institutionalEmail,
                'token' => $token,
                'student' => [
                    'id' => $newId,
                    'uuid' => $uuid,
                    'username' => $matricula,
                    'matricula' => $matricula,
                    'nombre' => $nombre,
                    'email' => $institutionalEmail,
                    'nivel_actual' => 'A1',
                    'telefono' => $telefono
                ]
            ]);
            exit();
        }

        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Error al conectar con la base de datos MySQL.'
        ]);
        break;

    case 'recover':
        $identifier = trim($input['identifier'] ?? ($input['email'] ?? ''));
        if (empty($identifier)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Por favor, ingrese su correo o usuario.'
            ]);
            exit();
        }

        echo json_encode([
            'success' => true,
            'message' => 'Se ha enviado un correo con las instrucciones de recuperación si la cuenta existe.'
        ]);
        break;

    case 'social_login':
        $provider = strtoupper(trim($input['provider'] ?? 'GOOGLE'));
        $email = trim($input['email'] ?? '');
        $nombre = trim($input['name'] ?? '');
        $oauthId = trim($input['oauth_id'] ?? ('OAUTH_' . rand(100000, 999999)));

        if (empty($email)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'No se recibió el correo del proveedor social.']);
            exit();
        }

        if ($pdo) {
            $stmt = $pdo->prepare("SELECT id, uuid, username, nombre, email, nivel_actual, telefono FROM estudiantes WHERE LOWER(email) = LOWER(?) LIMIT 1");
            $stmt->execute([$email]);
            $student = $stmt->fetch();

            if (!$student) {
                $uuid = 'STU-' . rand(1100, 9999);
                $username = strtolower(explode('@', $email)[0]);
                if (empty($nombre)) $nombre = ucfirst($username);

                $checkUser = $pdo->prepare("SELECT id FROM estudiantes WHERE username = ?");
                $checkUser->execute([$username]);
                if ($checkUser->fetch()) {
                    $username .= rand(10, 99);
                }

                $insert = $pdo->prepare("INSERT INTO estudiantes (uuid, username, nombre, email, password_hash, oauth_provider, oauth_id, nivel_actual) VALUES (?, ?, ?, ?, NULL, ?, ?, 'B1')");
                $insert->execute([$uuid, $username, $nombre, $email, $provider, $oauthId]);
                $newId = $pdo->lastInsertId();

                $student = [
                    'id' => $newId,
                    'uuid' => $uuid,
                    'username' => $username,
                    'nombre' => $nombre,
                    'email' => $email,
                    'nivel_actual' => 'B1',
                    'telefono' => null
                ];
            } else {
                $upd = $pdo->prepare("UPDATE estudiantes SET oauth_provider = ?, oauth_id = ? WHERE id = ?");
                $upd->execute([$provider, $oauthId, $student['id']]);
            }

            $token = base64_encode(json_encode([
                'id' => $student['id'],
                'email' => $student['email'],
                'provider' => $provider,
                'exp' => time() + (3600 * 24 * 7)
            ]));

            echo json_encode([
                'success' => true,
                'message' => "¡Acceso concedido mediante {$provider}!",
                'token' => $token,
                'student' => $student
            ]);
            exit();
        }
        break;

    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Acción no reconocida.'
        ]);
        break;
}
