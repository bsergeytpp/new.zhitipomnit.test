<?
	require_once (__DIR__."/../../functions/functions.php");
		
	/*
		Функция поиска ID пользователя по логину
		- принимает логин
		- возвращает ID
	*/
	function getUserId($login) {
		global $db;
		
		if($db->getLink()) {
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
	}
	
	function updateSettings($data) {
		global $db;
		global $dbLink;
		
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
	
	// всего логов
	function getLogsCount($queryString) {
		global $db;
		
		if($queryString) {
			$res = $db->executeQuery($queryString, null, null);
			
			if($res) return $res->fetchColumn();
		}
		
		return false;
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
		
		$totalLogs = getLogsCount($logsCountQuery);
		$query .= 'ORDER BY log_id';
		
		// делаем список и ограничиваем кол-во логов на странице
		if($totalLogs > LOGS_MAXCOUNT) {
			$logsList = getULlist($totalLogs, LOGS_MAXCOUNT, 'logs.php?page=', $logPage);
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
		
		while($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$i = 0;
			echo '<tr>';
			foreach($row as $val) {
				switch($i) {
					case 0: case 1: case 2: case 3: case 4: case 5: case 6: 
						echo '<td name='.$logsArr[$i].'>' . $val . '</td>'; break;
					default: break;
				}
				$i++;
			}
			echo '</tr>';
		}
	}
?>