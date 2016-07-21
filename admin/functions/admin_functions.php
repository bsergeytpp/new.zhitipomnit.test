<?
	require_once (__DIR__."/../../functions/functions.php");
		
	/*
		Функция поиска ID пользователя по логину
		- принимает логин
		- возвращает ID
	*/
	function getUserId($login) {
		global $link;
		
		if($link) {
			$query = "SELECT user_id " .
					 "FROM users " . 
					 "WHERE user_login = '" . $login ."'";
			$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
			
			if($result === false) echo 'Пользователь не был найдет';
			else {
				return pg_fetch_result($result, 0, 0);
			} 	
		}
	}
?>