<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1; $author_id = $_SESSION['user_id'];
		if($db->getLink()) {
			if(isset($_POST['comment-text']) && isset($_POST['comment-id'])) {
				$dataLogin = (isset($_POST['comment-author'])) ? $_POST['comment-author'] : null;
				$editedDate = date('Y-m-d H:i:sO');
				
				if($dataLogin !== $_SESSION['user'] || $_SESSION['admin'] === false) {
					echo "<div class='error-message'>Ошибка проверки подлинности.</div>";
					exit;
				}
				
				$text = clearStr($_POST['comment-text']);
				$id = (int)$_POST['comment-id'];
				$query = "UPDATE comments SET comments_text = ?, comments_edited_by = ?, comments_edited_date = ? WHERE comments_id = ?";
				$result = $db->executeQuery($query, array($text, $author_id, $editedDate, $id), 'update_comments');
				
				// данные для логирования
				global $logData;
				$logData['type'] = 1;
				$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$logData['date'] = date('Y-m-d H:i:sO');
				$logData['important'] = $_SESSION['admin'] || false;
				$logData['ip'] = getUserIp();
				
				if($result === false) {
					echo "<div class='error-message'>Комментарий не был обновлен</div>";
					$logData['name'] = 'failed to update a comment';
					$logData['text'] = 'user '.$_SESSION['user'].' has failed to update comment id: '.$id.' with new text: '.$text;
				}
				else {
					echo "<div class='success-message'>Комментарий был обновлен</div>";
					$logData['name'] = 'updated a comment';
					$logData['text'] = 'user '.$_SESSION['user'].' has updated comment id: '.$id.' with new text: '.$text;
				}

				addLogs($logData);
			}
			else echo "<div class='error-message'>Нет данных для обновления.</div>";
		}
		else {
			echo "<div class='error-message'>Нет соединения с БД.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было обновлено...</div>";
?>