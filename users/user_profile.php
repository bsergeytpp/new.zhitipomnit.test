<?
	session_start();
	require_once "../functions/functions.php";
	function getUserData() {
		global $link;
		$link = connectToPostgres();
		$user = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
		
		$query = "SELECT user_login, user_email, user_group FROM users WHERE user_login LIKE '".$user."'";
		$res = pg_query($link, $query) or die('Query error: '. pg_last_error());
		
		while($row = pg_fetch_assoc($res)) {
			echo '<tr>';
			foreach($row as $val) {
				echo '<td>' . $val . '</td>';
			}
			echo '</tr>';
		}
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Профиль пользователя</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../scripts/tinymce/tinymce.min.js"></script>
	<script src="scripts/admin_script.js"></script>
</head>
<body>
	<h1>Профиль пользователя</h1>
	<a href="login.php">Назад</a><br> 
	<a href="login.php?logout">Выйти</a>
	<h3>Данные:</h3>
	<table border='1'>
		<tr>
			<th>Логин</th>
			<th>Email</th>
			<th>Группа</th>
		</tr>
		<? getUserData(); ?>
	</table> 
</body>
</html>