<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($link) {
			$date = $_POST['news-date'];
			$header = clearStr($_POST['news-header']);
			$text = clearStr($_POST['news-text']);
			$author = (isset($_SESSION['user'])) ? $_SESSION['user'] : 'default';
			$query = "INSERT INTO news (news_date, news_header, news_text, news_author)
					  VALUES ('$date', '$header', '$text', '" . $author . "')";
			$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
			
			if($result === false) echo 'Новость не была добавлена';
			else echo 'Новость была добавлена';			
		}
		else {
			/*$newsArr[] = clearStr($_POST['news-date']);
			$newsArr[] = clearStr($_POST['news-text']);
			$newsArr[] = clearStr($_POST['news-date']);
			$newsStr = serialize($newsArr);

			//echo "Серилизованная новость: $newsStr";
			file_put_contents('../content/news/'.$newsArr[0].'.txt', $newsStr);*/
			echo "Тут могла быть ваша новость.";
		}
	}
	else echo "Ничего не было передано...";
?>