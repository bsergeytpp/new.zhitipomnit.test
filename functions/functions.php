<?
	require (__DIR__."/../classes/DB.class.php"); 
	
	define('NEWS_MAXCOUNT', '5');	// новостей на странице
	define('OLDNEWS_MAXCOUNT', '10');	// старых новостей на странице
	define('PUBLS_MAXCOUNT', '5');	// статей на странице
	define('PRESS_MAXCOUNT', '10');	// гaзет на странице
	$NEWS_MAXCOUNT = 3;
	$OLDNEWS_MAXCOUNT = 10;
	$PUBLS_MAXCOUNT = 10;
	$PRESS_MAXCOUNT = 10;
	
	$link = false;
	$userLogin = null;
	$secret = null;
	$token = null;
	$debug = '';
	
	if(session_status() !== PHP_SESSION_ACTIVE) session_start();
	
	// подключаемся к базе данных
	$config = parse_ini_file(__DIR__.'/../config.ini');
	$connectStr = "host=".$config['host'].
				  " port=".$config['port'].
				  " dbname=".$config['dbname'].
				  " user=".$config['user'].
				  " password=".$config['password'];
	$db = DBClass::getInstance();
	$db->connectToDB($connectStr);
	$dbLink = $db->getLink();
	
	function checkToken($str) {
		$temp = explode(':', $str);
		$salt = $temp[0];
		$temp = $salt . ':' . md5($salt . ':' . $_SESSION['secret']);
		
		if($temp === $_SESSION['token']) {
			return true;
		}
		
		return false;
	}
	
	function updateSessionDB() {
		global $db;
		
		if($db->getLink()) {
			$sessionId = clearStr(session_id());
			$user = $_SESSION['user'];
			$lastSeen = date('Y-m-d');
			
			$query = "SELECT session_id FROM sessions WHERE session_hash = $1";
			$result = $db->executeQuery($query, array($sessionId), 'get_session_id');
			
			if($result === false) {
				echo 'Ошибка запроса';
			}
			else {
				$dbSessionId = pg_fetch_row($result);
				if($dbSessionId) {
					$query = 'UPDATE sessions SET session_last_seen = $1 WHERE session_id = $2';
					$result = $db->executeQuery($query, array($lastSeen, $dbSessionId[0]), 'update_session');
					
					if($result === false) {
						echo 'Не удалось добавить сессию';
					}
					else {
						echo 'Сессия успешно добавлена';
					}
				}
				else {
					$query = 'INSERT INTO sessions (session_hash, session_last_seen, "session_user") VALUES ($1, $2, $3)';
					$result = $db->executeQuery($query, array($sessionId, $lastSeen, $user), 'add_session');
					
					if($result === false) {
						echo 'Не удалось добавить сессию';
					}
					else {
						echo 'Сессия успешно добавлена';
					}
				}
			}
		}
		else echo "Соединение не установлено";
	}
	
	function deleteSessionDB() {
		global $db;
				
		$sessionId = clearStr(session_id());
		
		$query = "DELETE FROM sessions WHERE session_hash = $1";
		$result = $db->executeQuery($query, array($sessionId), 'delete_session');
		
		if($result === false) {
			echo 'Ошибка запроса';
		}
		else {
			echo 'Сессия удалена';
		}
	}
	
	function addLogs($type, $name, $text, $location, $date, $important) {
		global $db;
		
		if($name === null || $text === null || $location === null || 
		   $date === null || $important === null || $type === null) {
			return false;
		}
		
		$name = clearStr($name);
		$text = filter_var($text, FILTER_SANITIZE_STRING);
		$location = clearStr($location);
		
		if(!$important) {
			$important = 'false';
		}
		
		if($db->getLink()) {
			$query = "INSERT INTO logs (log_type, log_name, log_text, log_location, log_date, log_important) 
					  VALUES ($1, $2, $3, $4, $5, $6)";
			$result = $db->executeQuery($query, array($type, $name, $text, $location, $date, $important), 'add_log');
			
			if($result !== false) {
				echo 'Лог добавлен';
			}
			else {
				echo 'Лог не добавлен';
			}
		}
		else {
			echo 'Соединение с базой данных не установлено';
		}
	}
	
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
		
		for($i=$pNum*$max, $len=$pNum*$max-$max; $i>$len; $i--) {
			if(isset($arr[$i-1])) {
				array_unshift($tempArr, $arr[$i-1]);
			}
		}
		
		return $tempArr;
	}
	
	function checkNewsExistence($date, $page, $id) {
		if(file_exists('content/news/'.convertDate($date).'.html')) {	// формат гггг-мм-дд
			return new OldNewsClass(convertDate($date), $page);
		}
		if(file_exists('content/news/'.$date.'.html')) {				// формат дд-мм-гггг
			return new OldNewsClass($date, $page);
		}
		if(file_exists('content/news/'.$date.'.txt')) {
			return new OtherNewsClass($date, $page);
		}
		return new DbNewsClass($id, $date, $page);
	}
	
	function checkPublsExistence($page, $date) {	
		if(substr($date, -4, 4) == 'html') {
			return new OldPublsClass($page);
		}
		if(substr($date, -3, 3) == 'txt') {
			return new OtherPublsClass($page);
		}
		if(file_exists($date)) {										// скорее всего файл
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
		- получает ID пользователя
	*/
	function getUserData($userLogin) {
		if(!$userLogin) return;
		
		global $db;
		$query = "SELECT user_login, user_email, user_group FROM users WHERE user_login LIKE $1";
		$result = $db->executeQuery($query, array($userLogin), 'get_user_info');
		
		while($row = pg_fetch_assoc($res)) {
			echo '<tr>';
			foreach($row as $val) {
				echo '<td>' . $val . '</td>';
			}
			echo '</tr>';
		}
	}
	
	
	/*
		Функция вывода всех комментариев для страницы
		- принимает ID материала
	*/
	function getComments($id) {
		global $db;
		
		if($db->getLink()) {
			$query = "SELECT comments.comments_id, comments.comments_parent_id, users.user_login, comments.comments_text, comments.comments_date 
					  FROM comments, users 
					  WHERE comments_location_id = $1 
					  AND comments.comments_author = users.user_id ORDER BY comments.comments_id";
					
			$result = $db->executeQuery($query, array($id), 'get_all_comments');

			if($result === false) echo 'Ошибка в выборке комментариев';
			else {
				if(pg_num_rows($result) === 0) {
					echo 'Комментариев пока нет.';
				}
				
				while($row = pg_fetch_assoc($result)) {
					echo "<div class='comments-div'>";
					
					if($row['comments_parent_id'] !== null) {
						echo "<table class='comments-table respond'>"; 
					}
					else echo "<table class='comments-table'>"; 
					
					echo "<tr>
							<th class='row-id'>ID</th>
							<th class='row-parent'>Родитель</th>
							<th class='row-login'>Логин</th>
							<th class='row-text'>Сообщение</th>
							<th class='row-date'>Дата</th>
						 </tr>";
					echo "<tr class='comments-content'>";
					$i = 0;
					
					foreach($row as $val) {
						//if($val === $row['parent']) continue;
						
						//if($val === $row['comments_id']) continue;
						switch($i) {
							case 0: echo "<td class='comment-id'>". $val ."</td>"; break;
							case 3: echo "<td class='comment-text'>". $val ."</td>"; break;
							default: echo "<td>". $val ."</td>"; break;

						}
						$i++;
					}
					echo "</tr>";
					echo "<tr class='comments-respond'><td colspan='5'><a class='respond-button' href='#'>Ответить</a></td></tr>";
					echo "</table>";
					echo "</div>";
				}
			}
		}
	}
	
	/*
		Функция получения электронного адреса пользователя по логину
		- принимает логин
		- возвращает email
		- TODO: пока не используется
	*/
	function getUserEmail($userLogin) {
		global $db;
		
		if($db->getLink()) {
			$query = "SELECT user_email FROM users WHERE user_login = $1";
			$result = $db->executeQuery($query, array($userLogin), 'get_user_mail');
			
			if($result === false) echo 'Такого пользователя нет';
			else return pg_fetch_result($result, 0, 0);
		}
	}	
	
	/*
		Функция подключения частей сайта
		- принимает путь к контету
		- path может быть и массивом строк
		- можно упростить вывод и обойтись без буффера
	*/
	function includeContent($path) {
		$type = gettype($path);

		switch($type) {
			case 'array': 
				$len = count($path);
				for($i=0; $i<$len; $i++) {
					ob_start();
					include($path[$i]);
					$buffer = ob_get_contents();
					ob_end_clean();
					echo $buffer;
				}
				break;
			case 'string': 
				ob_start();
				include($path);
				$buffer = ob_get_contents();
				ob_end_clean();
				echo $buffer;
				break;
			case 'default': break;
		}
	}
	
	// PostgreSQL functions
	function connectToPostgres() {
		global $link;
		
		if(!function_exists('pg_connect')) 
			return false;

		$config = parse_ini_file(__DIR__.'/../config.ini');
		$connectStr = "host=".$config['host'].
					  " port=".$config['port'].
					  " dbname=".$config['dbname'].
					  " user=".$config['user'].
					  " password=".$config['password'];
		
		$link = pg_connect($connectStr);
		return $link;
	}
	
	function executeQuery($query, $params, $prepName) {
		global $link;
		$result = null;
		
		if(!$link) $link = connectToPostgres();
		
		if($params) {
			$result = pg_prepare($link, $prepName, $query) or die('Error: '. pg_last_error());
			$result = pg_execute($link, $prepName, $params) or die('Error: '. pg_last_error());
		}
		else {
			$result = pg_query($link, $query) or die('Error: '. pg_last_error());
		}
		
		return $result;
	}
?>