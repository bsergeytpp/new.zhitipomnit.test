<?
	//require_once "admin_security/session.inc.php";
	//require_once "admin_security/secure.inc.php";
	require_once "functions/admin_functions.php";
	global $link;
	$link = connectToPostgres();
	$userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($link) {
			if(isset($_POST['comments-text']) && isset($_POST['comments-login'])) {
				if(!checkToken($_POST['token'])) {
					echo "Проверка не пройдена.";
					exit;
				}
				
				$text = clearStr($_POST['comments-text']);
				$login = clearStr($_POST['comments-login']);
				$text = filter_var($text, FILTER_SANITIZE_STRING);
				$login = filter_var($login, FILTER_SANITIZE_STRING);
				$id = $_POST['comments-location-id'];
				$user_id = getUserId($login);
				$parent_id = $_POST['comments-parent'];
				$date = date('Y-m-d H:i:sO');
				$query = "INSERT INTO comments (comments_author, comments_location_id, comments_text, comments_date, comments_parent_id) " .
						 "VALUES ('$user_id', '$id', '$text', '$date', NULLIF('$parent_id','')::integer)";
				$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Комментарий не был добавлен';
				else {
					echo 'Комментарий был добавлен';
					$log_name = 'comment-add';
					$log_text = 'user '.$_SESSION['user'].' has added a comment: '.$text;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');;
					$log_important = $_SESSION['admin'];
					echo addLogs($log_name, $log_text, $log_location, $log_date, $log_important);
				}					
			}
			else echo "Нет данных для обновления.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
?>