<?
	require "functions/admin_functions.php";
	require_once "admin_security/secure.inc.php";
	require_once "admin_security/session.inc.php";

	if(isset($_GET['logout'])) {
		logOut();
	}
	
	global $sessionHandler;
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
	<h3>Доступные действия:</h3>
	<ul>
		<li>
			Новости
			<ul>
				<li><a href='admin_news/add_news.php'>Добавление новостей</a></li>
				<li><a href='admin_news/manage_news.php'>Управление новостями</a></li>
			</ul>
		</li>
		<li>
			Публикации
			<ul>
				<li><a href='admin_publs/add_publs.php'>Добавление публикаций</a></li>
				<li><a href='admin_publs/manage_publs.php'>Управление публикациями</a></li>
			</ul>
		</li>
		<li>
			Пользователи
			<ul>
				<li><a href='admin_users/manage_users.php'>Управление пользователями</a></li>
				<li><a href='admin_users/users_sessions.php'>Сессии пользователей</a></li>
			</ul>
		</li>
		<li>
			Книга Памяти
			<ul>
				<li><a href='admin_search/manage_persons.php'>Управление именами</a></li>
			</ul>
		</li>
		<li>
			Прочее
			<ul>
				<li><a href='admin_settings/settings.php'>Настройки</a></li>
				<li><a href='admin_security/logs.php'>Логи</a></li>
				<li><a href='index.php?logout'>Завершить сеанс</a></li>
			</ul>
		</li>
	</ul>
	<a href="/">Перейти на сайт</a>
	<div>Пользователи онлайн: </div>
	<strong>
	<? 
		$sessHandler = DBSessionHandler::getInstance();
		$sessHandler->getActiveSessions(); 
	?></strong>
</body>
</html>