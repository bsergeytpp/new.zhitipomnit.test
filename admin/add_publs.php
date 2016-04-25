<?
	require_once "session.inc.php";
	require_once "secure.inc.php";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Форма добавления публикаций</title>
    </head>
    <body>
        <div id="background-div"></div>
		<h2>Форма добавления публикаций:</h2>
        <form action="save_publs.php" method="post">
			<p>Заголовок: <input type="text" name="publs-header" size="20" required></p>
			<p>Текст: <textarea name="publs-text" size="50" required></textarea></p>
			<p><input type="submit" value="Добавить"></p>
		</form>
    </body>
</html>