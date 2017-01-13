<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($link) {
			$tmpNames = [];
			foreach($_FILES['news-image']['error'] as $key => $error) {
				if($error == UPLOAD_ERR_OK) {
					$tmpNames[] = $_FILES['news-image']['tmp_name'][$key];
				}
			} 
			
			$img = [];
			$imgAlign = 'center';
			
			$totalFiles = count($tmpNames);
			
			for($i=0; $i<$totalFiles; $i++) {
				if(is_uploaded_file($tmpNames[$i])) {
					move_uploaded_file($tmpNames[$i], '../images/'.$_FILES['news-image']['name'][$i]);
					$img[] = '/admin/images/'.$_FILES['news-image']['name'][$i];
					$imgAlign = $_POST['news-image-align'];
				}
			}
			
			$date = $_POST['news-date'];
			$header = clearStr($_POST['news-header']);
			$text = clearStr($_POST['news-text']);
			
			if(count($img) >= 0) {
				for($i=0, $j=1; $i<$totalFiles; $i++, $j++) {
					$tmpDiv = "<div style='text-align: $imgAlign'><img src='$img[$i]' alt=''></div>";
					$text = str_replace('$IMAGE'.$j, $tmpDiv, $text);
				}
				//echo "новый текст: ".$text;
			}
			
			$text = pg_escape_string($text);
			
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