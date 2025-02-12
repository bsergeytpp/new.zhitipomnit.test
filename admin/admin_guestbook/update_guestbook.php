<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../../sessions/session.inc.php");
	
	global $db;
	
	if(!$_SESSION['admin']) {
		echo "<div class='error-message'>Вы не админ</div>";
		exit;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			if(isset($_POST['gb-text']) && isset($_POST['gb-id'])) {
				$gbText = clearStr($_POST['gb-text']);
				$gbId = clearStr($_POST['gb-id']);
				$query = "UPDATE guestbook SET gb_text = ? WHERE gb_id = ?";
				$result = $db->executeQuery($query, array("$gbText", "$gbId"), 'update_gb_message');
				
				// данные для логирования
				global $logData;
				$logData['type'] = 6;
				$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$logData['date'] = date('Y-m-d H:i:sO');
				$logData['important'] = $_SESSION['admin'] || false;
				$logData['ip'] = getUserIp();
				
				if($result === false) {
					echo "<div class='error-message'>Сообщение не было обновлено</div>";
					$logData['name'] = 'failed to update guestbook message';
					$logData['text'] = 'user '.$_SESSION['user'].' has failed to update guestbook message with id: '.$gbId;
				}
				else {
					echo "<div class='success-message'>Сообщение было обновлено</div>";
					$logData['name'] = 'guestbook message has been updated';
					$logData['text'] = 'user '.$_SESSION['user'].' has updated guestbook message with id: '.$gbId;	
				}
				
				echo addLogs($logData);				
			}
			else {
				echo "<div class='error-message'>Нет данных для обновления.</div>";
			}
		}
		else {
			echo "<div class='error-message'>Нет соединения с БД.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было обновлено...";
?>