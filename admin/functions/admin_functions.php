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
			$query = "SELECT user_id FROM users WHERE user_login = ? LIMIT 1";
			$result = $db->executeQuery($query, array($login), 'select_user_id');
			
			if($result === false) echo "<div class='error-message'>Пользователь не был найдет</div>";
			else {
				$row = $result->fetch();
				return $row[0];	// TODO: не проверялось
			} 	
		}
	}
?>