<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($link) {
			if(isset($_POST['text']) && isset($_POST['id'])) {
				$text = clearStr($_POST['comments-text']);
				$id = (int)$_POST['comments-id'];
				$query = "UPDATE comments " .
						 "SET comments_text = '" . pg_escape_string($text) . "' " . 
						 "WHERE comments_id = " . $id;
				$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Комментарий не был обновлен';
				else echo 'Комментарий был обновлен';			
			}
			echo "Нет данных для обновления.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было обновлено...";
?>