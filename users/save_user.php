<?
	session_start();
	require_once "../functions/functions.php";
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($link) {
			$login = filter_input(INPUT_POST, 'user-login', FILTER_SANITIZE_STRING);
			$password = password_hash($_POST['user-password'], PASSWORD_DEFAULT);
			$email = filter_input(INPUT_POST, 'user-email', FILTER_SANITIZE_EMAIL);
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				echo "Email не прошел валидацию. Отмена.";
				return;
			}
			$group = "users";//$_POST['user-group'];
			$query = "INSERT INTO users (user_login, user_password, user_email, user_group)
					  VALUES ($1, $2, $3, $4)";
			$result = executeQuery($query, array($login, $password, $email, $group), 'reg_user');
			
			if($result === false) echo 'Пользователь не был добавлен';
			else {
				echo 'Пользователь был добавлен';
				$log_name = 'user-registration';
				$log_text = 'A new user has registred: '.$login;
				$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$log_date = date('Y-m-d H:i:sO');;
				$log_important = $_SESSION['admin'];
				echo addLogs($log_name, $log_text, $log_location, $log_date, $log_important);
			}				
		}
		else {
			echo "Тут мог быть ваш пользователь.";
		}
	}
	else echo "Ничего не было передано...";
?>