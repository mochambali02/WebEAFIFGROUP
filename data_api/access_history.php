<?php
// --------------------------------------------------------
//  WebEA access history logging (endpoint)
// --------------------------------------------------------
$sRootPath = dirname(dirname(__FILE__));
require_once $sRootPath . '/globals.php';
require_once $sRootPath . '/lib/access_history.php';
SafeStartSession();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(array('response' => 'skipped'));
	exit();
}
if (!IsSessionSettingTrue('authorized')) {
	echo json_encode(array('response' => 'skipped'));
	exit();
}

$sUserId = SafeGetInternalArrayParameter($_SESSION, 'login_user', '');
$sModelName = SafeGetInternalArrayParameter($_SESSION, 'model_name', '');
$sObjectGuid = SafeGetInternalArrayParameter($_POST, 'objectguid', '');
$sResType = SafeGetInternalArrayParameter($_POST, 'restype', '');
$sToken = SafeGetInternalArrayParameter($_POST, 'csrf_token', '');
if (strIsEmpty($sToken) || !isset($_SESSION['csrf_token']) || $sToken !== $_SESSION['csrf_token']) {
	echo json_encode(array('response' => 'skipped'));
	exit();
}

$now = time();
$key = $sResType . ':' . $sObjectGuid;
if (!isset($_SESSION['access_history_last']) || !is_array($_SESSION['access_history_last'])) {
	$_SESSION['access_history_last'] = array();
}
if (isset($_SESSION['access_history_last'][$key]) && ($now - $_SESSION['access_history_last'][$key] < 30)) {
	echo json_encode(array('response' => 'skipped'));
	return;
}
$_SESSION['access_history_last'][$key] = $now;
if (count($_SESSION['access_history_last']) > 100) {
	asort($_SESSION['access_history_last']);
	while (count($_SESSION['access_history_last']) > 100) {
		array_shift($_SESSION['access_history_last']);
	}
}

$result = record_access_history($sUserId, $sModelName, $sObjectGuid, $sResType);
if (!$result['ok']) {
	if ($result['message'] === 'invalid input') {
		setResponseCode(400, _glt('invalid request'));
		return;
	}
	if ($result['message'] === 'invalid guid') {
		setResponseCode(400, _glt('invalid request'));
		return;
	}
	echo json_encode(array('response' => $result['message']));
	return;
}

echo json_encode(array('response' => $result['message']));
