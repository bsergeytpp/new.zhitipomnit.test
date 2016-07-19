<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		$id = -1;
		if($link) {
			if(isset($_GET['id'])) {
				$id = (int)$_GET['id'];
				$query = "SELECT * FROM publs WHERE publs_id = " . $id;
				$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Публикация не найдена';
				else {
					$row = pg_fetch_assoc($result);
					echo json_encode($row);	
				}		
			}
			else echo "Нет данных для поиска публикации.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было найдено...";
?>