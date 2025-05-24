*/

// --- api/auth.php ---
/*
<?php
// api/auth.php
require_once '../config/db.php';
require_once '../includes/functions.php'; // Para sendJsonResponse
require_once '../includes/session_handler.php'; // Para session_start, regenerateSessionAfterLogin

$action = $_POST['action'] ?? $_GET['action'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($action === 'register') {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            sendJsonResponse(['success' => false, 'message' => 'Email y contraseña son requeridos.'], 400);
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            sendJsonResponse(['success' => false, 'message' => 'Formato de email inválido.'], 400);
        }
        if (strlen($password) < 6) { // Ejemplo de validación de contraseña
            sendJsonResponse(['success' => false, 'message' => 'La contraseña debe tener al menos 6 caracteres.'], 400);
        }

        try {
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            if ($stmt->fetch()) {
                sendJsonResponse(['success' => false, 'message' => 'El email ya está registrado.'], 409); // Conflict
            }

            $hashedPassword = password_hash($password, PASSWORD_BCRYPT); // Usar BCRYPT o ARGON2
            $stmt = $pdo->prepare("INSERT INTO users (email, password_hash) VALUES (:email, :password_hash)");
            $stmt->execute(['email' => $email, 'password_hash' => $hashedPassword]);
            
            sendJsonResponse(['success' => true, 'message' => 'Usuario registrado exitosamente. Por favor, inicie sesión.']);

        } catch (PDOException $e) {
            // Log error $e->getMessage()
            sendJsonResponse(['success' => false, 'message' => 'Error del servidor durante el registro.'], 500);
        }

    } elseif ($action === 'login') {
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            sendJsonResponse(['success' => false, 'message' => 'Email y contraseña son requeridos.'], 400);
        }

        try {
            $stmt = $pdo->prepare("SELECT id, email, password_hash FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login exitoso, establecer variables de sesión
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                
                regenerateSessionAfterLogin(); // Prevenir fijación de sesión

                sendJsonResponse([
                    'success' => true, 
                    'message' => 'Login exitoso.',
                    'user' => ['id' => $user['id'], 'email' => $user['email']]
                ]);
            } else {
                sendJsonResponse(['success' => false, 'message' => 'Credenciales inválidas.'], 401); // Unauthorized
            }
        } catch (PDOException $e) {
            // Log error $e->getMessage()
            sendJsonResponse(['success' => false, 'message' => 'Error del servidor durante el login.'], 500);
        }
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Acción POST no válida.'], 400);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($action === 'logout') {
        session_unset(); // Eliminar todas las variables de sesión
        session_destroy(); // Destruir la sesión
        sendJsonResponse(['success' => true, 'message' => 'Logout exitoso.']);
    } elseif ($action === 'status') {
        if (isLoggedIn()) {
            sendJsonResponse([
                'success' => true, 
                'loggedIn' => true, 
                'user' => ['id' => $_SESSION['user_id'], 'email' => $_SESSION['user_email']]
            ]);
        } else {
            sendJsonResponse(['success' => true, 'loggedIn' => false]);
        }
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Acción GET no válida.'], 400);
    }
} else {
    sendJsonResponse(['success' => false, 'message' => 'Método HTTP no soportado.'], 405); // Method Not Allowed
}
?>
