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
						
			if($result === false) {
				echo "<div class='error-message'>Сообщение не былы удалена</div>";
				$log_type = 6;
				$log_name = 'failed to delete guestbook message';
				$log_text = 'user '.$_SESSION['user'].' has failed to delete guestbook message with id: '.$gbId;
				$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$log_date = date('Y-m-d H:i:sO');
				$log_important = true;
				echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
			}
			else {
				echo "<div class='success-message'>Сообщение было удалено</div>";
				$log_type = 6;
				$log_name = 'guestbook message has been deleted';
				$log_text = 'user '.$_SESSION['user'].' has deleeted guestbook message with id: '.$gbId;
				$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$log_date = date('Y-m-d H:i:sO');
				$log_important = $_SESSION['admin'];
				echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
			}
		}
		else {
			echo "<div class='warning-message'>Соединения с базой данных не установлено.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было удалено...</div>";
?>