<?
	define('DB_CONNECT', 'host=192.168.0.4 port=5432 dbname=new.zip user=zip.admin password=123');
	define('NEWS_MAXCOUNT', '3');	// новостей на странице
	define('OLDNEWS_MAXCOUNT', '10');	// старых новостей на странице
	define('PUBLS_MAXCOUNT', '10');	// статей на странице
	define('PRESS_MAXCOUNT', '10');	// гaзет на странице
	$NEWS_MAXCOUNT = 3;
	$OLDNEWS_MAXCOUNT = 10;
	$PUBLS_MAXCOUNT = 10;
	$PRESS_MAXCOUNT = 10;
	
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
		$list = '<ul class="news-list"><li> « ';
		$totalPages = ceil($totalElems/$elemsPerPage);
		
		for($j = 1; $j <= $totalPages; $j++) {
			if($j == $pageNum) {
				$list .= " <li>" . $j . " ";
				continue;
			}
			$list .= " <li><a href='$href$j'>" . $j . "</a> ";
		}
		
		$list .= '<li> » </ul><br>';
		
		return $list;
	}
	
	function reverseDate($date) {
		return intval(substr($date, -2, 2) . substr($date, -5, 2) . substr($date, 0, 2));
	}
	
	function replaceTemplateTags($string, $replace) {
		return str_replace(array_keys($replace), $replace, $string);
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
	
	/*
		Функция вывода информации о пользователе
	*/
	function getUserData() {
		global $link;
		$link = connectToPostgres();
		$user = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
		
		$query = "SELECT user_login, user_email, user_group FROM users WHERE user_login LIKE '".$user."'";
		$res = pg_query($link, $query) or die('Query error: '. pg_last_error());
		
		while($row = pg_fetch_assoc($res)) {
			echo '<tr>';
			foreach($row as $val) {
				echo '<td>' . $val . '</td>';
			}
			echo '</tr>';
		}
	}
	
	// PostgreSQL functions
	function connectToPostgres() {
		global $link;
		
		if(!function_exists('pg_connect')) 
			return false;
		
		$link = pg_connect(DB_CONNECT);
		return $link;
	}
?>