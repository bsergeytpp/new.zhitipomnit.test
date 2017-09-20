	<?
		require_once "admin_security/session.inc.php";
		
		if(session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		// проверяем, есть ли новость
		if($db->getLink()) {
			if(isset($_GET['id'])) {
				$id = $_GET['id'];
				$query = "SELECT COUNT(*) FROM news WHERE news_id = ?";
				$result = $db->executeQuery($query, array($id), 'check_news');
			
				if($result === false) echo "<div class='error-message'>Ошибка в запросе</div>";
				else if($result->fetchColumn() > 0) {
	?>
					<div class='comments-wrapper'>
						<? 
							// есть комментарии
							if(checkComments($id) > 0) {
								include "comments_list.php";
							}							
							include "comments_form.php";
						?>
					</div>
	<?  		} 
			}
		}
		else {
			echo "<div class='error-message'>Соединение с базой данных не установлено.</div>";
			exit;
		}
	?>