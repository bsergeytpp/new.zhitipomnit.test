<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			$header = clearStr($_POST['publs-header']);
			$text = clearStr($_POST['publs-text']);
		    $query = "INSERT INTO publs (publs_header, publs_text) VALUES (?, ?)";
			$result = $db->executeQuery($query, array("$header", "$text"), 'save_publs_query');
			
			if($result === false) {
				echo "<div class='error-message'>Публикация не была добавлена</div>";
				$log_type = 3;
				$log_name = 'failed to add a publ';
				$log_text = 'user '.$_SESSION['user'].' has failed to add a publ: '.$header;
				$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$log_date = date('Y-m-d H:i:sO');
				$log_important = true;
				echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
			}
			else {
				echo "<div class='success-message'>Публикация была добавлена</div>";
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
			echo "<div class='error-message'>Тут могла быть ваша Публикация.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было передано...</div>";
?>
<!DOCTYPE html>
<html>
<head>
	<title>Админка - сохранение статьи</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
</head>
<body>
	<a href="/admin/">Назад в админку</a><br>
	<a href="/admin/admin_publs/add_publs.php">Добавить еще статью</a>
</body>