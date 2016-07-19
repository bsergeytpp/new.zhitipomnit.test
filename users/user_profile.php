<?
	session_start();
	require_once "../functions/functions.php";
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