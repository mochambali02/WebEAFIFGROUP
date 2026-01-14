<?php
$sRootPath = dirname(__DIR__);
require_once $sRootPath . '/globals.php';
SafeStartSession();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['response' => 'skipped']);
    exit();
}

$sToken = SafeGetInternalArrayParameter($_POST, 'csrf_token', '');
if (strIsEmpty($sToken) || !isset($_SESSION['csrf_token']) || $sToken !== $_SESSION['csrf_token']) {
    echo json_encode(['response' => 'skipped']);
    exit();
}

$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$modelName = isset($_POST['model_name']) ? trim($_POST['model_name']) : '';
$loginResult = isset($_POST['login_result']) ? trim($_POST['login_result']) : '';
$authType = isset($_POST['auth_type']) ? trim($_POST['auth_type']) : '';
$errorMessage = isset($_POST['error_message']) ? trim($_POST['error_message']) : '';
if ($username === '') {
    echo json_encode(['response' => 'Username is required']);
    exit();
}
if (strlen($username) > 100) {
    echo json_encode(['response' => 'Invalid username']);
    exit();
}
if (!preg_match('/^[A-Za-z0-9._\\\\@-]+$/', $username)) {
    echo json_encode(['response' => 'Invalid username']);
    exit();
}
if ($modelName !== '' && strlen($modelName) > 255) {
    echo json_encode(['response' => 'Invalid model']);
    exit();
}
if ($loginResult !== 'success' && $loginResult !== 'failed') {
    echo json_encode(['response' => 'Invalid login_result']);
    exit();
}
if ($authType !== '' && (strlen($authType) > 20 || !preg_match('/^[A-Za-z0-9_-]+$/', $authType))) {
    echo json_encode(['response' => 'Invalid auth_type']);
    exit();
}
if ($errorMessage !== '' && strlen($errorMessage) > 500) {
    $errorMessage = substr($errorMessage, 0, 500);
}

$serverName = getenv('WEBEA_DB_HOST') ?: '';
$dbName = getenv('WEBEA_DB_NAME') ?: '';
$dbUser = getenv('WEBEA_DB_USER') ?: '';
$dbPass = getenv('WEBEA_DB_PASS') ?: '';

if ($serverName === '' || $dbName === '' || $dbUser === '' || $dbPass === '') {
    error_log('login_history: missing DB env vars');
    echo json_encode(['response' => 'Database configuration is missing']);
    exit();
}

$connectionInfo = array('Database' => $dbName, 'UID' => $dbUser, 'PWD' => $dbPass);
$conn = sqlsrv_connect($serverName, $connectionInfo);
if ($conn === false) {
    error_log('login_history: db connect failed');
    echo json_encode(['response' => 'Database connection failed']);
    exit();
}

try {
    $insert = 'INSERT INTO dbo.t_loginhistory (user_id, model_name, login_result, auth_type, error_message, login_at) VALUES (?, ?, ?, ?, ?, GETDATE())';
    $params = array($username, $modelName, $loginResult, $authType, $errorMessage);
    $query = sqlsrv_query($conn, $insert, $params);
    if ($query === false) {
        error_log('login_history: insert failed');
        sqlsrv_close($conn);
        echo json_encode(['response' => 'An error occurred']);
        exit();
    }

    sqlsrv_close($conn);
    echo json_encode(['response' => 'Success']);
} catch (Exception $e) {
    error_log('login_history: exception');
    if ($conn) {
        sqlsrv_close($conn);
    }
    echo json_encode(['response' => 'An error occurred']);
}
