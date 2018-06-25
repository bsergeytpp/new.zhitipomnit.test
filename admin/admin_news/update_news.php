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
			if(isset($_POST['news-text']) && 
				isset($_POST['news-id'] && 
				isset($_POST['news-date'] && 
				isset($_POST['news-header']))) {
				$newsText = clearStr($_POST['news-text']);
				$newsHeader = strip_tags(clearStr($_POST['news-header']));
				$newsId = clearStr($_POST['news-id']);
				$newsDate = $_POST['news-date']);
				$query = "UPDATE news SET news_date = ?, news_text = ?, news_header = ? WHERE news_id = ?";
				$result = $db->executeQuery($query, array("$newsDate", "$newsText", "$newsHeader", "$id"), 'update_news_query');
				
				// данные для логирования
				$log_type = 2;
				$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$log_date = date('Y-m-d H:i:sO');
				$log_important = $_SESSION['admin'];
				
				if($result === false) {
					echo "<div class='error-message'>Новость не была обновлена</div>";
					$log_name = 'failed to update news';
					$log_text = 'user '.$_SESSION['user'].' has failed to update news: '.$id;
				}
				else {
					echo "<div class='success-message'>Новость была обновлена</div>";
					$log_name = 'updated news';
					$log_text = 'user '.$_SESSION['user'].' has updated news: '.$id;
				}
				
				echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
			}
			else {
				echo "<div class='error-message'>Нет данных для обновления.</div>";
			}
		}
		else {
			echo "<div class='error-message'>Нет соединения с БД.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было обновлено...";
?>