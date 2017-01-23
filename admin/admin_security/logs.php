<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");

	function getLogsToTable() {
		global $link;
		$link = connectToPostgres();
		
		$query = 'SELECT * FROM logs ORDER BY log_id';
		$res = pg_query($link, $query) or die('Query error: '. pg_last_error());
		
		$logsArr = [
			0 => 'log_id',
			1 => 'log_name',
			2 => 'log_text',
			3 => 'log_date',
			4 => 'log_important',
			5 => 'log_location'
		];
		
		while($row = pg_fetch_assoc($res)) {
			$i = 0;
			echo '<tr>';
			foreach($row as $val) {
				switch($i) {
					case 0: echo '<td name='.$logsArr[$i].'>' . $val . '</td>'; break;
					case 1: echo '<td name='.$logsArr[$i].'>' . $val . '</td>'; break;
					case 2: echo '<td name='.$logsArr[$i].'>' . $val . '</td>'; break;
					case 3: echo '<td name='.$logsArr[$i].'>' . $val . '</td>'; break;
					case 4: echo '<td name='.$logsArr[$i].'>' . $val . '</td>'; break;
					case 5: echo '<td name='.$logsArr[$i].'>' . $val . '</td>'; break;
					default: break;
				}
				$i++;
			}
			echo '</tr>';
		}
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Логи</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../scripts/admin_script.js"></script>
	<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
</head>
<body>
	<h1>Логи</h1>
	<a href="../">Назад</a>
	<h3>Доступные действия:</h3>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Name</th>
			<th>Text</th>
			<th>Date</th>
			<th>Important</th>
			<th>Location</th>
		</tr>
		<? getLogsToTable(); ?>
	</table> 
</body>
</html>