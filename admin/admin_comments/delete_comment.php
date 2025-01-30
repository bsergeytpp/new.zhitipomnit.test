<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	if(!$_SESSION['admin']) {
		echo "<div class='error-message'>Вы не админ.</div>";
		exit;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($db->getLink()) {
			if(isset($_POST['comment-id'])) {
				$id = (int)$_POST['comment-id'];
				$query = "UPDATE comments SET comments_deleted = ? WHERE comments_id = ?";
				$result = $db->executeQuery($query, array(true, $id), 'delete_comment');

				// данные для логирования
				global $logData;
				$logData['type'] = 1;
				$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$logData['date'] = date('Y-m-d H:i:sO');
				$logData['important'] = $_SESSION['admin'] || false;
				$logData['ip'] = getUserIp();
				
				if($result === false) {
					echo "<div class='error-message'>Комментарий не был удален</div>";
					$logData['name'] = 'failed to delete a comment';
					$logData['text'] = 'user '.$_SESSION['user'].' has failed to delete comment id: '.$id;
				}
				else {
					echo "<div class='success-message'>Комментарий был удален</div>";
					$logData['name'] = 'deleted a comment';
					$logData['text'] = 'user '.$_SESSION['user'].' has deleted comment id: '.$id;
				}
				
				echo addLogs($logData);
			}
			else echo "<div class='error-message'>Нет данных для удаления.</div>";
		}
		else {
			echo "<div class='error-message'>Нет соединения с БД.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было удалено...</div>";
?>