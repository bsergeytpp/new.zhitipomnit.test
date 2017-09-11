<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		$id = -1;
		if($db->getLink()) {
			if(isset($_GET['id'])) {
				$id = (int)$_GET['id'];
				$query = "SELECT * FROM news WHERE news_id = ?";
				$result = $db->executeQuery($query, array("$id"), 'get_news_query');
				
				if($result === false) echo "<div class='error-message'>Новость не найдена</div>";
				else {
					$row = $result->fetch(PDO::FETCH_ASSOC);
					echo json_encode($row);	
				}		
			}
			else echo "<div class='error-message'>Нет данных для поиска новости.</div>";
		}
		else {
			echo "<div class='error-message'>Нет соединения с БД.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было найдено...</div>";
?>