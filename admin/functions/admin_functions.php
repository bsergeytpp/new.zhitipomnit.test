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
	
	function updateSettings($data) {
		global $db, $dbLink;
		
		if(!isset($data)) {
			$data = [
				'NEWS_MAXCOUNT' => 5,
				'OLDNEWS_MAXCOUNT' => 10,
				'PUBLS_MAXCOUNT' => 5,
				'PRESS_MAXCOUNT' => 10,
				'LOGS_MAXCOUNT' => 50,
			];
		}
		
		if(!$db || !$dbLink) return false;
		
		$query = "UPDATE settings SET settings_data = ? WHERE settings_name = ?";
		$result = $db->executeQuery($query, array(serialize($data), 'materials_count'), 'update_settings');
		
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

		$logsCountQuery = 'SELECT COUNT(log_id) FROM logs ';
		$query = 'SELECT * FROM logs ';
		
		// если определена категория логов
		if($pars['type'] > 0) {
			$query .= "WHERE log_type = '".$pars['type']."' ";
			$logsCountQuery .= "WHERE log_type = '".$pars['type']."' ";
			
			if($pars['importance']) {
				$query .= 'AND log_important = TRUE ';
				$logsCountQuery .= 'AND log_important = TRUE ';
			}
		}
		// только важные
		else if($pars['importance']) {
			$query .= 'WHERE log_important = TRUE ';
			$logsCountQuery .= 'WHERE log_important = TRUE ';
		}
		
		$query .= 'ORDER BY log_id';
		$totalRes = $db->executeQuery($logsCountQuery, null, null);
		
		if($totalRes) $totalLogs = $totalRes->fetchColumn();
		
		// делаем список и ограничиваем кол-во логов на странице
		if($totalLogs > LOGS_MAXCOUNT) {
			$logsList = getULlist($totalLogs, LOGS_MAXCOUNT, 'logs.php?page=', $logPage);
			echo "Всего: $totalLogs";
			echo $logsList;
			$query .= ' LIMIT '.LOGS_MAXCOUNT;
			
			if($logPage > 1) {
				$query .= ' OFFSET '.(LOGS_MAXCOUNT * ($logPage-1));
			}
		}
		
		$res = $db->executeQuery($query, null, null);
		$logsArr = [
			0 => 'log_id',
			1 => 'log_type_category',
			2 => 'log_name',
			3 => 'log_text',
			4 => 'log_date',
			5 => 'log_important',
			6 => 'log_location'
		];
		
		while($row = $res->fetch(PDO::FETCH_NUM)) {
			echo '<tr>';
			for($i=0, $len=count($logsArr); $i<$len; $i++) {
				echo '<td name='.$logsArr[$i].'>' . $row[$i] . '</td>';
			}
			echo '</tr>';
		}
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
			3 => 'news_text'
		];
		
		while($row = $res->fetch(PDO::FETCH_NUM)) {
			echo '<tr>';
			for($i=0, $len=count($newsArr); $i<$len; $i++) {
				echo '<td name='.$newsArr[$i].'>' . $row[$i] . '</td>';
			}
			echo '</tr>';
			echo '<tr>';
			echo '<td class="edit-btn" colspan="3"><strong>Редактировать</strong></td>';
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
			2 => 'publs_text',
		];
		
		while($row = $res->fetch(PDO::FETCH_NUM)) {
			echo '<tr>';
			for($i=0, $len=count($publsArr); $i<$len; $i++) {
				echo '<td name='.$publsArr[$i].'>' . $row[$i] . '</td>';
			}
			echo '</tr>';
			echo '<tr>';
			echo '<td class="edit-btn" colspan="2"><strong>Редактировать</strong></td>';
			echo '<td class="delete-btn"><strong>Удалить</strong></td>';
			echo '</tr>';
		}
	}
?>