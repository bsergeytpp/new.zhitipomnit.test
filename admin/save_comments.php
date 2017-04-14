<?
	//require_once "admin_security/session.inc.php";
	//require_once "admin_security/secure.inc.php";
	require_once "functions/admin_functions.php";
	global $db;
	$userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			if(isset($_POST['comments-text']) && isset($_POST['comments-login'])) {
				if(!checkToken($_POST['token'])) {
					echo "<div class='error-message'>Проверка не пройдена.</div>";
					exit;
				}
				
				$text = clearStr($_POST['comments-text']);
				$login = clearStr($_POST['comments-login']);
				$text = filter_var($text, FILTER_SANITIZE_STRING);
				$login = filter_var($login, FILTER_SANITIZE_STRING);
				$id = $_POST['comments-location-id'];
				$user_id = getUserId($login);
				$parent_id = $_POST['comments-parent'];
				if($parent_id === '') {
					$parent_id = NULL;
				}
				$date = date('Y-m-d H:i:sO');
				$query = "INSERT INTO comments (comments_author, comments_location_id, comments_text, comments_date, comments_parent_id) " .
						 "VALUES (?, ?, ?, ?, ?)";
				$result = $db->executeQuery($query, array($user_id, $id, $text, $date, $parent_id), 'insert_comment');
				
				if($result === false) {
					echo "<div class='error-message'>Комментарий не был добавлен</div>";
					$log_type = 1;
					$log_name = 'failed to add a comment';
					$log_text = 'user '.$_SESSION['user'].' has failed to add a comment with text: '.$text;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = true;
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
				}
				else {
					echo "<div class='success-message'>Комментарий был добавлен</div>";
					$log_type = 1;
					$log_name = 'added a comment';
					$log_text = 'user '.$_SESSION['user'].' has added a comment: '.$text;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = $_SESSION['admin'];
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
				}					
			}
			else echo "<div class='error-message'>Нет данных для обновления.</div>";
		}
		else {
			echo "<div class='error-message'>Нет соединения с БД.</div>";
		}
	}
?>