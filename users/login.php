<?
	session_start();
	header("HTTP/1.0 401 Unauthorized");
	require_once "../admin/admin_security/secure.inc.php";
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$user = trim(strip_tags($_POST['user']));
		$pw = trim(strip_tags($_POST['pw']));
		
		if(!checkUser($user, $pw)) {
			echo 'No luck';
		}
		else {
			$row = checkUser($user, $pw);
			
			if($row['user_group'] == 'admins') {
				$_SESSION['admin'] = true;
				$_SESSION['user'] = $row['user_login'];
				header("Location: ../admin/index.php");
			}
			else {
				$_SESSION['admin'] = false;
				$_SESSION['user'] = $row['user_login'];
				header("Location: user_profile.php");
			}
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
	<form action="<?= $_SERVER['REQUEST_URI']?>" method="post">
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