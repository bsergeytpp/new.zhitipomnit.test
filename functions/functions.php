<?
	require (__DIR__."/../classes/DB.class.php"); 
	//require_once (__DIR__."/../admin/admin_security/session.inc.php");
	
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
	
	// загружаем настройки
	$materialsCount = getSettings(null, 'materials_count');
	
	if($materialsCount) {
		$materialsCount = unserialize($materialsCount[0]);
	}
	
	$NEWS_MAXCOUNT = 5;
	$OLDNEWS_MAXCOUNT = 10;
	$PUBLS_MAXCOUNT = 5;
	$PRESS_MAXCOUNT = 10;
	
	if(!$materialsCount) {
		echo "<div class='warning-message'>Настройки не найдены в БД</div>";
	}
	else {
		$NEWS_MAXCOUNT = $materialsCount['NEWS_MAXCOUNT'];
		$OLDNEWS_MAXCOUNT = $materialsCount['OLDNEWS_MAXCOUNT'];
		$PUBLS_MAXCOUNT = $materialsCount['PUBLS_MAXCOUNT'];
		$PRESS_MAXCOUNT = $materialsCount['PRESS_MAXCOUNT'];
	}
	
	define('NEWS_MAXCOUNT', $NEWS_MAXCOUNT);	// новостей на странице
	define('OLDNEWS_MAXCOUNT', $OLDNEWS_MAXCOUNT);	// старых новостей на странице
	define('PUBLS_MAXCOUNT', $PUBLS_MAXCOUNT);	// статей на странице
	define('PRESS_MAXCOUNT', $PRESS_MAXCOUNT);	// гaзет на странице
	
	$link = false;
	$userLogin = null;
	$secret = null;
	$token = null;
	$debug = '';
	
	//if(session_status() !== PHP_SESSION_ACTIVE) session_start();
	
	function checkToken($str) {
		$temp = explode(':', $str);
		$salt = $temp[0];
		$temp = $salt . ':' . md5($salt . ':' . $_SESSION['secret']);
		
		if($temp === $_SESSION['token']) {
			return true;
		}
		
		return false;
	}
	
	function getSettings($id, $name) {
		global $db;
		global $dbLink;
		
		if(!$db || !$dbLink) return false;
		
		$query = "SELECT settings_data FROM settings WHERE ";
		
		if(isset($id)) {
			$query .= "settings_id = ?";
			$result = $db->executeQuery($query, array($id), 'select_settings_by_id');
		}
		else if(isset($name)) {
			$query .= "settings_name = ?";
			$result = $db->executeQuery($query, array($name), 'select_settings_by_name');
		}
		else return false;
		
		if($result === false) {
			echo "<div class='error-message'>Настройки не найдены</div>";
		}
		else {
			return $result->fetch();
		}
		
		return false;
	}
	
	function updateSessionDB() {
		global $db;
		
		if($db->getLink()) {
			$phpSessionId = clearStr(session_id());
			$user = $_SESSION['user'];
			$lastSeen = date('Y-m-d');
			
			$query = "SELECT session_id FROM sessions WHERE session_hash = ? LIMIT 1";
			$result = $db->executeQuery($query, array($phpSessionId), 'get_session_id');
			
			if($result === false) {
				echo "<div class='error-message'>Ошибка запроса</div>";
			}
			else {
				$dbSessionId = $result->fetch();
				if($dbSessionId) {
					$query = 'UPDATE sessions SET session_last_seen = ? WHERE session_id = ?';
					$result = $db->executeQuery($query, array($lastSeen, $dbSessionId[0]), 'update_session');
					
					if($result === false) {
						echo "<div class='error-message'>Не удалось добавить сессию</div>";
					}
					else {
						echo "<div class='success-message'>Сессия успешно добавлена</div>";
					}
				}
				else {
					$query = 'INSERT INTO sessions (session_hash, session_last_seen, session_username) VALUES (?, ?, ?)';
					$result = $db->executeQuery($query, array($phpSessionId, $lastSeen, $user), 'add_session');
					
					if($result === false) {
						echo "<div class='error-message'>Не удалось добавить сессию</div>";
					}
					else {
						echo "<div class='success-message'>Сессия успешно добавлена</div>";
					}
				}
			}
		}
		else echo "<div class='error-message'>Соединение не установлено</div>";
	}
	
	function deleteSessionDB() {
		global $db;
				
		$phpSessionId = clearStr(session_id());
		
		$query = "DELETE FROM sessions WHERE session_hash = ?";
		$result = $db->executeQuery($query, array($phpSessionId), 'delete_session');
		
		if($result === false) {
			echo "<div class='error-message'>Ошибка запроса</div>";
		}
		else {
			echo "<div class='success-message'>Сессия удалена</div>";
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
					  VALUES (?, ?, ?, ?, ?, ?)";
			$result = $db->executeQuery($query, array($type, $name, $text, $location, $date, $important), 'add_log');
			
			if($result !== false) {
				echo "<div class='success-message'>Лог добавлен. Данные: ".$type.'|'.$name.'|'.$text.'|'.$location.'|'.$date.'|'.$important."</div>";
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
		- получает ID пользователя
	*/
	function getUserData($userLogin) {
		if(!$userLogin) return;
		
		global $db;
		$query = "SELECT user_login, user_email, user_group FROM users WHERE user_login LIKE ?";
		$result = $db->executeQuery($query, array($userLogin), 'get_user_info');
		
		while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			echo '<tr>';
			foreach($row as $val) {
				echo '<td>' . $val . '</td>';
			}
			echo '</tr>';
		}
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
				
				if(isset($_SESSION['user'])) {
					if($_SESSION['user']!== null) {
						echo "<tr class='comments-respond'><td colspan='5'><a class='respond-button' href='#'>Ответить</a></td></tr>";
					}
				}
				
				echo "</table>";
				echo "</div>";
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
			$query = "SELECT user_email FROM users WHERE user_login = ?";
			$result = $db->executeQuery($query, array($userLogin), 'get_user_mail');
			
			if($result === false) echo "<div class='error-message'>Такого пользователя нет</div>";
			else {
				$row = $result->fetch();
				return $row[0];	// TODO: не проверялось
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
?>