<?
	//require_once "admin_security/session.inc.php";
	//require_once "admin_security/secure.inc.php";
	require_once "functions/admin_functions.php";
	global $link;
	$link = connectToPostgres();
	$userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($link) {
			if(isset($_POST['comments-text']) && isset($_POST['comments-login'])) {
				$text = clearStr($_POST['comments-text']);
				$login = clearStr($_POST['comments-login']);
				$location = clearStr($_SERVER['HTTP_REFERER']);
				$user_id = getUserId($login);
				$date = date('Y-m-d H:i:sO');
				$query = "INSERT INTO comments (comments_author, comments_location, comments_text, comments_date) " .
						 "VALUES ('$user_id', '$location', '$text', '$date')";
				$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Комментарий не был добавлен';
				else echo 'Комментарий был добавлен';			
			}
			else echo "Нет данных для обновления.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
?>