<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($db->getLink()) {
			if(isset($_POST['text']) && isset($_POST['id'])) {
				$text = strip_tags(clearStr($_POST['text']));
				$name = clearStr($_POST['name']);
				$id = (int)$_POST['id'];
				$query = "UPDATE users SET " . pg_escape_string($name) . " = ? WHERE user_id = ?";
				$result = $db->executeQuery($query, array($text, $id), 'update_user');
				
				if($result === false) echo "<div class='error-message'>Логин пользователя не был обновлен</div>";
				else {
					echo "<div class='success-message'>Логин пользователя был обновлен</div>";
					$log_name = 'user-update';
					$log_text = 'user '.$_SESSION['user'].' has updated user: '.$name;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');;
					$log_important = $_SESSION['admin'];
					echo addLogs($log_name, $log_text, $log_location, $log_date, $log_important);
				}
			}
			echo "<div class='error-message'>Нет данных для обновления.</div>";
		}
		else {
			echo "<div class='error-message'>Нет соединения с БД.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было обновлено...</div>";
?>