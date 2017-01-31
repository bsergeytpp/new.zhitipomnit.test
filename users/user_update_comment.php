<?
	//require_once (__DIR__."/../admin_security/session.inc.php");
	//require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/functions.php");
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($db->getLink()) {
			if(isset($_POST['comment-text']) && isset($_POST['comment-id'])) {
				$text = clearStr($_POST['comment-text']);
				$id = (int)$_POST['comment-id'];
				$query = "UPDATE comments SET comments_text = ? WHERE comments_id = ?";
				$result = $db->executeQuery($query, array($text, $id), 'update_user_comment');
				
				if($result === false) echo 'Комментарий не был обновлен';
				else {
					echo 'Комментарий был обновлен';
					$log_name = 'comment-update';
					$log_text = 'user '.$_SESSION['user'].' has updated a comment: '.$id;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');;
					$log_important = $_SESSION['admin'];
					echo addLogs($log_name, $log_text, $log_location, $log_date, $log_important);
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