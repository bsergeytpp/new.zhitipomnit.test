<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
?>
<!DOCTYPE html>
<html>
<head>
	<title>Настройки сайта</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../../scripts/tinymce/tinymce.min.js"></script>
	<script src="../scripts/admin_script.js"></script>
</head>
<body>
	<h1>Настройки сайта</h1>
	<a href="../">Назад</a>
	<h3>Доступные действия:</h3>
	<table border='1' class="settings-table">
		<tr>
			<th>NEWS_MAXCOUNT</th>
			<th>OLDNEWS_MAXCOUNT</th>
			<th>PUBLS_MAXCOUNT</th>
			<th>PRESS_MAXCOUNT</th>
		</tr>
		<tr>
			<td><input name="NEWS" type="number" min="3" max="10" value="<? echo NEWS_MAXCOUNT; ?>"></td>
			<td><input name="OLDNEWS" type="number" min="3" max="10" value="<? echo OLDNEWS_MAXCOUNT; ?>"></td>
			<td><input name="PUBLS" type="number" min="3" max="10" value="<? echo PUBLS_MAXCOUNT; ?>"></td>
			<td><input name="PRESS" type="number" min="3" max="10" value="<? echo PRESS_MAXCOUNT; ?>"></td>
		</tr>
	</table> 
	<a href="#">Сохранить</a>
	<script>
		document.body.addEventListener('click', function(e) {
			var target = e.target;
		
			if(target.innerHTML === 'Сохранить') {
				e.preventDefault();
				saveSettings();
			} 
		}, false);
	</script>
</body>
</html>