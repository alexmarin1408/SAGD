<?php
// ============================================================
//  SAGD – Configuración de base de datos
// ============================================================

define('DB_HOST', 'localhost');
define('DB_NAME', 'sagd_db');
define('DB_USER', 'root');          // Cambiar en producción
define('DB_PASS', '');              // Cambiar en producción
define('DB_CHARSET', 'utf8mb4');

define('SITE_NAME', 'SAGD – Iglesia Cristiana Emanuel');
define('SESSION_TIMEOUT', 3600);   // 1 hora

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['error' => 'Error de conexión a la base de datos.']));
        }
    }
    return $pdo;
}
