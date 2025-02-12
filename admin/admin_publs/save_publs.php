<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../../sessions/session.inc.php");
	
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			$header = clearStr($_POST['publs-header']);
			$text = clearStr($_POST['publs-text']);
		    $query = "INSERT INTO publs (publs_header, publs_text) VALUES (?, ?)";
			$result = $db->executeQuery($query, array("$header", "$text"), 'save_publs_query');
			
			// данные для логирования
			global $logData;
			$logData['type'] = 3;
			$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$logData['date'] = date('Y-m-d H:i:sO');
			$logData['important'] = $_SESSION['admin'] || false;
			$logData['ip'] = getUserIp();
			
			if($result === false) {
				echo "<div class='error-message'>Публикация не была добавлена</div>";
				$logData['name'] = 'failed to add a publ';
				$logData['text'] = 'user '.$_SESSION['user'].' has failed to add a publ: '.$header;
			}
			else {
				echo "<div class='success-message'>Публикация была добавлена</div>";
				$logData['name'] = 'added a publ';
				$logData['text'] = 'user '.$_SESSION['user'].' has added a publ: '.$header;
			}
			
			echo addLogs($logData);
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