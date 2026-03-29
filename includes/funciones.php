<?php
// ============================================================
//  SAGD – Funciones auxiliares y autenticación
// ============================================================

require_once __DIR__ . '/../config/database.php';

// ── Iniciar sesión segura ────────────────────────────────────
function iniciarSesionSegura(): void {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_TIMEOUT,
            'path'     => '/',
            'secure'   => false,   // true en producción con HTTPS
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        session_start();
    }
}

// ── Verificar si hay sesión activa ───────────────────────────
function estaAutenticado(): bool {
    iniciarSesionSegura();
    if (!isset($_SESSION['usuario_id'], $_SESSION['ultimo_acceso'])) return false;
    if ((time() - $_SESSION['ultimo_acceso']) > SESSION_TIMEOUT) {
        session_destroy();
        return false;
    }
    $_SESSION['ultimo_acceso'] = time();
    return true;
}

// ── Redirigir si no está autenticado ────────────────────────
function requerirAuth(): void {
    if (!estaAutenticado()) {
        header('Location: ../login.php?msg=sesion_expirada');
        exit;
    }
}

// ── Login ────────────────────────────────────────────────────
function login(string $correo, string $password): array {
    $db   = getDB();
    $stmt = $db->prepare("SELECT * FROM usuarios WHERE correo = ? AND activo = 1 LIMIT 1");
    $stmt->execute([$correo]);
    $usuario = $stmt->fetch();

    if (!$usuario || !password_verify($password, $usuario['password'])) {
        return ['ok' => false, 'msg' => 'Correo o contraseña incorrectos.'];
    }

    iniciarSesionSegura();
    session_regenerate_id(true);
    $_SESSION['usuario_id']    = $usuario['id'];
    $_SESSION['usuario_nombre'] = $usuario['nombre'];
    $_SESSION['usuario_rol']   = $usuario['rol'];
    $_SESSION['ultimo_acceso'] = time();

    return ['ok' => true];
}

// ── Logout ───────────────────────────────────────────────────
function logout(): void {
    iniciarSesionSegura();
    session_unset();
    session_destroy();
    header('Location: ../index.php');
    exit;
}

// ── Sanitizar entrada ────────────────────────────────────────
function limpiar(string $valor): string {
    return htmlspecialchars(strip_tags(trim($valor)), ENT_QUOTES, 'UTF-8');
}

// ── Paginación ───────────────────────────────────────────────
function paginar(int $total, int $porPagina, int $paginaActual): array {
    $totalPaginas = (int) ceil($total / $porPagina);
    $offset = ($paginaActual - 1) * $porPagina;
    return ['total_paginas' => $totalPaginas, 'offset' => $offset];
}

// ── Respuesta JSON ───────────────────────────────────────────
function jsonResponse(bool $ok, string $msg, array $data = []): void {
    header('Content-Type: application/json');
    echo json_encode(array_merge(['ok' => $ok, 'msg' => $msg], $data));
    exit;
}
