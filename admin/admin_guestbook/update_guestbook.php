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
			if(isset($_POST['gb-text']) && isset($_POST['gb-id'])) {
				$gbText = clearStr($_POST['gb-text']);
				$gbId = clearStr($_POST['gb-id']);
				$query = "UPDATE guestbook SET gb_text = ? WHERE gb_id = ?";
				$result = $db->executeQuery($query, array("$gbText", "$gbId"), 'update_gb_message');
				
				if($result === false) {
					echo "<div class='error-message'>Сообщение не было обновлено</div>";
					$log_type = 6;
					$log_name = 'failed to update guestbook message';
					$log_text = 'user '.$_SESSION['user'].' has failed to update guestbook message with id: '.$gbId;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = true;
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
				}
				else {
					echo "<div class='success-message'>Сообщение было обновлено</div>";
					$log_type = 6;
					$log_name = 'guestbook message has been updated';
					$log_text = 'user '.$_SESSION['user'].' has updated guestbook message with id: '.$gbId;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = $_SESSION['admin'];
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
				}					
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