<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	function uploadImages($imgParams, $text, $date) {
		$tmpNames = [];
		$img = [];
		
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
				mkdir('../../content/images/news/'.$date);
				move_uploaded_file($tmpNames[$i], '../../content/images/news/'.$date.'/'.$_FILES['news-image']['name'][$i]);
				$img[] = '/content/images/news/'.$date.'/'.$_FILES['news-image']['name'][$i];
			}
		}
		
		// вставляем ссылки на картинки
		if(count($img) >= 0) {
			for($i=0, $j=1; $i<$totalFiles; $i++, $j++) {
				$align = $imgParams['align'][$i];
				$width = $imgParams['width'][$i];
				$height = $imgParams['height'][$i];
				$tmpDiv = '<div style="text-align: '.$align.'"><img src="'.$img[$i].'" width="'.$width.'" height="'.$height.'" alt=""></div>';
				$text = str_replace('$IMAGE'.$j, $tmpDiv, $text);
			}
		}
		
		return $text;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			$imgParams = [
				'align' => $_POST['image-align'],
				'width' => $_POST['image-width'],
				'height' => $_POST['image-height']
			];
			$date = $_POST['news-date'];
			$header = clearStr($_POST['news-header']);
			$text = clearStr($_POST['news-text']);
			$author = clearStr((isset($_SESSION['user'])) ? $_SESSION['user'] : 'default');
			$text = pg_escape_string(uploadImages($imgParams, $text, $date));
			$query = "INSERT INTO news (news_date, news_header, news_text, news_author)
					  VALUES (?, ?, ?, ?)";
		    $result = $db->executeQuery($query, array("$date", "$header", "$text", "$author"), 'save_news_query');
			
			// данные для логирования
			global $logData;
			$logData['type'] = 2;
			$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$logData['date'] = date('Y-m-d H:i:sO');
			$logData['important'] = $_SESSION['admin'] || false;
			$logData['ip'] = getUserIp();
			
			if($result === false) {
				echo "<div class='error-message'>Новость не была добавлена</div>";
				$logData['name'] = 'failed to add news';
				$logData['text'] = 'user '.$_SESSION['user'].' has failed to add news: '.$header;
			}
			else {
				echo "<div class='success-message'>Новость была добавлена</div>";
				$logData['name'] = 'added news';
				$logData['text'] = 'user '.$_SESSION['user'].' has added news: '.$header;
			}
			
			echo addLogs($logData);
		}
		else {
			/*$newsArr[] = clearStr($_POST['news-date']);
			$newsArr[] = clearStr($_POST['news-text']);
			$newsArr[] = clearStr($_POST['news-date']);
			$newsStr = serialize($newsArr);

			//echo "Серилизованная новость: $newsStr";
			file_put_contents('../content/news/'.$newsArr[0].'.txt', $newsStr);*/
			echo "<div class='warning-message'>Тут могла быть ваша новость.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было передано...</div>";
?>
<!DOCTYPE html>
<html>
<head>
	<title>Админка - сохранение новости</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
</head>
<body>
	<a href="/admin/">Назад в админку</a><br>
	<a href="/admin/admin_news/add_news.php">Добавить еще новость</a>
</body>