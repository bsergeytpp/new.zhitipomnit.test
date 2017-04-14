<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	
	global $db;
		
	if($db->getLink()) {
		$query = "SELECT * FROM log_type";
		$result = $db->executeQuery($query, null, null);
		
		if($result === false) {
			echo "<div class='error-message'>Ошибка запроса</div>";
		}
		else {
			$logsTypes = array();
					
			while($row = $result->fetch(PDO::FETCH_ASSOC)) {
				$logsTypes[] = $row;
			}
			
			echo json_encode($logsTypes);
		}
	}
	else {
		echo "<div class='error-message'>Подключение к базе данных не установлено</div>";
	}
?>