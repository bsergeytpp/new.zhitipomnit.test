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
	
	function checkPublsExistence($page, $date) {
		if(substr($date, -4, 4) == 'html') {
			return new OldPublsClass($page);
		}
		if(substr($date, -3, 3) == 'txt') {
			return new OtherPublsClass($page);
		}
		if(file_exists($date)) {	// скорее всего файл
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-strem');
			header('Content-Disposition: attachment; filename='.basename($date));
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: '.filesize($date));
			ob_clean();
			flush();
			readfile($date);
			exit;
		}
		return new DbPublsClass($page);
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