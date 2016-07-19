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
	
	/*
		Функция получения электронного адреса пользователя по логину
		- принимает логин
		- возвращает email
		- TODO: пока не используется
	*/
	function getUserEmail($userLogin) {
		global $link;
		
		if($link) {
			$query = "SELECT user_email " .
					 "FROM users " . 
					 "WHERE user_login = '". pg_escape_string($userLogin) . "'";
			$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
			
			if($result === false) echo 'Такого пользователя нет';
			else return pg_fetch_result($result, 0, 0);
		}
	}
	
	/*
		Функция вывода всех комментариев для страницы
		- принимает адрес страницы в виде URI
	*/
	function getComments($uri) {
		global $link;
		
		if($link) {
			$query = "SELECT comments_author, comments_text " .
					 "FROM comments " . 
					 "WHERE comments_location = '" . pg_escape_string($uri) . "'";
			$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
			
			if($result === false) echo 'Ошибка в выборке комментариев';
			else {
				while($row = pg_fetch_assoc($result)) {
					echo "<tr>";
					foreach($row as $val) {
						echo "<td>". $val ."</td>";
					}
					echo "</tr>";
				}
			}
		}
	}	
?>