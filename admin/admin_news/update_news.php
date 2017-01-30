<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $db;
	
	if(!$_SESSION['admin']) {
		echo "Вы не админ";
		break;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($db->getLink()) {
			if(isset($_POST['text']) && isset($_POST['id'])) {
				$text = clearStr($_POST['text']);
				$name = clearStr($_POST['name']);
				
				if($name === 'news_header') {
					$text = strip_tags($text);
				}
				
				$id = (int)$_POST['id'];
				$query = "UPDATE news SET " . pg_escape_string($name) . " = $1 WHERE news_id = $2";
				$result = $db->executeQuery($query, array("$text", "$id"), 'update_news_query');
				
				if($result === false) {
					echo 'Новость не была обновлена';
					$log_type = 2;
					$log_name = 'failed to update news';
					$log_text = 'user '.$_SESSION['user'].' has failed to update news: '.$id;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = true;
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
				}
				else {
					echo 'Новость была обновлена';
					$log_type = 2;
					$log_name = 'updated news';
					$log_text = 'user '.$_SESSION['user'].' has updated news: '.$id;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = $_SESSION['admin'];
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
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