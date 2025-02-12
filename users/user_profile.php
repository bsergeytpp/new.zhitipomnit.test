<?
	require_once (__DIR__."/../functions/functions.php");
	require_once (__DIR__."/../sessions/session.inc.php");
	
	if(session_status() !== PHP_SESSION_ACTIVE) session_start();
?>
<!DOCTYPE html>
<html>
<head>
	<title>Профиль пользователя</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../scripts/jslib.js"></script>
	<script src="../scripts/tinymce/tinymce.min.js"></script>
	<!--script src="../admin/scripts/admin_script.js"></script-->
	<link type="text/css" rel="StyleSheet" href="../styles/styles.css" />
</head>
<body>
	<div id="wrapper" class="blocks">
		<? 
			global $userLogin; 
			
			// переход по ссылке из мини-профиля
			$userLogin = (isset($_GET['user_login'])) ? $_GET['user_login'] : null;
			
			// зашел другой пользователь или по прямому адресу
			if($userLogin == null) $userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
			
			// зашел гость
			if($userLogin == null) {
				header("Location: login.php");
			}
			
			$userData = getUserData($userLogin); 
			if(!$userData) {
				echo "<h2>Пользователь <strong>".$userLogin."</strong> не найден!</h2>";
				exit;
			}
			
			echo "<h1>Профиль пользователя $userLogin</h1>";
		?>
		<a href="/">Главная</a><br> 
		<h3>Данные:</h3>
		<p><strong>Логин: </strong><? echo $userData['user_login']; ?></p>
		<p><strong>Email: </strong><? echo $userData['user_email']; ?></p>
		<p><strong>Группа: </strong><? echo $userData['user_group']; ?></p>
		<p><strong>Дата регистрации: </strong><? echo $userData['user_reg_date']; ?></p>
		<p><strong>Последний вход: </strong><? echo $userData['user_last_seen']; ?></p>
		<a href="login.php?logout">Выйти</a>
	</div>
</body>
</html>