<?
	require_once (__DIR__."/../functions/functions.php");
	
	if($_SERVER['REQUEST_METHOD'] === 'GET') {
		if($_GET['news_date']) {
			global $db;
			$newsDate = $_GET['news_date'];
			error_log('LOG: news date => '.$newsDate, 0);
				
			if($db->getLink()) {
				$query = "SELECT COUNT(news_id)
						  FROM news 
						  WHERE news_date = ?";
						
				$result = $db->executeQuery($query, array($newsDate), 'check_news_by_date');
				$newsCount = $result->fetchColumn();
				
				//error_log('LOG: news count => '.$newsCount, 0);
				//error_log('LOG: convert date => '.convertDate($newsDate), 0);
				
				if(!$newsCount) {
					if(file_exists(__DIR__.'/../content/news/'.convertDate($newsDate).'.html')) {
						//error_log('LOG: old news has been found', 0);
						echo json_encode(['date' => convertDate($newsDate), 'type' => 'old']);
					}
					else {
						//error_log('LOG: no news found', 0);
					}
				}
				else if($newsCount > 1) {
					echo json_encode(['date' => $newsDate, 'type' => 'db']);
				}
			}
			else {
				echo "<div class='error-message'>Нет подключения к базе данных</div>";
			}
		}
	}
?>