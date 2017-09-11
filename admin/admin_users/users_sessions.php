<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");

	function getSessionsToTable() {
		global $db;
		
		$query = 'SELECT * FROM sessions ORDER BY session_id';
		$res = $db->executeQuery($query, null, null);		
		$usersArr = [
			0 => 'session_id',
			1 => 'session_hash',
			2 => 'session_last_seen',
			3 => 'session_username',
			4 => 'session_data',
			5 => 'session_id',
			6 => 'session_user_agent'
		];
		
		while($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$i = 0;
			echo '<tr>';
			foreach($row as $val) {
				switch($i) {
					case 0: echo '<td name='.$usersArr[$i].'>' . $val . '</td>'; break;
					case 1: echo '<td name='.$usersArr[$i].'>' . $val . '</td>'; break;
					case 2: echo '<td name='.$usersArr[$i].'>' . $val . '</td>'; break;
					case 3: echo '<td name='.$usersArr[$i].'>' . $val . '</td>'; break;
					case 4: echo '<td name='.$usersArr[$i].'>' . $val . '</td>'; break;
					case 5: echo '<td name='.$usersArr[$i].'>' . $val . '</td>'; break;
					case 6: echo '<td name='.$usersArr[$i].'>' . $val . '</td>'; break;
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
	<a href="/admin">Назад к админке</a>
	<h3>Доступные действия:</h3>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Session id</th>
			<th>Last seen</th>
			<th>User</th>
			<th>Data</th>
			<th>IP</th>
			<th>UserAgent</th>
		</tr>
		<? getSessionsToTable(); ?>
	</table> 
</body>
</html>