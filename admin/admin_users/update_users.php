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
				
				if($result === false) echo "<div class='error-message'>Данные пользователя не были обновлены</div>";
				else {
					echo "<div class='success-message'>Данные пользователя были обновлены</div>";
					
					// данные для логирования
					global $logData;
					$logData['type'] = 4;
					$logData['name'] = 'user-update';
					$logData['text'] = 'user '.$_SESSION['user'].' has updated user: '.$name;
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