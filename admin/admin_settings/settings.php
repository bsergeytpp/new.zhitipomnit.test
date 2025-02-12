<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../../sessions/session.inc.php");	
	
	$res = getSettings();
	
	if(!$res) {
		echo "<div class='error-message'>Настройки не найдены</div>";
	}
		
	$settingsArr = unserialize($res[0]);
	$siteSettingsArr = $settingsArr['site_settings'];
	$userSettingsArr = $settingsArr['user_settings'];
?>
<!DOCTYPE html>
<html>
<head>
	<title>Настройки сайта</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../../scripts/tinymce/tinymce.min.js"></script>
	<script src="../../scripts/jslib.js"></script>
	<script src="../scripts/admin_script.js"></script>
</head>
<body>
	<h1>Настройки сайта</h1>
	<a href="../">Назад</a>
	<h3>Доступные действия:</h3>
	<table border='1' class="site-settings-table">
		<tr>
			<th>NEWS_MAXCOUNT</th>
			<th>OLDNEWS_MAXCOUNT</th>
			<th>PUBLS_MAXCOUNT</th>
			<th>PRESS_MAXCOUNT</th>
			<th>LOGS_MAXCOUNT</th>
		</tr>
		<tr>
			<td><input name="NEWS" type="number" min="3" max="10" value="<? echo $siteSettingsArr['NEWS_MAXCOUNT']; ?>"></td>
			<td><input name="OLDNEWS" type="number" min="3" max="10" value="<? echo $siteSettingsArr['OLDNEWS_MAXCOUNT']; ?>"></td>
			<td><input name="PUBLS" type="number" min="3" max="10" value="<? echo $siteSettingsArr['PUBLS_MAXCOUNT']; ?>"></td>
			<td><input name="PRESS" type="number" min="3" max="10" value="<? echo $siteSettingsArr['PRESS_MAXCOUNT']; ?>"></td>
			<td><input name="LOGS" type="number" min="3" max="10" value="<? echo $siteSettingsArr['LOGS_MAXCOUNT']; ?>"></td>
		</tr>
	</table> 
	
	<table border='1' class="user-settings-table">
		<tr>
			<th>news_style</th>
		</tr>
		<tr>
			<td><input name="news_style" type="text" value="<? echo $userSettingsArr['news_style']; ?>"></td>
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