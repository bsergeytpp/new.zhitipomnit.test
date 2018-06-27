<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	if(!$_SESSION['admin']) {
		echo "<div class='error-message'>Вы не админ</div>";
		exit;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			$gbId = $_POST['gb-id'];
			$query = "DELETE FROM guestbook WHERE gb_id = ?";
		    $result = $db->executeQuery($query, array("$gbId"), 'delete_gb_message');
			
			// данные для логирования
			global $logData;
			$logData['type'] = 6;
			$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$logData['date'] = date('Y-m-d H:i:sO');
			$logData['important'] = $_SESSION['admin'] || false;
			$logData['ip'] = getUserIp();
			
			if($result === false) {
				echo "<div class='error-message'>Сообщение не былы удалена</div>";
				$logData['name'] = 'failed to delete guestbook message';
				$logData['text'] = 'user '.$_SESSION['user'].' has failed to delete guestbook message with id: '.$gbId;
			}
			else {
				echo "<div class='success-message'>Сообщение было удалено</div>";
				$logData['name'] = 'guestbook message has been deleted';
				$logData['text'] = 'user '.$_SESSION['user'].' has deleeted guestbook message with id: '.$gbId;
			}
			
			echo addLogs($logData);
		}
		else {
			echo "<div class='warning-message'>Соединения с базой данных не установлено.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было удалено...</div>";
?>