<?
	session_start();
	require_once "../functions/functions.php";
	global $db;

	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			$captcha = verifyCaptcha($_POST['cf-turnstile-response']);
			$currentTime = date('Y-m-d H:i:sO');
			
			// данные для логирования
			global $logData;
			$logData['type'] = 4;
			$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$logData['important'] = $_SESSION['admin'] || false;
			$logData['date'] = $currentTime;
			$logData['ip'] = getUserIp();
			
			if(!json_decode($captcha)) {
				echo "Ошибка Cloudflare Turnstile: неверные или пустые данные!";
				return;
			}
			else {
				$jsonResult = json_decode($captcha);
				if($jsonResult->success !== true) {
					echo "Ошибка Cloudflare Turnstile: неверно введены данные!";
					$logData['name'] = 'user-registration';
					$logData['text'] = 'Wrong Cloudflare Turnstile code: ' . $captcha;
					echo addLogs($logData);
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
				$logData['name'] = 'user-registration';
				$logData['text'] = 'A new user has registred: '.$login;
				echo addLogs($logData);
			}				
		}
		else {
			echo "Тут мог быть ваш пользователь.";
		}
	}
	else echo "Ничего не было передано...";
?>