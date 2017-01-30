<?
	require_once (__DIR__."/../../functions/functions.php");
		
	/*
		Функция поиска ID пользователя по логину
		- принимает логин
		- возвращает ID
	*/
	function getUserId($login) {
		global $db;
		
		if($db->getLink()) {
			$query = "SELECT user_id FROM users WHERE user_login = $1";
			$result = $db->executeQuery($query, array($login), 'select_user_id');
			
			if($result === false) echo 'Пользователь не был найдет';
			else {
				return pg_fetch_result($result, 0, 0);
			} 	
		}
	}
?>