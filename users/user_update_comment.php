<?
	require_once (__DIR__."/../sessions/session.inc.php");
	//require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/functions.php");
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($db->getLink()) {
			if(isset($_POST['comment-text']) && isset($_POST['comment-id'])) {
				$text = clearStr($_POST['comment-text']);
				$id = (int)$_POST['comment-id'];
				$editedDate = date('Y-m-d H:i:sO');
				$userLogin = $_SESSION['user'];
				
				// проверка пользователя
				$checkQuery = 'SELECT 
								(SELECT user_id FROM users WHERE user_id = comments.comments_author),
								(SELECT user_login FROM users WHERE user_id = comments.comments_author)
							   FROM comments WHERE comments_id = ?';

				$checkResult = $db->executeQuery($checkQuery, array($id), 'check_user');
				$res = $checkResult->fetchAll();
				
				foreach($res as $row) {
					$userId = $row[0];
					$commentAuthor = $row[1];
				}
				
				if($commentAuthor !== $userLogin) {
					echo "Ошибка проверки подлинности!";
					return;
				}
				
				// обновление комментария
				$query = "UPDATE comments SET comments_text = ?, comments_edited_by = ?, comments_edited_date = ? WHERE comments_id = ?";
				$result = $db->executeQuery($query, array($text, $userId, $editedDate, $id), 'update_user_comment');
				
				if($result === false) echo 'Комментарий не был обновлен';
				else {
					echo 'Комментарий был обновлен';
					// данные для логирования
					global $logData;
					$logData['type'] = 1;
					$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$logData['date'] = date('Y-m-d H:i:sO');;
					$logData['important'] = $_SESSION['admin'] || false;
					$logData['ip'] = getUserIp();
					$logData['name'] = 'comment-update';
					$logData['text'] = 'user '.$userLogin.' has updated a comment: '.$id;
					echo addLogs($logData);
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