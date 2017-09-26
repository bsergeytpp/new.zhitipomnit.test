<?
	require_once "../admin/admin_security/secure.inc.php";
	require_once "../admin/admin_security/session.inc.php";
		
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$user = trim(strip_tags($_POST['user']));
		$user = filter_var($user, FILTER_SANITIZE_STRING);
		$pw = trim(strip_tags($_POST['pw']));
		
		if(!checkUser($user, $pw)) {
			echo 'No luck';
			$log_type = 4;
			$log_name = 'failed login';
			$log_text = 'somebody failed to log in with username: ' . $user;
			$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$log_date = date('Y-m-d H:i:sO');
			$log_important = true;
			echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
			header("HTTP/1.0 401 Unauthorized");
			exit;
		}
		else {
			$row = checkUser($user, $pw);
			$salt = rand(1, 1000);
			$_SESSION['secret'] = rand(1, 1000);
			$_SESSION['token'] = $salt . ':' . md5($salt . ':' . $_SESSION['secret']);
			
			setcookie("sec-token", $_SESSION['token'], 0, '/', 'new.zhitipomnit.test');
			
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
			
			if(isset($sessionHandler)) {
				$sessionHandler->setUser($_SESSION['user']);
			}
			//updateSessionDB();
			
			$log_type = 4;
			$log_name = 'login';
			$log_text = 'user '.$_SESSION['user'].' has logged in';
			$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$log_date = date('Y-m-d H:i:sO');
			$log_important = $_SESSION['admin'];
			echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
			
			exit;
		}
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
</head>
<body>
	<h1>Авторизация</h1>
	<form action="login.php" method="post">
		<div>
			<label for="txtUser">Логин</label>
			<input id="txtUser" type="text" name="user" />
		</div>
		<div>
			<label for="txtString">Пароль</label>
			<input id="txtString" type="password" name="pw" />
		</div>
		<div>
			<button type="submit">Войти</button>
		</div>	
	</form>
</body>
</html>