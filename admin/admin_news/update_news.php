<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../../sessions/session.inc.php");
	
	global $db;
	
	if(!$_SESSION['admin']) {
		echo "<div class='error-message'>Вы не админ</div>";
		exit;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			if(isset($_POST['news-text']) || 
				isset($_POST['news-id']) || 
				isset($_POST['news-date']) || 
				isset($_POST['news-header'])) {
				$newsText = clearStr($_POST['news-text']);
				$newsHeader = isset($_POST['news-header']) ?  strip_tags(clearStr($_POST['news-header'])) : NULL;
				$newsId = clearStr($_POST['news-id']);
				$newsDate = isset($_POST['news-date']) ? $_POST['news-date'] : NULL;
				$query = "UPDATE news SET news_date = COALESCE(?, news_date),
										  news_text = COALESCE(?, news_text),
										  news_header = COALESCE(?, news_header) WHERE news_id = ?";
				$result = $db->executeQuery($query, array($newsDate, "$newsText", "$newsHeader", "$newsId"), 'update_news_query');
				
				// данные для логирования
				global $logData;
				$logData['type'] = 2;
				$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$logData['date'] = date('Y-m-d H:i:sO');
				$logData['important'] = $_SESSION['admin'] || false;
				$logData['ip'] = getUserIp();
				
				if($result === false) {
					echo "<div class='error-message'>Новость не была обновлена</div>";
					$logData['name'] = 'failed to update news';
					$logData['text'] = 'user '.$_SESSION['user'].' has failed to update news: '.$newsId;
				}
				else {
					echo "<div class='success-message'>Новость была обновлена</div>";
					$logData['name'] = 'updated news';
					$logData['text'] = 'user '.$_SESSION['user'].' has updated news: '.$newsId;
				}
				
				echo addLogs($logData);
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