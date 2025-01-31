<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if($db->getLink()) {
			if(isset($_GET['user_id'])) {
				$id = (int)$_GET['user_id'];
				$query = "UPDATE users SET user_deleted = ? WHERE user_id = ?";
				$result = $db->executeQuery($query, array(true, $id), 'delete_user');
				
				if($result === false) echo "<div class='error-message'>Пользователь не был удален</div>";
				else {
					echo "<div class='success-message'>Пользователь был удален</div>";
					
					// данные для логирования
					global $logData;
					$logData['type'] = 4;
					$logData['name'] = 'user-delete';
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