<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($link) {
			if(isset($_POST['text']) && isset($_POST['id'])) {
				$text = strip_tags(clearStr($_POST['text']));
				$name = clearStr($_POST['name']);
				$id = (int)$_POST['id'];
				$query = "UPDATE users " .
						 "SET " . pg_escape_string($name) . " = '" . pg_escape_string($text) . "' " . 
						 "WHERE user_id = " . $id;
				$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Логин пользователя не был обновлен';
				else echo 'Логин пользователя был обновлен';			
			}
			echo "Нет данных для обновления.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было обновлено...";
?>