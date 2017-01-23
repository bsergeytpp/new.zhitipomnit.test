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
			$result = pg_prepare($link, "save_publs_query", $query);
			$result = pg_execute($link, "save_publs_query", array("$header", "$text")) 
					  or die('Query error: '. pg_last_error());
			
			if($result === false) echo 'Публикация не была добавлена';
			else {
				echo 'Публикация была добавлена';
				$log_name = 'publ-add';
				$log_text = 'user '.$_SESSION['user'].' has added a publ: '.$header;
				$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$log_date = date('Y-m-d H:i:sO');;
				$log_important = $_SESSION['admin'];
				echo addLogs($log_name, $log_text, $log_location, $log_date, $log_important);
			}				
		}
		else {
			echo "Тут могла быть ваша Публикация.";
		}
	}
	else echo "Ничего не было передано...";
?>