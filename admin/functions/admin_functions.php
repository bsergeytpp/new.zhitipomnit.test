<?
	require_once (__DIR__."/../../functions/functions.php");
		
	/*
		Функция поиска ID пользователя по логину
		- принимает логин
		- возвращает ID
	*/
	function getUserId($login) {
		global $db, $dbLink;
		
		if(!$dbLink) return false;
		
		$query = "SELECT user_id FROM users WHERE user_login = ? LIMIT 1";
		$result = $db->executeQuery($query, array($login), 'select_user_id');
		
		if($result === false) {
			echo "<div class='error-message'>Пользователь не найден</div>";
		}
		else {
			$row = $result->fetch();
			return $row[0];	// TODO: не проверялось
		} 	
	}
	
	function updateSettings($data, $userLogin, $settingsName) {
		global $db;
		global $dbLink;
		
		if(!$db || !$dbLink) return false;
		
		if(!$userLogin) return false;
		
		$currentSettings = getSettings();
		$currentSettings = unserialize($currentSettings[0]);
		
		switch($settingsName) {
			case 'site_settings':
				if(!$data) {
					$data = [
						'NEWS_MAXCOUNT' => NEWS_MAXCOUNT,
						'OLDNEWS_MAXCOUNT' => OLDNEWS_MAXCOUNT,
						'PUBLS_MAXCOUNT' => PUBLS_MAXCOUNT,
						'PRESS_MAXCOUNT' => PRESS_MAXCOUNT,
						'LOGS_MAXCOUNT' => LOGS_MAXCOUNT,
					];
				}
				$newSettings['site_settings'] = $data;
				break;
			case 'user_settings':
				if(!$data) {
					$data = [
						'news_style' => 'classic'
					];
				}
				$newSettings['user_settings'] = $data;
				break;
		}
		
		$query = "UPDATE settings SET settings_data = ? WHERE settings_user_login = ?";
		$result = $db->executeQuery($query, array(serialize($data), $userLogin), 'update_settings');
		
		if($result === false) {
			echo "<div class='error-message'>Настройки не сохранены</div>";
		}
		else {
			echo "<div class='error-message'>Настройки сохранены</div>";
		}
	}
		
	// записываем логи в таблицу
	function getLogsToTable($pars) {
		global $db, $logPage;

		$query = 'SELECT * FROM logs ';
		
		// если определена категория логов
		if($pars['type'] > 0) {
			$query .= "WHERE log_type = '".$pars['type']."' ";
			
			if($pars['importance']) {
				$query .= 'AND log_important = TRUE ';
			}
		}
		// только важные
		else if($pars['importance']) {
			$query .= 'WHERE log_important = TRUE ';
		}
		
		$query .= 'ORDER BY log_id';
		$totalLogs = getLogsCount($pars);
		
		// делаем список и ограничиваем кол-во логов на странице
		if($totalLogs > LOGS_MAXCOUNT) {
			$logsQuery = 'logs.php?log-type='.$pars["type"].'&log-important='.$pars["importance"].'&';
			$logsList = getULlist($totalLogs, LOGS_MAXCOUNT, $logsQuery.'page=', $logPage);
			echo "Всего: $totalLogs ";
			echo $logsList;
			$query .= ' LIMIT '.LOGS_MAXCOUNT;
			
			if($logPage > 1) {
				$query .= ' OFFSET '.(LOGS_MAXCOUNT * ($logPage-1));
			}
		}
		
		$res = $db->executeQuery($query, null, null);
		$logsArr = [
			0 => 'log_id',
			1 => 'log_name',
			2 => 'log_text',
			3 => 'log_date',
			4 => 'log_important',
			5 => 'log_location',
			6 => 'log_type_category',
			7 => 'log_client_ip'
		];
		
		while($row = $res->fetch(PDO::FETCH_NUM)) {
			echo '<tr>';
			for($i=0, $len=count($logsArr); $i<$len; $i++) {
				if($logsArr[$i] === 'log_important') {
					$impValue = $row[$i] ? 'true' : 'false';
					echo '<td name='.$logsArr[$i].'>' . $impValue . '</td>';
					continue;
				}
				
				echo '<td name='.$logsArr[$i].'>' . $row[$i] . '</td>';
			}
			echo '</tr>';
		}
	}
	
	// получаем кол-во логов
	function getLogsCount($pars) {
		global $db;

		$logsCountQuery = 'SELECT COUNT(log_id) FROM logs ';
		
		// если определена категория логов
		if($pars['type'] > 0) {
			$logsCountQuery .= "WHERE log_type = '".$pars['type']."' ";
			
			if($pars['importance']) {
				$logsCountQuery .= 'AND log_important = TRUE ';
			}
		}
		// только важные
		else if($pars['importance']) {
			$logsCountQuery .= 'WHERE log_important = TRUE ';
		}
		
		$totalRes = $db->executeQuery($logsCountQuery, null, null);
		
		return $totalRes ? $totalRes->fetchColumn() : null;
	}
	
	function getNewsToTable() {
		global $db, $newsPage;
		
		$query = 'SELECT * FROM news ORDER BY news_id';
		$newsCountQuery = 'SELECT COUNT(news_id) FROM news';
		$totalRes = $db->executeQuery($newsCountQuery, null, null);
		
		if($totalRes) $totalNews = $totalRes->fetchColumn();
		
		if($totalNews > NEWS_MAXCOUNT) {
			$newsList = getULlist($totalNews, NEWS_MAXCOUNT, 'manage_news.php?page=', $newsPage);
			echo "Всего: $totalNews";
			echo $newsList;
			$query .= ' LIMIT '.NEWS_MAXCOUNT;
			
			if($newsPage > 1) {
				$query .= ' OFFSET '.(NEWS_MAXCOUNT * ($newsPage-1));
			}
		}
		
		$res = $db->executeQuery($query, null, null);
		
		$newsArr = [
			0 => 'news_id',
			1 => 'news_date',
			2 => 'news_header',
		];
		
		while($row = $res->fetch(PDO::FETCH_NUM)) {
			echo '<tr>';
			
			for($i=0, $len=count($newsArr); $i<$len; $i++) {
				echo '<td name='.$newsArr[$i].'>' . $row[$i] . '</td>';
			}
			
			echo '</tr>';
			echo '<tr>';
			echo '<td class="edit-btn" colspan="2"><strong>';
			echo '<a href="edit_single_news.php?news_id='.$row[0].'">Редактировать</a></strong></td>';
			echo '<td class="delete-btn" colspan="1"><strong>Удалить</strong></td>';
			echo '</tr>';
		}
	}
	
	function getPublsToTable() {
		global $db, $publsPage;
		
		$query = 'SELECT * FROM publs ORDER BY publs_id';
		$totalPublsQuery = 'SELECT COUNT(publs_id) FROM publs';
		$totalRes = $db->executeQuery($totalPublsQuery, null, null);
		
		if($totalRes) $totalPubls = $totalRes->fetchColumn();
		
		if($totalPubls > PUBLS_MAXCOUNT) {
			$newsList = getULlist($totalPubls, PUBLS_MAXCOUNT, 'manage_publs.php?page=', $publsPage);
			echo "Всего: $totalPubls";
			echo $newsList;
			$query .= ' LIMIT '.PUBLS_MAXCOUNT;
			
			if($publsPage > 1) {
				$query .= ' OFFSET '.(PUBLS_MAXCOUNT * ($publsPage-1));
			}
		}
		
		$res = $db->executeQuery($query, null, null);
		
		$publsArr = [
			0 => 'publs_id',
			1 => 'publs_header',
		];
		
		while($row = $res->fetch(PDO::FETCH_NUM)) {
			echo '<tr>';
			
			for($i=0, $len=count($publsArr); $i<$len; $i++) {
				echo '<td name='.$publsArr[$i].'>' . $row[$i] . '</td>';
			}
			
			echo '</tr>';
			echo '<tr>';
			echo '<td class="edit-btn"><strong>';
			echo '<a href="edit_single_publ.php?publId='.$row[0].'">Редактировать</a></strong></td>';
			echo '<td class="delete-btn"><strong>Удалить</strong></td>';
			echo '</tr>';
		}
	}
	
	function getUsersToTable() {
		global $db;
		
		$query = 'SELECT user_id, user_login, user_email, user_group, user_reg_date, user_last_seen, user_deleted FROM users ORDER BY user_id';
		$res = $db->executeQuery($query, null, null);
		
		$usersArr = [
			0 => 'user_id',
			1 => 'user_login',
			2 => 'user_email',
			3 => 'user_group',
			4 => 'user_reg_date',
			5 => 'user_last_seen',
			6 => 'user_deleted'
		];
		
		while($row = $res->fetch(PDO::FETCH_NUM)) {
			echo '<tr>';
			
			for($i=0, $len=count($usersArr); $i<$len; $i++) {
				echo '<td name='.$usersArr[$i].'>' . $row[$i] . '</td>';
			}
	
			echo '</tr>';
			echo '<tr>';
			echo '<td class="edit-btn" colspan="5" style="cursor: pointer;"><strong>';
			echo '<a href="edit_single_user.php?user_id='.$row[0].'">Редактировать</a></strong></td>';
			echo '<td class="delete-btn" colspan="2" style="cursor: pointer;"><strong>';
			echo '<a href="delete_user.php?user_id='.$row[0].'">Удалить</strong></td>';
			echo '</tr>';
		}
	}
?>