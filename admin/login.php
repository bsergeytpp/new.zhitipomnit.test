<?
	session_start();
	header("HTTP/1.0 401 Unauthorized");
	require_once "secure.inc.php";
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$user = trim(strip_tags($_POST['user']));
		$pw = trim(strip_tags($_POST['pw']));
		$ref = trim(strip_tags($_GET['ref']));
		
		if(!$ref) $ref = '/admin/';
		
		if(checkUser($user, $pw)) {
			$_SESSION['admin'] = true;
			header("Location: $ref");
			exit;
		}
		else echo 'No luck';
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