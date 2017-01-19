<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $link;
	$link = connectToPostgres();
	
	if(!$_SESSION['admin']) {
		echo "Вы не админ";
		break;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($link) {
			if(isset($_POST['text']) && isset($_POST['id'])) {
				$text = clearStr($_POST['text']);
				$name = clearStr($_POST['name']);
				
				if($name === 'publs_header') {
					$text = strip_tags($text);
				}
				
				$id = (int)$_POST['id'];
				$query = "UPDATE publs SET " . pg_escape_string($name) . " = $1 " . 
						 "WHERE publs_id = $2";
				$result = pg_prepare($link, "update_publs_query", $query);
				$result = pg_execute($link, "update_publs_query", array("$text", "$id")) 
						  or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Публикация не была обновлена';
				else echo 'Публикация была обновлена';			
			}
			echo "Нет данных для обновления.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было обновлено...";
?>