<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $db;
	
	if(!$_SESSION['admin']) {
		echo "Вы не админ.";
		break;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($db->getLink()) {
			if(isset($_POST['comment-id'])) {
				$id = (int)$_POST['comment-id'];
				$query = "DELETE FROM comments WHERE comments_id = ?";
				$result = $db->executeQuery($query, array($id), 'delete_comment');

				if($result === false) {
					echo 'Комментарий не был удален';
					$log_type = 1;
					$log_name = 'failed to delete a comment';
					$log_text = 'user '.$_SESSION['user'].' has failed to delete comment id: '.$id;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = true;
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
				}
				else {
					echo 'Комментарий был удален';
					$log_type = 1;
					$log_name = 'deleted a comment';
					$log_text = 'user '.$_SESSION['user'].' has deleted comment id: '.$id;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = $_SESSION['admin'];
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
				}
			}
			else echo "Нет данных для удаления.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было удалено...";
?>