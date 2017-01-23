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
				
				if($name === 'news_header') {
					$text = strip_tags($text);
				}
				
				$id = (int)$_POST['id'];
				$query = "UPDATE news SET " . pg_escape_string($name) . " = $1 WHERE news_id = $2";
				$result = pg_prepare($link, "update_news_query", $query);
				$result = pg_execute($link, "update_news_query", array("$text", "$id")) 
						  or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Новость не была обновлена';
				else {
					echo 'Новость была обновлена';
					$log_name = 'login';
					$log_text = 'user '.$_SESSION['user'].' has updated news: '.$id;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');;
					$log_important = $_SESSION['admin'];
					echo addLogs($log_name, $log_text, $log_location, $log_date, $log_important);
				}					
			}
			else {
				echo "Нет данных для обновления.";
			}
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было обновлено...";
?>