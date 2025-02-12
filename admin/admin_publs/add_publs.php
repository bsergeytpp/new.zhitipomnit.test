<?
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../../sessions/session.inc.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Форма добавления публикаций</title>
		<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
		<script src="../../scripts/tinymce/tinymce.min.js"></script>
		<script type="text/javascript">
		  tinymce.init({
			selector: '#publ-textarea',
			height: 300,
			width: 1000,
			language: 'ru',
			plugins: 'code',
			paste_data_images: true
		  });
		</script>
    </head>
    <body>
        <a href="/admin">Назад к админке</a><br>
		<h2>Форма добавления публикаций:</h2>
        <form action="save_publs.php" method="post">
			<p>Заголовок: <input type="text" name="publs-header" size="20" required></p>
			<p>Текст: <textarea id="publ-textarea" name="publs-text" size="50"></textarea></p>
			<p><input type="submit" value="Добавить"></p>
		</form>
    </body>
</html>