<?
	session_start();
	require_once "../functions/functions.php";
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if($link) {
			if(isset($_GET['login']) && isset($_GET['comments-location-id'])) {
				$login = $_GET['login'];
				$location_id = $_GET['comments-location-id'];

				$query = "SELECT comments_id FROM comments, users " .
						 "WHERE comments_location_id = '" . $location_id . "' " .
						 "AND comments_author = user_id " . 
						 "AND user_id IN (SELECT user_id FROM users WHERE user_login = '" . $login . "')";
				$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Ошибка в запросе';
				else if(pg_num_rows($result) === 0 ) echo 'Комментарии не найдены для ID '.$location_id;
				else {
					//echo 'Найдено комментариев: ' . pg_num_rows($result);
					$commentsIds = array();
					
					while($row = pg_fetch_assoc($result)) {
						$commentsIds[] = $row;
					}
					
					echo json_encode($commentsIds);
				}
			}
			else echo "Ничего не было передано."; 			
		}
		else {
			echo "Соединение не установлено.";
		}
	}
	else echo "Запрос не удался.";
?>