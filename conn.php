<?php
// DB connection using PDO. Edit the constants below for your environment.
// When you run this on XAMPP, update DB_NAME, DB_USER and DB_PASS accordingly.
define('DB_HOST', '127.0.0.1');
define('DB_PORT', '3306');
define('DB_NAME', 'petadopt'); // <- change to your DB name
define('DB_USER', 'root');          // <- change if needed
define('DB_PASS', '');              // <- change if needed

// PDO options
$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $pdoOptions);
} catch (PDOException $e) {
    error_log('DB connection error: ' . $e->getMessage());
    // For development you can uncomment the next line to see the error in the browser:
    // exit('Database connection failed: ' . $e->getMessage());
    exit('Database connection failed.');
}

/**
 * Helper to get the PDO instance.
 * Usage: $db = getPDO();
 *
 * @return PDO
 */
function getPDO(): PDO {
    global $pdo;
    return $pdo;
}

// Note: intentionally omitting the PHP closing tag to avoid accidental trailing output.