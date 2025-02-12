<?
	//require_once "admin_security/secure.inc.php";
	require_once "../functions/functions.php";
	require_once "../sessions/session.inc.php";
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
				$user_id = getUserId2($login);
				$parent_id = $_POST['comments-parent'];
				if($parent_id === '') {
					$parent_id = NULL;
				}
				$date = date('Y-m-d H:i:sO');
				$query = "INSERT INTO comments (comments_author, comments_location_id, comments_text, comments_date, comments_parent_id) " .
						 "VALUES (?, ?, ?, ?, ?)";
				$result = $db->executeQuery($query, array($user_id, $id, $text, $date, $parent_id), 'insert_comment');
				
				// данные для логирования
				global $logData;
				$logData['type'] = 1;
				$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$logData['date'] = date('Y-m-d H:i:sO');
				$logData['important'] = $_SESSION['admin'] || false;
				$logData['ip'] = getUserIp();
				
				if($result === false) {
					echo "<div class='error-message'>Комментарий не был добавлен</div>";
					$logData['name'] = 'failed to add a comment';
					$logData['text'] = 'user '.$_SESSION['user'].' has failed to add a comment with text: '.$text;
				}
				else {
					echo "<div class='success-message'>Комментарий был добавлен</div>";
					$logData['name'] = 'added a comment';
					$logData['text'] = 'user '.$_SESSION['user'].' has added a comment: '.$text;
				}

				echo addLogs($logData);
			}
			else echo "<div class='error-message'>Нет данных для обновления.</div>";
		}
		else {
			echo "<div class='error-message'>Нет соединения с БД.</div>";
		}
	}
?>