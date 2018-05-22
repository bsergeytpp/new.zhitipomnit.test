<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	if(!$_SESSION['admin']) {
		echo "<div class='error-message'>Вы не админ</div>";
		exit;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($db->getLink()) {
			if(isset($_POST['text']) && isset($_POST['id'])) {
				$text = clearStr($_POST['text']);
				$name = clearStr($_POST['name']);
				
				if($name === 'publs_header') {
					$text = strip_tags($text);
				}
				
				$id = (int)$_POST['id'];
				$query = "UPDATE publs SET " . pg_escape_string($name) . " = ? " . 
						 "WHERE publs_id = ?";
			    $result = $db->executeQuery($query, array("$text", "$id"), 'update_publs_query');
				
				if($result === false) {
					echo "<div class='error-message'>Публикация не была обновлена</div>";
					$log_type = 3;
					$log_name = 'failed to update a publ';
					$log_text = 'user '.$_SESSION['user'].' has failed to update a publ: '.$id;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = true;
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
				}
				else {
					echo "<div class='success-message'>Публикация была обновлена</div>";
					$log_type = 3;
					$log_name = 'updated a publ';
					$log_text = 'user '.$_SESSION['user'].' has updated a publ: '.$id;
					$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
					$log_date = date('Y-m-d H:i:sO');
					$log_important = $_SESSION['admin'];
					echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
				}					
			}
			echo "<div class='error-message'>Нет данных для обновления.</div>";
		}
		else {
			echo "<div class='error-message'>Нет соединения с БД.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было обновлено...</div>";
?>