<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");

	function getSessionsToTable() {
		global $link;
		$link = connectToPostgres();
		
		$query = 'SELECT session_id, session_hash, session_last_seen, "session_user" FROM sessions ORDER BY session_id';
		$res = executeQuery($query);		
		$usersArr = [
			0 => 'session_id',
			1 => 'session_hash',
			2 => 'session_last_seen',
			3 => 'session_user'
		];
		
		while($row = pg_fetch_assoc($res)) {
			$i = 0;
			echo '<tr>';
			foreach($row as $val) {
				switch($i) {
					case 0: echo '<td name='.$usersArr[$i].'>' . $val . '</td>'; break;
					case 1: echo '<td name='.$usersArr[$i].'>' . $val . '</td>'; break;
					case 2: echo '<td name='.$usersArr[$i].'>' . $val . '</td>'; break;
					case 3: echo '<td name='.$usersArr[$i].'>' . $val . '</td>'; break;
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
	<title>Сессии пользователей</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../scripts/admin_script.js"></script>
	<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
</head>
<body>
	<h1>Сессии пользователей</h1>
	<a href="../">Назад</a>
	<h3>Доступные действия:</h3>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Session id</th>
			<th>Last seen</th>
			<th>User</th>
		</tr>
		<? getSessionsToTable(); ?>
	</table> 
</body>
</html>