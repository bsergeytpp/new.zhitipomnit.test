<?
	session_start();
	require_once "../functions/functions.php";
	global $db;

	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			$recaptcha = verifyCaptcha($_POST['g-recaptcha-response']);
			$currentTime = date('Y-m-d H:i:sO');
			$log_type = 4;
			$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			if(!json_decode($recaptcha)) {
				echo "Ошибка reCaptcha: не верные или пустые данные!";
				return;
			}
			else {
				$jsonResult = json_decode($recaptcha);
				if($jsonResult->success !== true) {
					echo "Ошибка reCaptcha: не верно введены данные!";
					$log_name = 'user-registration';
					$log_text = 'Wrong reCaptcha code: ' . $recaptcha;
					$log_date = $currentTime;
					$log_important = $_SESSION['admin'] ? $_SESSION['admin'] : false;
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
					return;
				} 
			}
			$login = filter_input(INPUT_POST, 'user-login', FILTER_SANITIZE_STRING);
			$password = password_hash($_POST['user-password'], PASSWORD_DEFAULT);
			$email = filter_input(INPUT_POST, 'user-email', FILTER_SANITIZE_EMAIL);
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
				echo "Email не прошел валидацию. Отмена.";
				return;
			}
			$group = "users";
			$query = "INSERT INTO users (user_login, user_password, user_email, user_group, user_reg_date, user_last_seen)
					  VALUES (?, ?, ?, ?, ?, ?)";
			$result = $db->executeQuery($query, array($login, $password, $email, $group, $currentTime, $currentTime), 'reg_user');
			
			if($result === false) echo 'Пользователь не был добавлен';
			else {
				echo 'Пользователь был добавлен<br>';
				echo '<a href="login.php">Войти</a>';
				$log_name = 'user-registration';
				$log_text = 'A new user has registred: '.$login;
				$log_date = $currentTime;
				$log_important = $_SESSION['admin'] ? $_SESSION['admin'] : false;
				echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
			}				
		}
		else {
			echo "Тут мог быть ваш пользователь.";
		}
	}
	else echo "Ничего не было передано...";
?>