<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		$id = -1;
		if($db->getLink()) {
			if(isset($_GET['id'])) {
				$id = (int)$_GET['id'];
				$query = "SELECT * FROM news WHERE news_id = $1";
				$result = $db->executeQuery($query, array("$id"), 'get_news_query');
				
				if($result === false) echo 'Новость не найдена';
				else {
					$row = pg_fetch_assoc($result);
					echo json_encode($row);	
				}		
			}
			else echo "Нет данных для поиска новости.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было найдено...";
?>