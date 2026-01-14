<?php
// --------------------------------------------------------
//  WebEA access history logging (library)
// --------------------------------------------------------
function is_valid_access_guid($sGuid, $sResType)
{
	if (strIsEmpty($sGuid) || strIsEmpty($sResType)) {
		return false;
	}
	if ($sResType === 'Package') {
		return (bool)preg_match('/^pk_\\{[0-9A-Fa-f-]{36}\\}$/', $sGuid);
	}
	if ($sResType === 'Diagram') {
		return (bool)preg_match('/^(dg|di)_\\{[0-9A-Fa-f-]{36}\\}$/', $sGuid);
	}
	return false;
}

function record_access_history($sUserId, $sModelName, $sObjectGuid, $sResType)
{
	if (strIsEmpty($sUserId) || strIsEmpty($sModelName) || strIsEmpty($sObjectGuid)) {
		return array('ok' => false, 'message' => 'invalid input');
	}
	if (strlen($sUserId) > 100 || strlen($sModelName) > 255) {
		return array('ok' => false, 'message' => 'invalid input');
	}
	if (!is_valid_access_guid($sObjectGuid, $sResType)) {
		return array('ok' => false, 'message' => 'invalid guid');
	}

	$sPackageGuid = '';
	$sDiagramGuid = '';
	if ($sResType === 'Package') {
		$sPackageGuid = $sObjectGuid;
	} elseif ($sResType === 'Diagram') {
		$sDiagramGuid = $sObjectGuid;
	} else {
		return array('ok' => true, 'message' => 'skipped');
	}

	$serverName = getenv('WEBEA_DB_HOST') ?: '';
	$dbName = getenv('WEBEA_DB_NAME') ?: '';
	$dbUser = getenv('WEBEA_DB_USER') ?: '';
	$dbPass = getenv('WEBEA_DB_PASS') ?: '';

	if ($serverName === '' || $dbName === '' || $dbUser === '' || $dbPass === '') {
		return array('ok' => false, 'message' => 'config missing');
	}

	$connectionInfo = array('Database' => $dbName, 'UID' => $dbUser, 'PWD' => $dbPass);
	$conn = sqlsrv_connect($serverName, $connectionInfo);
	if ($conn === false) {
		return array('ok' => false, 'message' => 'db error');
	}

	$sql = 'INSERT INTO dbo.t_accesshistory (user_id, model_name, package_guid, diagram_guid, accessed_at) VALUES (?, ?, ?, ?, GETDATE())';
	$params = array($sUserId, $sModelName, $sPackageGuid, $sDiagramGuid);
	$query = sqlsrv_query($conn, $sql, $params);
	if ($query === false) {
		sqlsrv_close($conn);
		return array('ok' => false, 'message' => 'insert failed');
	}

	sqlsrv_close($conn);
	return array('ok' => true, 'message' => 'success');
}
