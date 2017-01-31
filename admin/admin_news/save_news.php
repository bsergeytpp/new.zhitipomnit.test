<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			$tmpNames = [];
			$img = [];
			$imgAlign = $_POST['image-align'];
			
			// получаем список файлов
			foreach($_FILES['news-image']['error'] as $key => $error) {
				if($error == UPLOAD_ERR_OK) {
					$tmpNames[] = $_FILES['news-image']['tmp_name'][$key];
				}
			}

			$totalFiles = count($tmpNames);
			
			// загружаем файлы в папку
			for($i=0; $i<$totalFiles; $i++) {
				if(is_uploaded_file($tmpNames[$i])) {
					move_uploaded_file($tmpNames[$i], '../images/'.$_FILES['news-image']['name'][$i]);
					$img[] = '/admin/images/'.$_FILES['news-image']['name'][$i];
				}
			}
			
			$date = $_POST['news-date'];
			$header = clearStr($_POST['news-header']);
			$text = clearStr($_POST['news-text']);
			$author = clearStr((isset($_SESSION['user'])) ? $_SESSION['user'] : 'default');
			
			// вставляем ссылки на картинки
			if(count($img) >= 0) {
				for($i=0, $j=1; $i<$totalFiles; $i++, $j++) {
					$tmpDiv = "<div style='text-align: $imgAlign[$i]'><img src='$img[$i]' alt=''></div>";
					$text = str_replace('$IMAGE'.$j, $tmpDiv, $text);
				}
			}
			
			$text = pg_escape_string($text);
			
			$query = "INSERT INTO news (news_date, news_header, news_text, news_author)
					  VALUES (?, ?, ?, ?)";
		    $result = $db->executeQuery($query, array("$date", "$header", "$text", "$author"), 'save_news_query');
						
			if($result === false) {
				echo 'Новость не была добавлена';
				$log_type = 2;
				$log_name = 'failed to add news';
				$log_text = 'user '.$_SESSION['user'].' has failed to add news: '.$header;
				$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$log_date = date('Y-m-d H:i:sO');
				$log_important = true;
				echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
			}
			else {
				echo 'Новость была добавлена';
				$log_type = 2;
				$log_name = 'added news';
				$log_text = 'user '.$_SESSION['user'].' has added news: '.$header;
				$log_location = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$log_date = date('Y-m-d H:i:sO');
				$log_important = $_SESSION['admin'];
				echo addLogs($log_type, $log_name, $log_text, $log_location, $log_date, $log_important);
			}
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