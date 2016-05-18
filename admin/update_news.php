<?
	require_once "session.inc.php";
	require_once "secure.inc.php";
	require_once "../functions/functions.php";
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$text = ''; $id = -1;
		if($link) {
			if(isset($_POST['text']) && isset($_POST['id'])) {
				$text = clearStr($_POST['text']);
				$id = (int)$_POST['id'];
				$query = "UPDATE news
						  SET news_text = '" . pg_escape_string($text) . "' " . 
						  "WHERE news_id = " . $id;
				$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Новость не была обновлена';
				else echo 'Новость была обновлена';			
			}
			echo "Нет данных для обновления.";
		}
		else {
			/*$newsArr[] = clearStr($_POST['news-date']);
			$newsArr[] = clearStr($_POST['news-text']);
			$newsArr[] = clearStr($_POST['news-date']);
			$newsStr = serialize($newsArr);

			//echo "Серилизованная новость: $newsStr";
			file_put_contents('../content/news/'.$newsArr[0].'.txt', $newsStr);*/
			echo "Нет соединения с БД.";
		}
	}
	else echo "Ничего не было обновлено...";
?>