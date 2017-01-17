	<?
		if(session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		// проверяем, есть ли новость
		if($link) {
			if(isset($_GET['id'])) {
				$id = $_GET['id'];
				$query = "SELECT * FROM news WHERE news_id = $1";
				$result = pg_prepare($link, "check_news", $query);
				$result = pg_execute($link, "check_news", array($id)) 
						  or die('Query error: '. pg_last_error());
			
				if($result === false) echo 'Ошибка в запросе';
				else if(pg_num_rows($result) > 0) {
	?>
					<div class='comments-wrapper'>
						<? 
							include "comments_list.php"; 
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