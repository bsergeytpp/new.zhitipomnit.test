<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $link;
	$link = connectToPostgres();
	
	if(!$_SESSION['admin']) {
		echo "Вы не админ.";
		break;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($link) {
			if(isset($_POST['comment-id'])) {
				$id = (int)$_POST['comment-id'];
				$query = "DELETE FROM comments " . 
						 "WHERE comments_id = " . $id;
				$result = pg_query($link, $query) or die('Query error: '. pg_last_error());

				if($result === false) echo 'Комментарий не был удален';
				else echo 'Комментарий был удален';			
			}
			else echo "Нет данных для удаления.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было удалено...";
?>