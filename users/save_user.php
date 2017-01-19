<?
	session_start();
	require_once "../functions/functions.php";
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($link) {
			$login = filter_input(INPUT_POST, $_POST['user-login'], FILTER_SANITIZE_STRING);
			$password = password_hash($_POST['user-password'], PASSWORD_DEFAULT);
			$email = filter_input(INPUT_POST, $_POST['user-email'], FILTER_SANITIZE_EMAIL);
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				echo "Email не прошел валидацию. Отмена.";
				return;
			}
			$group = "users";//$_POST['user-group'];
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