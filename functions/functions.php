<?
	require_once (__DIR__."/../classes/DB.class.php");
	require_once (__DIR__."/../classes/Sessions.class.php");	
	
	// подключаемся к базе данных
	$config = parse_ini_file(__DIR__.'/../config.ini');
	$connectStr = "host=".$config['host'].
				  " port=".$config['port'].
				  " dbname=".$config['dbname'].
				  " user=".$config['user'].
				  " password=".$config['password'];
	$db = DBClass::getInstance();
	$db->connectToDB($config, 'PGSQL');
	$dbLink = $db->getLink();
	$defaultSettings = [
		'site_settings' => [
			'NEWS_MAXCOUNT' => 5,
			'OLDNEWS_MAXCOUNT' => 10,
			'PUBLS_MAXCOUNT' => 5,
			'PRESS_MAXCOUNT' => 10,
			'LOGS_MAXCOUNT' => 50
		],
		'user_settings' => [
			'news_style' => 'classic'
		]
	];
	$sessionHandler = DBSessionHandler::getInstance();
	session_set_save_handler($sessionHandler, true);
	session_start();
	
	// загружаем настройки
	$userSettings = getSettings();
	$materialsCount = null;
	$siteSettings = null;

	if($userSettings) {
		$userSettings = unserialize($userSettings[0]);
		$materialsCount = $userSettings['site_settings'];
		$siteSettings = $userSettings['user_settings'];
	}
	else if(isset($_SESSION['user'])) {
		saveDefaultSettings(serialize($defaultSettings));
	}
	
	if(!$siteSettings) {
		$_COOKIE["newsStyle"] = 'alt';
	}
	
	$NEWS_MAXCOUNT = $defaultSettings['site_settings']['NEWS_MAXCOUNT'];
	$OLDNEWS_MAXCOUNT = $defaultSettings['site_settings']['OLDNEWS_MAXCOUNT'];
	$PUBLS_MAXCOUNT = $defaultSettings['site_settings']['PUBLS_MAXCOUNT'];
	$PRESS_MAXCOUNT = $defaultSettings['site_settings']['PRESS_MAXCOUNT'];
	$LOGS_MAXCOUNT = $defaultSettings['site_settings']['LOGS_MAXCOUNT'];
	
	if($materialsCount) {
		$NEWS_MAXCOUNT = $materialsCount['NEWS_MAXCOUNT'];
		$OLDNEWS_MAXCOUNT = $materialsCount['OLDNEWS_MAXCOUNT'];
		$PUBLS_MAXCOUNT = $materialsCount['PUBLS_MAXCOUNT'];
		$PRESS_MAXCOUNT = $materialsCount['PRESS_MAXCOUNT'];
		$LOGS_MAXCOUNT = $materialsCount['LOGS_MAXCOUNT'];
	}
	
	define('NEWS_MAXCOUNT', $NEWS_MAXCOUNT);	// новостей на странице
	define('OLDNEWS_MAXCOUNT', $OLDNEWS_MAXCOUNT);	// старых новостей на странице
	define('PUBLS_MAXCOUNT', $PUBLS_MAXCOUNT);	// статей на странице
	define('PRESS_MAXCOUNT', $PRESS_MAXCOUNT);	// гaзет на странице
	define('LOGS_MAXCOUNT', $LOGS_MAXCOUNT);	// логов на странице
	define('NEWS_STYLE', 'alt');	// логов на странице
	
	$link = false;
	$userLogin = null;
	$secret = null;
	$token = null;
	$debug = '';
	$logData = array(
		'type' => null, 
		'location' => null, 
		'date' => null, 
		'important' => null,
		'ip' => null,
		'name' => null, 
		'text' => null
	);
	
	//if(session_status() !== PHP_SESSION_ACTIVE) session_start();
	
	function checkToken($str) {
		$temp = explode(':', $str);
		$salt = $temp[0];
		$temp = $salt . ':' . md5($salt . ':' . $_SESSION['secret']);
		
		return ($temp === $_SESSION['token']) ? true : false;
	}
	
	//https://gist.github.com/jonathanstark/dfb30bdfb522318fc819
	function verifyCaptcha($userData) {
		global $config;
		
		$post_data = http_build_query(
			array(
				'secret' => $config['secret'],
				'response' => $userData,
				'remoteip' => $_SERVER['REMOTE_ADDR']
			)
		);
		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $post_data
			)
		);
		$context  = stream_context_create($opts);
		$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
		return $response;
	}
	
	function getSessionId() {
		global $db;
		global $dbLink;
		global $sessionHandler;
		
		if(!$db || !$dbLink) return false;
		
		$sessionHash = $sessionHandler->getSessionHash();
		
		if(!$sessionHash) return;
		
		$query = "SELECT session_id FROM sessions WHERE session_hash = ?";
		$result = $db->executeQuery($query, array($sessionHash), 'get_session_id');
		$sessionId = $result->fetch();
		
		return $sessionId ? $sessionId[0] : null;
	}
	
	function getSettings() {
		global $db;
		global $dbLink;
		$userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
		
		if(!$db || !$dbLink || !$userLogin) return false;
		
		$query = "SELECT settings_data FROM settings WHERE settings_user_login = ?";
		$result = $db->executeQuery($query, array($userLogin), 'select_settings_by_user_login');

		return $result ? $result->fetch() : false;
	}
	
	function saveDefaultSettings($data) {
		global $db;
		global $dbLink;
		$userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
		
		if(!$db || !$dbLink) return false;
		
		if(!$userLogin) return false;
		
		$query = "INSERT INTO settings (settings_user_login, settings_data) VALUES (?, ?)";
		$result = $db->executeQuery($query, array($userLogin, $data), 'save_settings');
	}
	
	function addLogs($logData) {
		global $db;

		foreach($logData as $value) {
			if($value === null) return false;
		}
		
		unset($value);
		
		$logData['name'] = clearStr($logData['name']);
		$logData['text'] = filter_var($logData['text'], FILTER_SANITIZE_STRING);
		$logData['location'] = clearStr($logData['location']);
		
		if(!$logData['important']) {
			$logData['important'] = 'false';
		}
		
		if(!$logData['ip']) {
			$logData['ip'] = 'undefined';
		}
		
		if($db->getLink()) {
			$query = "INSERT INTO logs (log_type, log_location, log_date, log_important, log_client_ip, log_name, log_text) 
					  VALUES (?, ?, ?, ?, ?, ?, ?)";
			$result = $db->executeQuery($query, array_values($logData), 'add_log');
			
			if($result !== false) {
				echo "<div class='success-message'>Лог добавлен. Данные: ".implode('|', $logData)."</div>";
			}
			else {
				echo "<div class='error-message'>Лог не добавлен</div>";
			}
		}
		else {
			echo "<div class='error-message'>Соединение с базой данных не установлено</div>";
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
		$list = '<ul class="elems-list"><li> « ';
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
		if(file_exists('content/news/'.$date.'.html')) {				// формат дд-мм-гг
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
		- получает логин пользователя
		- возвращает массив из данных пользователя
	*/
	function getUserData($userLogin) {
		if(!$userLogin) return;
		
		global $db;
		
		$query = "SELECT user_login, user_email, user_group, user_reg_date, user_last_seen FROM users WHERE user_login LIKE ?";
		$result = $db->executeQuery($query, array($userLogin), 'get_user_info');
				
		return ($result) ? $result->fetch(PDO::FETCH_ASSOC) : false;
	}
	
	/*
		Функция обновления информации о последнем входе пользователя
		- получает логин пользователя
	*/
	function updateUserLastSeen($userLogin) {
		if(!$userLogin) return;
		
		global $db;
		$currentTime = date('Y-m-d H:i:sO');
		$query = "UPDATE users SET user_last_seen = ? WHERE user_login LIKE ?";
		$result = $db->executeQuery($query, array($currentTime, $userLogin), 'update_user_last_seen');
				
		return $result ? true : false;
	}
	
	/*
		Функция проверки наличия комментариев к материалу
		- принимает ID материала (поле comment_location_id)
		- возвращает количество комментариев или false
	*/
	function checkComments($locationId) {
		global $db;
		
		if($db->getLink()) {
			$query = "SELECT COUNT(comments.comments_id)
					  FROM comments, users 
					  WHERE comments_location_id = ?";
					
			$result = $db->executeQuery($query, array($locationId), 'check_comments');
			
			return $result->fetchColumn();
		}
		else {
			echo "<div class='error-message'>Нет подключения к базе данных</div>";
		}
		
		return false;
	}
	
	/*
		Функция получения всех комментариев для страницы
		- принимает ID материала
	*/
	function getComments($id) {
		global $db;
		
		if($db->getLink()) {
			$commentsNum = checkComments($id);

			if($commentsNum === false) echo "<div class='error-message'>Ошибка в выборке комментариев</div>";
			else if($commentsNum > 0) {
				$query = "SELECT
					comments.comments_id, 
					comments.comments_parent_id, 
					users.user_login, 
					comments.comments_text, 
					comments.comments_date,
					(SELECT user_login FROM users WHERE user_id = comments_edited_by) as com_edited_by,
					comments.comments_edited_date
				  FROM comments, users 
				  WHERE comments_location_id = ? 
				  AND comments.comments_author = users.user_id 
				  ORDER BY comments.comments_id";
				
				$result = $db->executeQuery($query, array($id), 'get_all_comments');
				printComments($result);
			}
		}
	}
	
	/*
		Функция вывода всех комментариев для страницы
		- принимает результат запроса к БД
	*/
	function printComments($result) {
		if(!$result) {
			return false;
		}
		else {
			$j = 1;
			while($row = $result->fetch(PDO::FETCH_ASSOC)) {
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
					switch($i) {
						case 0: echo "<td class='comment-id'>". $val ."</td>"; break;
						case 3: 
							echo "<td class='comment-text' id='text-id-$j'>". $val;
							
							if($row['com_edited_by'] !== null) {
								echo "<br><em class='edited'>Отредактировано: ".$row['com_edited_by'];
								echo " | ".$row['comments_edited_date']."</em>";
							}
							
							echo "</td>"; 
							break;
						case 5: case 6: break;
						default: echo "<td>". $val ."</td>"; break;
					}
					$i++; $j++;
				}
				echo "</tr>";
				
				if(isset($_SESSION['user']) && $_SESSION['user'] !== null) {
					echo "<tr class='comments-respond'><td colspan='5'><a class='respond-button' href='#'>Ответить</a></td></tr>";
				}
				
				echo "</table>";
				echo "</div>";
			}
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
	
	function getUserIp() {
		if(isset($_SERVER['REMOTE_ADDR'])) {
			return $_SERVER['REMOTE_ADDR'];
		}
		
		return false;
	}
?>