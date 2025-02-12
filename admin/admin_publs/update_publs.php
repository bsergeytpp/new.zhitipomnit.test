<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../../sessions/session.inc.php");
	
	global $db;
	
	if(!$_SESSION['admin']) {
		echo "<div class='error-message'>Вы не админ</div>";
		exit;
	}
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($db->getLink()) {
			if(isset($_POST['publ-header']) &&
				isset($_POST['publ-text']) && 			
				isset($_POST['publ-id'])) {
				$publId = clearStr($_POST['publ-id']);
				$publHeader = strip_tags(clearStr($_POST['publ-header']));
				$publText = clearStr($_POST['publ-text']);
				$query = "UPDATE publs SET publs_text = COALESCE(?, publs_text),
										   publs_header = COALESCE(?, publs_header) WHERE publs_id = ?";
			    $result = $db->executeQuery($query, array("$publText", "$publHeader" "$publId"), 'update_publs_query');
				
				// данные для логирования
				global $logData;
				$logData['type'] = 3;
				$logData['location'] = 'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
				$logData['date'] = date('Y-m-d H:i:sO');
				$logData['important'] = $_SESSION['admin'] || false;
				$logData['ip'] = getUserIp();
				
				if($result === false) {
					echo "<div class='error-message'>Публикация не была обновлена</div>";
					$logData['log_name'] = 'failed to update a publ';
					$logData['log_text'] = 'user '.$_SESSION['user'].' has failed to update a publ: '.$publId;
				}
				else {
					echo "<div class='success-message'>Публикация была обновлена</div>";
					$logData['log_name'] = 'updated a publ';
					$logData['log_text'] = 'user '.$_SESSION['user'].' has updated a publ: '.$publId;
				}

				echo addLogs($logData);
			}
			echo "<div class='error-message'>Нет данных для обновления.</div>";
		}
		else {
			echo "<div class='error-message'>Нет соединения с БД.</div>";
		}
	}
	else echo "<div class='error-message'>Ничего не было обновлено...</div>";
?>