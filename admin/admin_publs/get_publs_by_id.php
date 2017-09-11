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
				$query = "SELECT * FROM publs WHERE publs_id = ?";
				$result = $db->executeQuery($query, array("$id"), 'get_publs_query');
				
				if($result === false) echo "<div class='error-message'>Публикация не найдена</div>";
				else {
					$row = $result->fetch(PDO::FETCH_ASSOC);
					echo json_encode($row);	
				}		
			}
			else echo "<div class='error-message'>Нет данных для поиска публикации.</div>";
		}
		else {
			echo "<div class='error-message'>Нет соединения с БД.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было найдено...</div>";
?>