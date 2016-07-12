<?
	session_start();
	require_once "../functions/functions.php";
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($link) {
			$login = $_POST['user-login'];
			$password = $_POST['user-password'];
			$email = $_POST['user-email'];
			$group = $_POST['user-group'];
			$query = "INSERT INTO users (user_login, user_password, user_email, user_group)
					  VALUES ('$login', '$password', '$email', '$group')";
			$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
			
			if($result === false) echo 'Пользователь не был добавлен';
			else echo 'Пользователь был добавлен';			
		}
		else {
			echo "Тут мог быть ваш пользователь.";
		}
	}
	else echo "Ничего не было передано...";
?>