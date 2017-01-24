<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	
	global $link;
	
	if(!$link) $link = connectToPostgres();
	
	if($link) {
		$query = "SELECT * FROM log_type";
		$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
		
		if($result === false) {
			echo "Ошибка запроса";
		}
		else {
			$logsTypes = array();
					
			while($row = pg_fetch_assoc($result)) {
				$logsTypes[] = $row;
			}
			
			echo json_encode($logsTypes);
		}
	}
	else {
		echo "Подключение к базе данных не установлено";
	}
?>