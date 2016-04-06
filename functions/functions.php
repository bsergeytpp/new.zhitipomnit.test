<?
	function clearStr($str) {
		return preg_replace('~\R~u', "", trim($str));
	}
	
	function exceptStr($str) {
		for($i=0; $i<strlen($str); $i++)
			if($str[$i] == '.' && $i > 200)
				$str = substr($str, 0, $i+1);
				
		return $str;
	}
	
	function adaptModernNews($news) {
		for($i=0; $i<3; $i++)
			if($i == 2)
				unset($news[$i]);	
				
		$newsKeys = ['newsDate', 'newsText'];
		$news = array_combine($newsKeys, $news);
		$newsFull = file_get_contents('content/templates/news_full.php');
		$newsFull = replaceTemplateTags($newsFull, $news);
		
		return $newsFull;
	}
	
	function createExceptNews($news) {
		for($i=0; $i<3; $i++) {
			if($i == 1) {
				$news[$i] = exceptStr(strip_tags($news[$i]));
			}
		}
				
		$newsKeys = ['newsDate', 'newsText', 'newsUrl'];
		$news = array_combine($newsKeys, $news);
		$newsTemplate = file_get_contents('content/templates/news_template.php');
		$newsTemplate = replaceTemplateTags($newsTemplate, $news);
		
		return $newsTemplate;
	}
	
	function getAllNews($page) {
		$dir = "content/news/";
		$newsArr = scandir($dir);
		$rNews = [];
		
		foreach($newsArr as $news) {
			$news_path = $dir.$news;
			if(file_exists($news_path) && is_file($news_path)) {
				if(substr($news_path, -3, 3) == 'txt') {
					$rNews[] = unserialize(clearStr(file_get_contents($news_path)));
				}
			}
		}
		
		$rNews = sortNews($rNews);
		
		for($i=0; $i<count($rNews); $i++) {
			$rNews[$i] = createExceptNews($rNews[$i]);
		}
		
		createNewsList($rNews, $page);
	}
	
	function createNewsList($news, $page) {
		$totalNews = count($news);
		
		// делаем ссылки на страницы списка		
		$list = getULlist($totalNews, 3, 'index.php?pages=news&page=', $page);
		
		// новостей мало, список не делаем
		if($totalNews <= 3) {
			foreach($news as $cur) {
				file_put_contents('content/templates/smth.php', $cur, FILE_APPEND); 
			}
			return;
		}
		
		// создаем список новостей для страницы
		$tempArr = [];
		for($i=$page*3; $i>($page*3-3); $i--) {
			array_unshift($tempArr, $news[$i-1]);
		}
				
		file_put_contents('content/templates/smth.php', $tempArr, FILE_APPEND);
		
		echo $list;
		
		include 'content/templates/smth.php'; 
		
		echo $list;
	}
	
	function createOldNewsList($oldNews, $page) {
		$dom = new DOMDocument;
		$oldNews = mb_convert_encoding($oldNews, 'HTML-ENTITIES', "UTF-8");
		$dom->loadHTML($oldNews);
				
		$p_elems = $dom->getElementsByTagName('p');
		$totalNews = $p_elems->length;
		
		// делаем ссылки на страницы списка
		$list = getULlist($totalNews, 10, 'index.php?pages=news&custom-news-date=all-old&page=', $page);
		
		// новостей мало, список не делаем
		if($totalNews <= 10) {
			echo $oldNews;
			return;
		}
		
		// переносим DOM-элементы в новый документ и выводим его
		$dom2 = new DOMDocument;
		
		for($i = $page*10; $i>($page*10-10); $i--) {
			$node = $dom2->importNode($p_elems->item($i-1), true);
			if(!$dom2->hasChildNodes()) {
				$dom2->appendChild($node);
				$firstChild = $dom2->firstChild;
				continue;
			}
			// вывод по убыванию даты
			$firstChild->parentNode->insertBefore($node, $firstChild);
			$firstChild = $dom2->firstChild;
		}
		
		echo $list, $dom2->saveHTML(), $list;
	}
	
	function getULlist($totalNews, $newsPerPage, $href, $pageNum) {
		$list = '<ul class="news-list"><li> << ';
		for($j = 1; $j <= $totalNews/$newsPerPage; $j++) {
			if($j == $pageNum) {
				$list .= " <li>" . $j . " ";
				continue;
			}
			$list .= " <li><a href='$href$j'>" . $j . "</a> ";
		}
		
		$list .= '<li> >> </ul><br>';
		
		return $list;
	}
	
	function sortNews($newsArr) {
		for($i=1; $i<count($newsArr); $i++) {					
			for($j= $i-1; $j>=0; $j--) {		
				if(reverseDate($newsArr[$j][0]) < reverseDate($newsArr[$j+1][0])) {
					$temp = $newsArr[$j+1];
					$newsArr[$j+1] = $newsArr[$j];
					$newsArr[$j] = $temp;
				}
			}
		}
		
		return $newsArr;
	}
	
	function reverseDate($date) {
		return intval(substr($date, -2, 2) . substr($date, -5, 2) . substr($date, 0, 2));
	}
	
	function getSingleNews($date) {
		// Определяем формат даты
		$dateArr = explode('-', $date);
		if(strlen((string)$dateArr[0]) > 2) {
			$date = $dateArr[2].'-'.$dateArr[1].'-'.substr($dateArr[0], -2, 2);
		}
		// Определяем тип новости
		$pageNum = isset($_GET['page']) ? $_GET['page'] : '1';
		if(file_exists('content/news/'.$date.'.txt')) {
			return getSingleModernNews($date.'.txt', $pageNum);
		}
		else if(file_exists('content/news/'.$date.'.html')) {
			return getSingleOldNews($date.'.html', $pageNum);
		}
		else {
			echo "<h1>Такой новости не существует!</h1>";
			echo "<a href='index.php?pages=news&page=$pageNum'>Вернуться назад</a>";
			return;
		}
	}
	
	function getSingleModernNews($name, $pageNum) {
		echo "<strong><a href='index.php?pages=news&page=$pageNum'>Назад</a></strong>";
		
		return adaptModernNews(unserialize(file_get_contents("content/news/".$name)));
	}
	
	function getSingleOldNews($name, $pageNum) {		
		echo "<strong><a href='index.php?pages=news&custom-news-date=all-old&page=$pageNum'>Назад</a></strong>";
		echo "<script>document.addEventListener('DOMContentLoaded', function() { changeStyle(); }, false);</script>";
		
		return adaptOldNews(file_get_contents("content/news/$name"));
	}
	
	function adaptOldNews($newsToAdapt) {
		
		
		// ошибки в коде старых новостей
		$pattern = [
			'/materials/',
			'/Фонд Жить и Помнить/',
			'/Новости фонда ЖИТЬ И ПОМНИТЬ/',
			'/<img src="..\/\images\/\m1.gif" width="100%" height="28" border="0" \/\>/',
			'/<img src="..\/\images\/\m2.gif" width="100%" height="21" border="0" \/\>/',
			'/style=padding-top: 10""/',
			'/style=padding-top:10""/',
			'/style=padding-top:10"/',
			'/style=padding-top: 10"/',
			'/style=padding-top: 10 ""/',
			'/"padding-left: 25; padding-right: 25; padding-top: 0; padding-bottom: 25""/'
		];
		$replacement = array_fill(0, 11, '');
		$replacement[0] = '/content/news/materials/';
		$adaptedNews = preg_replace($pattern, $replacement, $newsToAdapt);
		
		if(!mb_detect_encoding($adaptedNews, "UTF-8", true)) {
			$adaptedNews = mb_convert_encoding($adaptedNews, "UTF-8", "windows-1251");
		}
		
		$adaptedNews = strip_tags($adaptedNews, '<h1><h2><h3><p><strong><a><img><ol><ul><li>');

		return $adaptedNews;
	}
	
	function getAllOldNews($page) {
		if(!$page) $page = 1;
		
		$allNews = file_get_contents("content/news/archive_news.html");
		$allNews = strip_tags($allNews, '<p><strong><a>');
		
		createOldNewsList($allNews, $page);
	}
	
	function replaceTemplateTags($string, $replace) {
		return str_replace(array_keys($replace), $replace, $string);
	}
	
	function getPubls() {
		$dir = "content/publ/";
		$publArr = scandir($dir);
		$rPubls = [];
		
		foreach($publArr as $publName) {
			$publPath = $dir.$publName;
			if(file_exists($publPath) && is_file($publPath)) {
				if(substr($publPath, -3, 3) == 'txt') {	// новые статьи
					$publ = file_get_contents($publPath);
					$publ = createExceptPubl($publ, false);
					$rPubls[] = $publ;
				}
			}
		}
		
		$oldPubls = getOldPubls();
		
		foreach($oldPubls as $publ) $rPubls[] = $publ;
		
		return $rPubls;
	}
	
	function getOldPubls() {
		$publsList = file('content/publ/publik.html');
		$arr = [];
		
		foreach($publsList as $publ) {
			// получаем ссылку
			$a = strpos($publ, '"');
			$b = strpos($publ, '"', $a+1);
			$href = substr($publ, $a+1, --$b-$a);
			// получаем заголовок
			$a = strpos($publ, '>');
			$b = strpos($publ, '<', $a);
			$text = substr($publ, $a+1, --$b-$a);
			$oldPubl = [
				'link' => 'content/publ/'.$href,
				'text' => $text
			];
			$oldPubl = createExceptPubl($oldPubl, true);
			$arr[] = $oldPubl;
		}
		
		return $arr;
	}
	
	function getSinglePubl($name) {
		if(substr($name, -3, 3) == 'txt') {
			$publArr = unserialize(file_get_contents($name));
			for($i=0; $i<3; $i++) {
				if($i == 2)
					$publ = $publArr[$i];
			}
		}
		else if(substr($name, -4, 4) == 'html'){
			$publ = file_get_contents($name);
			// ошибка в большом кол-ве файлов
			$pattern = [
				'/materials/',
				'/ПУБЛИКАЦИИ/',
				'/<img src="..\/\images\/\m1.gif" width="100%" height="28" border="0" \/\>/',
				'/<img src="..\/\images\/\m2.gif" width="100%" height="21" border="0" \/\>/',
				'/<IMG SRC="..\/\images\/\m2.gif" ALIGN=BOTTOM WIDTH=100% HEIGHT=21 BORDER=0>/',
				'/style=padding-top: 10""/',
				'/style=padding-top:10""/',
				'/style=padding-top:10"/',
				'/style=padding-top: 10"/',
				'/style=padding-top: 10 ""/'
			];
			$replacemetnt = array_fill(0, 10, '');
			$replacemetnt[0] = "/content/publ/materials/";
			$publ = preg_replace($pattern, $replacemetnt, $publ);
			$publ = preg_replace("/Фонд Жить и Помнить/", "", $publ, 1);
			
			if(!mb_detect_encoding($publ, "UTF-8", true)) 
				$publ = mb_convert_encoding($publ, "UTF-8", "windows-1251");
			
			$publ = strip_tags($publ, '<h1><h2><h3><p><strong><a><img><ol><ul><li>');
		}
		else {
			if(file_exists($name)) {
				header('Content-Description: File Transfer');
				header('Content-Type: application/octet-strem');
				header('Content-Disposition: attachment; filename='.basename($name));
				header('Content-Transfer-Encoding: binary');
				header('Expires: 0');
				header('Cache-Control: must-revalidate');
				header('Pragma: public');
				header('Content-Length: '.filesize($name));
				ob_clean();
				flush();
				readfile($name);
			}
			exit;
		}
		
		return $publ;
	}
	
	function createExceptPubl($publ, $isOld) {
		$publTemplate = file_get_contents('content/templates/publ_template.php');
		if($isOld) {
			$publTemplate = preg_replace("/publUrl/", $publ['link'], $publTemplate);
			$publTemplate = preg_replace("/publHeader/", $publ['text'], $publTemplate);
		}
		else {
			$publArr = unserialize($publ);
			$publKeys = ['publHeader', 'publUrl', 'publText'];
			$publArr = array_combine($publKeys, $publArr);
			$publTemplate = replaceTemplateTags($publTemplate, $publArr);
		}
		
		return $publTemplate;
	}
	
	function getPressArray($name) {
		$pressArr = [];
		
		for($i=0,$j=1;$i<4;$i++,$j++) {
			$press = file_get_contents("content/press/$name/$j.html");
						
			// ошибки в большом кол-ве файлов
			$pattern = [
				'/materials/',
				'/<img src="..\/\..\/\images\/\m1.gif" width="100%" height="28" border="0" \/\>/',
				'/log.jpg/',
				'/<img src="..\/\..\/\images\/\" width="687" height="153" \/\>/',
				'/<img src="..\/\..\/\images\/\m2.gif" width="100%" height="21" border="0" \/\>/',
				'/style=padding-top: 10""/',
				'/style=padding-top:10""/',
				'/style=padding-top:10"/',
				'/style=padding-top: 10"/',
				'/style=padding-top: 10 ""/',
				'/bgcolor="#FFFFFF""/'
			];
			$replacement = array_fill(0, 11, '');
			$replacement[0] = "/content/press/$name/materials/";
			$press = preg_replace($pattern, $replacement, $press);
			
			if(!mb_detect_encoding($press, 'UTF-8', true)) {
				$press = mb_convert_encoding($press, "UTF-8", 'windows-1251');
			}
			
			$press = strip_tags($press, '<h1><h2><h3><p><strong><a><img><ul><ol><li>');	
			$pressArr[] = $press;
		}
		
		return $pressArr;
	}
	
	function getPressPage($pressArr, $pageNum) {
		return $pressArr[$pageNum];
	}
	
	// PostgreSQL functions
	function connectToPostgres() {
		$link = pg_connect("host=192.168.0.4 dbname=new.zip user=root password=pass") or die("No DB connection: " . pg_last_error());
		return $link;
	}
?>