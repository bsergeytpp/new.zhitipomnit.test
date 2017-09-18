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
			];
		}
		
		if(!$db || !$dbLink) return false;
		
		$query = "UPDATE settings SET settings_data = ? WHERE settings_name = ?";
		$result = $db->executeQuery($query, array(serialize($data), 'materials_count'), 'select_settings_by_name');
		
		if($result === false) {
			echo "<div class='error-message'>Настройки не сохранены</div>";
		}
		else {
			echo "<div class='error-message'>Настройки сохранены</div>";
		}
	}
?>