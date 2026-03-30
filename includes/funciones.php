<?php
require_once __DIR__ . '/../config/database.php';

// Iniciar sesion segura
function iniciarSesion() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => 3600,
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

// Sanitizar entrada
function limpiar($valor) {
    return trim(strip_tags($valor));
}

// Verificar autenticacion
function requerirAuth() {
    iniciarSesion();
    if (empty($_SESSION['usuario_id'])) {
        header('Location: ' . BASE_URL . 'login.php');
        exit;
    }
}

// Login
function login(string $correo, string $password): array {
    $db = getDB();
    $stmt = $db->prepare(
        "SELECT * FROM usuarios WHERE correo = ? AND activo = 1 LIMIT 1"
    );
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($password, $usuario['password'])) {
        return ['ok' => false, 'msg' => 'Correo o contrasena incorrectos.'];
    }

    iniciarSesion();
    session_regenerate_id(true);
    $_SESSION['usuario_id']  = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['login_time']  = time();
    return ['ok' => true];
}

// Logout
function logout() {
    iniciarSesion();
    $_SESSION = [];
    session_destroy();
}
