<?
	session_start();
	require_once "../functions/functions.php";
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if($link) {
			if(isset($_GET['login']) && isset($_GET['location'])) {
				$login = $_GET['login'];
				$location = $_GET['location'];

				$query = "SELECT comments_id, comments_author, user_id, user_login " .
				         "FROM comments, users WHERE comments_author = '".$login."' AND comments_location = '".$location."'";
				$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Комментариев не найдено';
				else echo 'Комментарии найдены';		
			}
			else echo "Ничего не было передано."; 			
		}
		else {
			echo "Соединение не установлено.";
		}
	}
	else echo "Запрос не удался.";
?>