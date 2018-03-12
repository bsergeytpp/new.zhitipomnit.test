<?
	require "functions/admin_functions.php";
	require_once "admin_security/secure.inc.php";
	require_once "admin_security/session.inc.php";

	if(isset($_GET['logout'])) {
		logOut();
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Админка</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<link type="text/css" rel="StyleSheet" href="styles/admin_styles.css" />
</head>
<body>
	<h1>Администрирование сайта</h1>
	<h2>Доступные действия:</h2>
	<ul class="admin-nav">
		<li>
			<h3>Новости</h3>
			<ul>
				<li><a href='admin_news/add_news.php'>Добавление новостей</a></li>
				<li><a href='admin_news/manage_news.php'>Управление новостями</a></li>
			</ul>
		</li>
		<li>
			<h3>Публикации</h3>
			<ul>
				<li><a href='admin_publs/add_publs.php'>Добавление публикаций</a></li>
				<li><a href='admin_publs/manage_publs.php'>Управление публикациями</a></li>
			</ul>
		</li>
		<li>
			<h3>Пользователи</h3>
			<ul>
				<li><a href='admin_users/manage_users.php'>Управление пользователями</a></li>
				<li><a href='admin_users/users_sessions.php'>Сессии пользователей</a></li>
			</ul>
		</li>
		<li>
			<h3>Книга Памяти</h3>
			<ul>
				<li><a href='admin_search/manage_persons.php'>Управление именами</a></li>
			</ul>
		</li>
		<li>
			<h3>Прочее</h3>
			<ul>
				<li><a href='admin_settings/settings.php'>Настройки</a></li>
				<li><a href='admin_security/logs.php'>Логи</a></li>
				<li><a href='index.php?logout'>Завершить сеанс</a></li>
			</ul>
		</li>
	</ul>
	<a href="/">Перейти на сайт</a>
	<div>Всего онлайн: </div>
	<strong>
	<? 
		global $sessionHandler;
		$sessHandler = DBSessionHandler::getInstance();
		$sessHandler->getActiveSessions(); 
	?></strong>
</body>
</html>