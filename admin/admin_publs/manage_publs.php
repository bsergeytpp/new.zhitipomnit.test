<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../../sessions/session.inc.php");

	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if(isset($_GET['page'])) {
			$publsPage = $_GET['page'];
		}
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Управление публикациями</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../../scripts/tinymce/tinymce.min.js"></script>
	<script src="../../scripts/jslib.js"></script>
	<script src="../scripts/admin_script.js"></script>
	<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
</head>
<body>
	<h1>Управление публикациями</h1>
	<a href="/admin">Назад к админке</a>
	<h3>Доступные действия:</h3>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Header</th>
		</tr>
		<? getPublsToTable(); ?>
	</table> 
</body>
</html>