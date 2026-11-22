<?php
/**
 * Configure with DB_HOST, DB_PORT, DB_NAME, DB_USER and DB_PASSWORD.
 * The default empty-password root account is for local XAMPP only.
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$dbHost = getenv('DB_HOST') ?: 'localhost';
$dbPort = (int) (getenv('DB_PORT') ?: 3306);
$dbName = getenv('DB_NAME') ?: 'edoc';
$dbUser = getenv('DB_USER') ?: 'root';
$dbPassword = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

try {
    $database = new mysqli($dbHost, $dbUser, $dbPassword, $dbName, $dbPort);
    $database->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('Hospital Management System database connection failed: ' . $exception->getMessage());
    http_response_code(500);
    exit('Database connection unavailable. Check the server configuration.');
}
?>