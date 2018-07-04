<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			if(isset($_POST['user-login']) && 
			   isset($_POST['user-email']) && 
			   isset($_POST['user-group']) && 
			   isset($_POST['user-reg']) && 
			   isset($_POST['user-last']) && 
			   isset($_POST['user-id'])) {
				$login = strip_tags(clearStr($_POST['user-login']));
				$email = strip_tags(clearStr($_POST['user-email']));
				$group = strip_tags(clearStr($_POST['user-group']));
				$regDate = $_POST['user-reg'];
				$lastSeen = $_POST['user-last'];
				$id = (int)$_POST['user-id'];
				$query = "UPDATE users SET user_login = COALESCE(?, user_login),
										  user_email = COALESCE(?, user_email),
										  user_group = COALESCE(?, user_email),
										  user_reg_date = COALESCE(?, user_reg_date),
										  user_last_seen = COALESCE(?, user_last_seen) WHERE user_id = ?";
				$result = $db->executeQuery($query, array($login, $email, $group, $regDate, $lastSeen, $id), 'update_user');
				
				if($result === false) echo "<div class='error-message'>Данные пользователя не были обновлены</div>";
				else {
					echo "<div class='success-message'>Данные пользователя были обновлены</div>";
					
					// данные для логирования
					global $logData;
					$logData['type'] = 4;
					$logData['name'] = 'user-update';
					$logData['text'] = 'user '.$_SESSION['user'].' has updated user: '.$login;
					$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$logData['date'] = date('Y-m-d H:i:sO');
					$logData['important'] = $_SESSION['admin'] || false;
					$logData['ip'] = getUserIp();
					echo addLogs($logData);
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