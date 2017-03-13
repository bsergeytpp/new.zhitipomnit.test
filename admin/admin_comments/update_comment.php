<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1; $author_id = $_SESSION['user_id'];
		if($db->getLink()) {
			if(isset($_POST['comment-text']) && isset($_POST['comment-id'])) {
				$dataLogin = (isset($_POST['comment-author'])) ? $_POST['comment-author'] : null;
				
				if($dataLogin !== $_SESSION['user'] || !$_SESSION['admin']) {
					echo 'Ошибка проверки подлинности';
					break;
				}
				
				$text = clearStr($_POST['comment-text']);
				$id = (int)$_POST['comment-id'];
				$query = "UPDATE comments SET comments_text = ? WHERE comments_id = ?";
				$result = $db->executeQuery($query, array($text, $id), 'update_comments');
				
				if($result === false) {
					echo 'Комментарий не был обновлен';
					$log_type = 1;
					$log_name = 'failed to update a comment';
					$log_text = 'user '.$_SESSION['user'].' has failed to update comment id: '.$id.' with new text: '.$text;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = true;
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
				}
				else {
					echo 'Комментарий был обновлен';
					$log_type = 1;
					$log_name = 'updated a comment';
					$log_text = 'user '.$_SESSION['user'].' has updated comment id: '.$id.' with new text: '.$text;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = $_SESSION['admin'];
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
				}					
			}
			else echo "Нет данных для обновления.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было обновлено...";
?>