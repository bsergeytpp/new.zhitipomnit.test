<?
	require_once "../admin/admin_security/secure.inc.php";
	require_once "../admin/admin_security/session.inc.php";
		
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if(isset($_POST['user'])) {
			$user = trim(strip_tags($_POST['user']));
			$user = filter_var($user, FILTER_SANITIZE_STRING);
		}
		else {
			$user = 'empty';
		}
		
		if(isset($_POST['pw'])) {
			$pw = trim(strip_tags($_POST['pw']));
		}
		else {
			$pw = null;
		}
		
		// данные для логирования
		global $logData;
		$logData['type'] = 4;
		$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
		$logData['date'] = date('Y-m-d H:i:sO');
		$logData['important'] = $_SESSION['admin'] || false;
		$logData['ip'] = getUserIp();
		
		if(!checkUser($user, $pw)) {
			echo 'No luck';
			jsLogNotify('No luck. <a href="../users/login.php">Войдите</a> или <a href="../users/reg_users.php">зарегистрируйстесь</a>.', 'warning');
			$logData['name'] = 'failed login';
			$logData['text'] = 'somebody failed to log in with username: ' . $user;
			addLogs($logData);
			header("HTTP/1.0 401 Unauthorized");
		}
		else {
			$row = checkUser($user, $pw);
			$salt = rand(1, 1000);
			$_SESSION['secret'] = rand(1, 1000);
			$_SESSION['token'] = $salt . ':' . md5($salt . ':' . $_SESSION['secret']);
			
			setcookie("sec-token", $_SESSION['token'], 0, '/', 'new.zhitipomnit.test');
			updateUserLastSeen($user);
			
			if($row['user_group'] == 'admins') {
				$_SESSION['admin'] = true;
				$_SESSION['user'] = $row['user_login'];
				$_SESSION['user_id'] = $row['user_id'];
				header("Location: ../admin/index.php");
			}
			else {
				$_SESSION['admin'] = false;
				$_SESSION['user'] = $row['user_login'];
				header("Location: user_profile.php");
			}
			$test = $_SESSION['user'];
			error_log("LOGIN_TEST: $test", 0);
			if(isset($sessionHandler)) {
				$sessionHandler->setUser($_SESSION['user']);
			}
			
			$logData['name'] = 'login';
			$logData['text'] = 'user '.$_SESSION['user'].' has logged in';
			addLogs($logData);
		}
		
		//exit;
	}
	else if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if(!isset($_SESSION['admin'])) {
			$_SESSION['admin'] = false;
		}
		
		if(!isset($_SESSION['user'])) {
			$_SESSION['user'] = null;
		}
		
		if(isset($_GET['logout'])) {
			logOut();
			exit;
		}
		
		if($_SESSION['admin'] === true) {
			header("Location: ../admin/index.php");
		}
		else if($_SESSION['user'] !== null) {
			echo "Вы уже вошли под логином " . $_SESSION['user'];
			echo "<br><a href='login.php?logout'>Выйти</a>";
			exit;
		}
	}
?>
<!DOCTYPE html>
<html>
<head>
	<title>Авторизация</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<link type="text/css" rel="StyleSheet" href="../styles/styles.css" />
</head>
<body>
	<h1>Авторизация</h1>
	<form action="login.php" method="post">
		<div>
			<label for="txtUser">Логин</label>
			<input id="txtUser" type="text" name="user" autocomplete="username" />
		</div>
		<div>
			<label for="txtString">Пароль</label>
			<input id="txtString" type="password" name="pw" autocomplete="current-password" />
		</div>
		<div>
			<button type="submit">Войти</button>
		</div>	
	</form>
	<a href="/">На главную</a>
	<a href="reg_users.php">Зарегистрироваться</a>
</body>
</html>