<?
	function clearStr($str) {
		return preg_replace('~\R~u', "", trim($str));
	}
	
	function exceptStr($str) {
		$len = strlen($str);
		for($i=0; $i<$len; $i++) {
			if($str[$i] == '.' && $i > 200) {
				$str = substr($str, 0, $i+1);
				break;
			}
		}
				
		return $str;
	}
	
	function adaptModernNews($news) {
		unset($news[2]); // TODO: некрасиво
		$newsKeys = ['newsDate', 'newsText'];
		$news = array_combine($newsKeys, $news);
		$newsFull = file_get_contents('content/templates/news_full.php');
		$newsFull = replaceTemplateTags($newsFull, $news);
		
		return $newsFull;
	}
	
	function createExceptNews($news, $db) {
		if($db) {
			$news['news_header'] = exceptStr(strip_tags($news['news_header']));
			$newsTemplate = file_get_contents('content/templates/news_template.php');
			$newsTemplate = str_replace(['newsDate', 'newsText', 'newsUrl'], [$news['news_date'], $news['news_header'], $news['news_date']], $newsTemplate);
		}
		else {
			$news[1] = exceptStr(strip_tags($news[1]));	// TODO: некрасиво
			$newsKeys = ['newsDate', 'newsText', 'newsUrl'];
			$news = array_combine($newsKeys, $news);
			$newsTemplate = file_get_contents('content/templates/news_template.php');
			$newsTemplate = replaceTemplateTags($newsTemplate, $news);
		}
		return $newsTemplate;
	}
	
	function getAllNews($page) {
		$newsArr = [];
		$totalNews = null;
		$link = connectToPostgres();
		
		if($link) {
			$query = 'SELECT * FROM news';
			$res = pg_query($link, $query) or die('Query error: '. pg_last_error());
			//$row = pg_fetch_all($res);
			echo "<h4>Новости из базы данных</h4>";
			
			while($row = pg_fetch_assoc($res)) {
				$newsArr[] = $row;
			}
			
			$totalNews = count($newsArr);
			
			$newsArr = sortNews($newsArr, $totalNews, true);
			
			for($i = 0; $i<$totalNews; $i++) {
				$newsArr[$i] = createExceptNews($newsArr[$i], true);
			}

			pg_close($link);
		}
		else {
			$dir = "content/news/";
			$dirNews = scandir($dir);
			echo "<h4>Новости из текстовых файлов</h4>";
			
			foreach($dirNews as $news) {
				$news_path = $dir.$news;
				if(file_exists($news_path) && is_file($news_path)) {
					if(substr($news_path, -3, 3) == 'txt') {
						$newsArr[] = unserialize(clearStr(file_get_contents($news_path)));
					}
				}
			}
			$totalNews = count($newsArr);
			$newsArr = sortNews($newsArr, $totalNews, false);
			
			for($i=0; $i<$totalNews; $i++) {
				$newsArr[$i] = createExceptNews($newsArr[$i]);
			}
		}
		
		createNewsList($newsArr, $page, $totalNews);
	}
	
	function createNewsList($news, $page, $totalNews) {		
		// делаем ссылки на страницы списка		
		$list = getULlist($totalNews, 3, 'index.php?pages=news&page=', $page);
		
		// новостей мало, список не делаем
		if($totalNews <= 3) {
			echo implode($news);
			return;
		}
		
		// создаем список новостей для страницы
		$tempArr = [];
		for($i=$page*3; $i>($page*3-3); $i--) {
			if(isset($news[$i-1])) {
				array_unshift($tempArr, $news[$i-1]);
			}
		}
				
		echo $list, implode($tempArr), $list;
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
			if($p_elems->item($i-1) !== null) {
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
		}
		
		echo $list, $dom2->saveHTML(), $list;
	}
	
	function getULlist($totalElems, $elemsPerPage, $href, $pageNum) {
		$list = '<ul class="news-list"><li> << ';
		$totalPages = ceil($totalElems/$elemsPerPage);
		
		for($j = 1; $j <= $totalPages; $j++) {
			if($j == $pageNum) {
				$list .= " <li>" . $j . " ";
				continue;
			}
			$list .= " <li><a href='$href$j'>" . $j . "</a> ";
		}
		
		$list .= '<li> >> </ul><br>';
		
		return $list;
	}
	
	function sortNews($newsArr, $totalNews, $isDb) {
		for($i=1; $i<$totalNews; $i++) {					
			for($j= $i-1; $j>=0; $j--) {
				if($isDb) {
					$tempCur = str_replace('-', '', $newsArr[$j]['news_date']);
					$tempNext = str_replace('-', '', $newsArr[$j+1]['news_date']);
				}
				else {
					$tempCur = reverseDate($newsArr[$j][0]);
					$tempNext = reverseDate($newsArr[$j+1][0]);
				}
				if(intval($tempCur) < intval($tempNext)) {
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
	
	function getSingleNews($date, $pageNum) {
		$link = connectToPostgres();
		// Определяем формат даты
		$dateArr = explode('-', $date);
		
		if(strlen((string)$dateArr[0]) > 2) {
			$date = $dateArr[2].'-'.$dateArr[1].'-'.substr($dateArr[0], -2, 2);
		}
		
		// Определяем тип новости		
		if(file_exists('content/news/'.$date.'.txt')) {
			return getSingleModernNews($date.'.txt', $pageNum);
		}
		else if(file_exists('content/news/'.$date.'.html')) {
			return getSingleOldNews($date.'.html', $pageNum);
		}
		else if($link) {
			$date = implode('-', $dateArr);
			$res = pg_query($link, "SELECT news_date, news_header, news_text FROM news WHERE news_date = '$date'") or die('Query error: '. pg_last_error());
			$row = pg_fetch_assoc($res);
			if(!$row) {
				echo "<h1>Такой новости не существует!</h1>";
				echo "<a href='index.php?pages=news&page=$pageNum'>К новостям</a>";
				return;
			}
			return getSingleDbNews($row, $pageNum);
		}
		else {
			echo "<h1>Такой новости не существует!</h1>";
			echo "<a href='index.php?pages=news&page=$pageNum'>К новостям</a>";
			return;
		}
	}
	
	function getSingleDbNews($news, $pageNum) {
		echo "<strong><a href='index.php?pages=news&page=$pageNum'>К новостям</a></strong>";
		$newsFull = file_get_contents('content/templates/news_full.php');
		$newsFull = str_replace(['newsDate', 'newsText'], [$news['news_date'], "<h4>".$news['news_header']."</h4>".$news['news_text']], $newsFull);
		
		return $newsFull;
	}
	
	function getSingleModernNews($name, $pageNum) {
		echo "<strong><a href='index.php?pages=news&page=$pageNum'>К новостям</a></strong>";
		
		return adaptModernNews(unserialize(file_get_contents('content/news/'.$name)));
	}
	
	function getSingleOldNews($name, $pageNum) {		
		echo "<strong><a href='index.php?pages=news&custom-news-date=all-old&page=$pageNum'>К новостям</a></strong>";
		echo "<script>document.addEventListener('DOMContentLoaded', function() { changeStyle(); }, false);</script>";
		
		return adaptOldNews(file_get_contents('content/news/'.$name));
	}
	
	function adaptOldNews($newsToAdapt) {
		if(!mb_detect_encoding($newsToAdapt, "UTF-8", true)) {
			$newsToAdapt = mb_convert_encoding($newsToAdapt, "UTF-8", "windows-1251");
		}
		// ошибки в коде старых новостей
		$pattern = [
			'materials',
			'Фонд Жить и Помнить',
			'Новости фонда ЖИТЬ И ПОМНИТЬ',
			'<img src="../images/m1.gif" width="100%" height="28" border="0" />',
			'<img src="../images/m2.gif" width="100%" height="21" border="0" />',
			'style=padding-top: 10""',
			'style=padding-top:10""',
			'style=padding-top:10"',
			'style=padding-top: 10"',
			'style=padding-top: 10 ""',
			'"padding-left: 25; padding-right: 25; padding-top: 0; padding-bottom: 25""'
		];
		$replacement = array_fill(0, 11, '');
		$replacement[0] = 'content/news/materials';
		$adaptedNews = str_replace($pattern, $replacement, $newsToAdapt);
		$adaptedNews = strip_tags($adaptedNews, '<h1><h2><h3><p><strong><a><img><ol><ul><li>');

		return $adaptedNews;
	}
	
	function getAllOldNews($page) {
		$allNews = file_get_contents("content/news/archive_news.html");
		$allNews = strip_tags($allNews, '<p><strong><a>');
		createOldNewsList($allNews, $page);
	}
	
	function replaceTemplateTags($string, $replace) {
		return str_replace(array_keys($replace), $replace, $string);
	}
	
	function getPubls($page) {
		$link = connectToPostgres();
		$rPubls = [];
		
		if($link) {
			$res = pg_query($link, "SELECT publs_id, publs_header FROM publs") or die('Query error: '. pg_last_error());
			
			while($row = pg_fetch_assoc($res)) {
				$rPubls[] = $row;
			}
			
			for($i=0; $i<count($rPubls); $i++) {
				$rPubls[$i] = createExceptPubl($rPubls[$i], false, true);
			}
		}
		
		$dir = "content/publ/";
		$publArr = scandir($dir);
		
		foreach($publArr as $publName) {
			$publPath = $dir.$publName;
			
			if(file_exists($publPath) && is_file($publPath)) {
				if(substr($publPath, -3, 3) == 'txt') {	// новые статьи
					$publ = file_get_contents($publPath);
					$publ = createExceptPubl($publ, false, false);
					$rPubls[] = $publ;
				}
			}
		}
		
		$oldPubls = getOldPubls();
		$rPubls = $rPubls + $oldPubls;
		createPublsList($rPubls, $page);
	}
	
	function createPublsList($publs, $page) {
		$totalPubls = count($publs);
		
		if($totalPubls < 10) {
			echo implode($publs);
			return;
		}

		$list = getULlist($totalPubls, 10, 'index.php?pages=publ&page=', $page);
		echo $list;
		$publsTemp = [];
		
		for($i=$page*10; $i>($page*10-10); $i--) {
			if(isset($publs[$i-1])) {
				array_unshift($publsTemp, $publs[$i-1]);
			}
		}
		
		echo implode($publsTemp);
		echo $list;
	}
	
	function getOldPubls() {
		$publsList = file('content/publ/publik.html');
		$oldNewsArr = [];
		
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
			$oldPubl = createExceptPubl($oldPubl, true, false);
			$oldNewsArr[] = $oldPubl;
		}
		
		return $oldNewsArr;
	}
	
	function getSinglePubl($name) {
		$link = connectToPostgres();
		if(substr($name, -3, 3) == 'txt') {
			$publArr = unserialize(file_get_contents($name));
			for($i=0; $i<3; $i++) {
				if($i == 2) $publ = $publArr[$i];
			}
		}
		else if(substr($name, -4, 4) == 'html'){
			$publ = file_get_contents($name);
			
			if(!mb_detect_encoding($publ, "UTF-8", true)) {
				$publ = mb_convert_encoding($publ, "UTF-8", "windows-1251");
			}
			
			// ошибки в большом кол-ве файлов
			$pattern = [
				'materials',
				'ПУБЛИКАЦИИ',
				'<img src="../images/m1.gif" width="100%" height="28" border="0" />',
				'<img src="../images/m2.gif" width="100%" height="21" border="0" />',
				'<IMG SRC="../images/m2.gif" ALIGN=BOTTOM WIDTH=100% HEIGHT=21 BORDER=0>',
				'style=padding-top: 10""',
				'style=padding-top:10""',
				'style=padding-top:10"',
				'style=padding-top: 10"',
				'style=padding-top: 10 ""',
				'style="padding-top: 10""'
			];
			$replacemetnt = array_fill(0, 11, '');
			$replacemetnt[0] = "content/publ/materials";
			$publ = str_replace($pattern, $replacemetnt, $publ);
			$publ = preg_replace("/Фонд Жить и Помнить/", "", $publ, 1);
			$publ = strip_tags($publ, '<h1><h2><h3><p><strong><a><img><ol><ul><li>');
		}
		else if($link) {
			$res = pg_query($link, "SELECT publs_header, publs_text FROM publs WHERE publs_id = $name") or die("Query error: ". pg_last_error());
			$row = pg_fetch_assoc($res);
			$publ = '<h3>'.$row['publs_header'].'</h3>'.$row['publs_text'];
		}
		else {	// скорее всего файл
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
	
	function createExceptPubl($publ, $isOld, $isDb) {
		$publTemplate = file_get_contents('content/templates/publ_template.php');
		
		if($isOld) {
			$publTemplate = str_replace("publUrl", $publ['link'], $publTemplate);
			$publTemplate = str_replace("publHeader", $publ['text'], $publTemplate);
		}
		else if($isDb) {
			$publTemplate = str_replace(['publUrl', 'publHeader'], [$publ['publs_id'], $publ['publs_header']], $publTemplate);
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
			
			if(!mb_detect_encoding($press, 'UTF-8', true)) {
				$press = mb_convert_encoding($press, "UTF-8", 'windows-1251');
			}
			
			// ошибки в большом кол-ве файлов
			$pattern = [
				'materials',
				'<img src="../../images/m1.gif" width="100%" height="28" border="0" />',
				'log.jpg',
				'<img src="../../images/" width="687" height="153" />',
				'<img src="../../images/m2.gif" width="100%" height="21" border="0" />',
				'style=padding-top: 10""',
				'style=padding-top:10""',
				'style=padding-top:10"',
				'style=padding-top: 10"',
				'style=padding-top: 10 ""',
				'bgcolor="#FFFFFF""'
			];
			$replacement = array_fill(0, 11, '');
			$replacement[0] = "content/press/$name/materials";
			$press = str_replace($pattern, $replacement, $press);
			$press = preg_replace("/Фонд Жить и Помнить/", '', $press, 1);
			$press = strip_tags($press, '<h1><h2><h3><p><strong><a><img><ul><ol><li>');	
			$pressArr[] = $press;
		}
		
		return $pressArr;
	}
	
	function getPressPage($pressArr, $pageNum) {
		return $pressArr[$pageNum];
	}
	
	function createPressList($press, $page) {
		$pressArr = explode(PHP_EOL, $press);
		$totalPress = count($pressArr);
		
		if($totalPress < 10) {
			echo $pressArr;
			return;
		}
		
		$list = getULlist($totalPress, 10, 'index.php?pages=press&page=', $page);
		
		echo $list;
		$pressTemp = [];
		
		for($i = $page * 10; $i>($page*10-10); $i--) {
			if(isset($pressArr[$i-1])) {
				array_unshift($pressTemp, $pressArr[$i-1]);
			}
		}
		
		echo implode($pressTemp);
		echo $list;
	}
	
	// PostgreSQL functions
	function connectToPostgres() {
		$link = pg_connect("host=192.168.0.4 port=5432 dbname=new.zip user=zip.admin password=123");
		return $link;
	}
?>