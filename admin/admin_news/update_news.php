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
				$text = clearStr($_POST['text']);
				$name = clearStr($_POST['name']);
				$id = (int)$_POST['id'];
				$query = "UPDATE news SET " . pg_escape_string($name) . " = $1 WHERE news_id = $2";
				$result = pg_prepare($link, "update_news_query", $query);
				$result = pg_execute($link, "update_news_query", array("$text", "$id")) 
						  or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Новость не была обновлена';
				else echo 'Новость была обновлена';			
			}
			echo "Нет данных для обновления.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было обновлено...";
?>