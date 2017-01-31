<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");

	function getUsersToTable() {
		global $db;
		
		$query = 'SELECT user_id, user_login, user_email, user_group FROM users ORDER BY user_id';
		$res = $db->executeQuery($query, null, null);
		
		$usersArr = [
			0 => 'user_id',
			1 => 'user_login',
			2 => 'user_email',
			3 => 'user_group'
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
					default: break;
				}
				$i++;
			}
			echo '</tr>';
			echo '<tr>';
			echo '<td class="edit-btn" colspan="3" style="cursor: pointer;"><strong>Редактировать</strong></td>';
			echo '<td class="delete-btn" colspan="1" style="cursor: pointer;"><strong>Удалить</strong></td>';
			echo '</tr>';
		}
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Управление пользователями</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../../scripts/tinymce/tinymce.min.js"></script>
	<script src="../scripts/admin_script.js"></script>
	<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
</head>
<body>
	<h1>Управление пользователями</h1>
	<a href="../">Назад</a>
	<h3>Доступные действия:</h3>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Login</th>
			<th>Email</th>
			<th>Group</th>
		</tr>
		<? getUsersToTable(); ?>
	</table> 
	<script> editBtnOnClick('users'); </script>
	<script>
		var table = document.getElementsByTagName('table')[0];
		table.addEventListener('click', function(e) {
			var target = e.target;
			
			if(target.tagName !== 'TD') return;
			
			removeSelection(table);
			target.classList.add('selected');
		}, false);
	</script>
</body>
</html>