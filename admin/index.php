<?
require "../functions/functions.php";
require_once "session.inc.php";
require_once "secure.inc.php";
if(isset($_GET['logout'])) {
	logOut();
}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Админка</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
</head>
<body>
	<h1>Администрирование сайта</h1>
	<h3>Доступные действия:</h3>
	<ul>
		<li><a href='add_news.php'>Добавление новостей</a></li>
		<li><a href='add_publs.php'>Добавление публикаций</a></li>
		<li><a href='manage_news.php'>Управление новостями</a></li>
		<li><a href='manage_publs.php'>Управление публикациями</a></li>
		<li><a href='settings.php'>Настройки</a></li>
		<li><a href='index.php?logout'>Завершить сеанс</a></li>
	</ul>
	<a href="/">Перейти на сайт</a>
</body>
</html>