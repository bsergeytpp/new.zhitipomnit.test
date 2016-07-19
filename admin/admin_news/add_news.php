<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Форма добавления новости</title>
		<script src="/scripts/tinymce/tinymce.min.js"></script>
		<script type="text/javascript">
		  tinymce.init({
			selector: '#news-textarea',
			height: 300,
			width: 1000,
			language: 'ru_RU',
			plugins: 'code',
			paste_data_images: true
		  });
		</script>
    </head>
    <body>
        <div id="background-div"></div>
		<h2>Форма добавления новости:</h2>
        <form action="save_news.php" method="post">
			<p>Дата: <input type="date" name="news-date" size="10" required></p>
			<p>Заголовок: <input type="text" name="news-header" size="20" required></p>
			<p>Текст: <textarea id="news-textarea" name="news-text" size="50"></textarea></p>
			<!--<p>Есть логотип: <input name="hasImage" type="checkbox" required>Да/Нет</input></p>-->
			<p><input type="submit" value="Добавить"></p>
		</form>
    </body>
</html>