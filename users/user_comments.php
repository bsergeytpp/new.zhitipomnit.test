<?
	require_once ("../sessions/session.inc.php");
	require_once "../functions/functions.php";
	if(session_status() !== PHP_SESSION_ACTIVE) session_start();
	
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if($db->getLink()) {
			if(isset($_GET['login']) && isset($_GET['comments-location-id'])) {
				$login = $_GET['login'];

				if($login !== $_SESSION['user']) {
					echo "Ошибка проверки подлинности!";
					return;
				}
				
				$location_id = $_GET['comments-location-id'];

				$query = "SELECT COUNT(comments_id) FROM comments, users " .
						 "WHERE comments_location_id = ? " .
						 "AND comments_author = user_id " . 
						 "AND user_id IN (SELECT user_id FROM users WHERE user_login = ?)";
				$result = $db->executeQuery($query, array($location_id, $login), 'get_comments_id');
				
				if($result === false) {
					echo 'Ошибка в запросе';
				}
				else if($result->fetchColumn() === 0 ) {
					echo 'Комментарии не найдены для ID '.$location_id;
				}
				else {
					//echo 'Найдено комментариев: ' . pg_num_rows($result);
					$commentsIds = array();
					
					$query = "SELECT comments_id FROM comments, users " .
						 "WHERE comments_location_id = ? " .
						 "AND comments_author = user_id " . 
						 "AND user_id IN (SELECT user_id FROM users WHERE user_login = ?)";
					$result = $db->executeQuery($query, array($location_id, $login), 'get_comments_id');
					
					while($row = $result->fetch(PDO::FETCH_ASSOC)) {
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