	<?
		if(session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		// проверяем, есть ли новость
		if($db->getLink()) {
			if(isset($_GET['id'])) {
				$id = $_GET['id'];
				$query = "SELECT COUNT(*) FROM news WHERE news_id = ?";
				$result = $db->executeQuery($query, array($id), 'check_news');
			
				if($result === false) echo 'Ошибка в запросе';
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
			echo "Соединение с базой данных не установлено.";
			exit;
		}
	?>