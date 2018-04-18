<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	$logImportant = false;
	$logType = 0;
	$logParams = ['type' => 0,'importance' => false];
	$logPage = 1;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if(isset($_POST['log-important'])) {
			$logImportant = $_POST['log-important'];
		}
		if(isset($_POST['log-type'])) {
			$logType = $_POST['log-type'];
		}
		
		$logParams['type'] = $logType;
		$logParams['importance'] = $logImportant;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if(isset($_GET['page'])) {
			$logPage = $_GET['page'];
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
	<a href="/admin">Назад к админке</a>
	<h3>Доступные действия:</h3>
	<form action="logs.php" method="POST" class="comments-form">
		<p>
			<span>Категория логов:</span>
			<select name="log-type">
				<option value="1">1
				<option value="2">2
				<option value="3">3
				<option value="4">4
				<option value="0" selected>All
			</select>
		</p>
		<p><span>Только важные:</span> <input name="log-important" type="checkbox"></input></p>
		<p><input type="submit" class="log-post-button" value="Отправить"></p>
	</form>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Category</th>
			<th>Name</th>
			<th>Text</th>
			<th>Date</th>
			<th>Important</th>
			<th>Location</th>
		</tr>
		<? getLogsToTable($logParams); ?>
	</table> 
	<script>
		document.addEventListener('DOMContentLoaded', getLogsTypes);
	</script>
</body>
</html>