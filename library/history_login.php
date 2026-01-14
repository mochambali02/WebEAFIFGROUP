<?php
$dateTime = new DateTime("now", new DateTimeZone('GMT'));
$date = $dateTime->format("Y-m-d");
if (!empty($_POST['username'])) {
	$serverName = "BSDVSQLPRD075"; //serverName\instanceName
	$connectionInfo = array("Database" => "EAAFIF-PRIMARY", "UID" => "SA", "PWD" => "HailHydra!!!");
	try {
		$conn = sqlsrv_connect($serverName, $connectionInfo);
		$select = "SELECT * from dbo.ConnectionWebea WHERE Username LIKE '" . $_POST['username'] . "' AND Created_at IN('" . $date . "')";
		$sql = "INSERT INTO dbo.ConnectionWebea(Username, Created_at) VALUES('" . $_POST['username'] . "', '" . $date . "')";
		$count = sqlsrv_query($conn, $select, array(), array("Scrollable" => 'static'));
		$validasi = sqlsrv_num_rows($count);
		if ($validasi < 1) {
			$query = sqlsrv_query($conn, $sql);
			sqlsrv_close($conn);
			echo json_encode(['response' => 'Sukses']);
		} else {
			sqlsrv_close($conn);
			echo json_encode(['response' => 'Sudah terecord']);
		}
	} catch (\Exception $e) {
		sqlsrv_close($conn);
		echo json_encode(['response' => 'Error: ' . $e->getMessage()]);
	}
} else {
	echo json_encode(['response' => 'username belum terisi']);
}
