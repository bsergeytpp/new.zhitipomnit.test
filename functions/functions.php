<?
	define('DB_CONNECT', 'host=192.168.0.4 port=5432 dbname=new.zip user=zip.admin password=123');
	define('NEWS_MAXCOUNT', '3');	// новостей на странице
	define('OLDNEWS_MAXCOUNT', '10');	// старых новостей на странице
	define('PUBLS_MAXCOUNT', '10');	// статей на странице
	define('PRESS_MAXCOUNT', '10');	// гaзет на странице
	
	$link = false;

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
	
	function reverseDate($date) {
		return intval(substr($date, -2, 2) . substr($date, -5, 2) . substr($date, 0, 2));
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
			
			// сортируем статьи по ID
			$rPubls = array_reverse($rPubls);
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
		
		if($totalPubls < PUBLS_MAXCOUNT) {
			echo implode($publs);
			return;
		}

		$list = getULlist($totalPubls, PUBLS_MAXCOUNT, 'index.php?pages=publ&page=', $page);
		echo $list;
		echo implode(getSampleOfArray($page, PUBLS_MAXCOUNT, $publs));
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
		
		if($totalPress < PRESS_MAXCOUNT) {
			echo $pressArr;
			return;
		}
		
		$list = getULlist($totalPress, PRESS_MAXCOUNT, 'index.php?pages=press&page=', $page);
		
		echo $list;
		echo implode(getSampleOfArray($page, PRESS_MAXCOUNT, $pressArr));
		echo $list;
	}
	
	function getSampleOfArray($pNum, $max, $arr) {
		$tempArr = [];
		
		for($i = $pNum * $max; $i>($pNum*$max-$max); $i--) {
			if(isset($arr[$i-1])) {
				array_unshift($tempArr, $arr[$i-1]);
			}
		}
		
		return $tempArr;
	}
	
	function checkNewsExistence($date, $page) {
		if(file_exists('content/news/'.convertDate($date).'.html')) {	// формат гггг-мм-дд
			return new OldNewsClass(convertDate($date), $page);
		}
		if(file_exists('content/news/'.$date.'.html')) {	// формат дд-мм-гггг
			return new OldNewsClass($date, $page);
		}
		if(file_exists('content/news/'.$date.'.txt')) {
			return new OtherNewsClass($date, $page);
		}
		return new DbNewsClass($date, $page);
	}
	
	function convertDate($date) {
		$dateArr = explode('-', $date);

		return date('d-m-y', strtotime(implode('-', array_reverse($dateArr))));
	}
	
	// PostgreSQL functions
	function connectToPostgres() {
		global $link;
		$link = pg_connect(DB_CONNECT);
		return $link;
	}
?>