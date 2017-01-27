<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($link) {
			$header = clearStr($_POST['publs-header']);
			$text = clearStr($_POST['publs-text']);
		    $query = "INSERT INTO publs (publs_header, publs_text) VALUES ($1, $2)";
			$result = executeQuery($query, array("$header", "$text"), 'save_publs_query');
			
			if($result === false) {
				echo 'Публикация не была добавлена';
				$log_type = 3;
				$log_name = 'failed to add a publ';
				$log_text = 'user '.$_SESSION['user'].' has failed to add a publ: '.$header;
				$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$log_date = date('Y-m-d H:i:sO');
				$log_important = true;
				echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
			}
			else {
				echo 'Публикация была добавлена';
				$log_type = 3;
				$log_name = 'added a publ';
				$log_text = 'user '.$_SESSION['user'].' has added a publ: '.$header;
				$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$log_date = date('Y-m-d H:i:sO');
				$log_important = $_SESSION['admin'];
				echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
			}				
		}
		else {
			echo "Тут могла быть ваша Публикация.";
		}
	}
	else echo "Ничего не было передано...";
?>