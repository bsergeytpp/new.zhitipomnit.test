<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Управление пользователями</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../../scripts/tinymce/tinymce.min.js"></script>
	<script src="../../scripts/jslib.js"></script>
	<script src="../scripts/admin_script.js"></script>
	<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
</head>
<body>
	<h1>Управление пользователями</h1>
	<a href="/admin">Назад к админке</a>
	<h3>Доступные действия:</h3>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Login</th>
			<th>Email</th>
			<th>Group</th>
			<th>Reg date</th>
			<th>Last login</th>
		</tr>
		<? getUsersToTable(); ?>
	</table> 
</body>
</html>