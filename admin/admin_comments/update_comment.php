<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1; $author_id = $_SESSION['user_id'];
		if($link) {
			if(isset($_POST['comment-text']) && isset($_POST['comment-id'])) {
				$dataLogin = (isset($_POST['comment-author'])) ? $_POST['comment-author'] : null;
				
				if($dataLogin !== $_SESSION['user']) {
					echo 'Ошибка проверки подлинности';
					break;
				}
				
				$text = clearStr($_POST['comment-text']);
				$id = (int)$_POST['comment-id'];
				$query = "UPDATE comments SET comments_text = $1 WHERE comments_id = $2";
				$result = pg_prepare($link, "update_comments", $query);
				$result = pg_execute($link, "update_comments", array($text, $id)) 
						  or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Комментарий не был обновлен';
				else echo 'Комментарий был обновлен';			
			}
			else echo "Нет данных для обновления.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было обновлено...";
?>