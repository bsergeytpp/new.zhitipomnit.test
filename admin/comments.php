	<?
		if(session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}

		// проверяем, есть ли новость
		if($link) {
			if(isset($_GET['custom-news-date'])) {
				$date = $_GET['custom-news-date'];
				$query = "SELECT * FROM news WHERE news_date = '" . $date . "'";
				$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
			
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
			echo "Соединение с базой данных неустановлено.";
			exit;
		}
	?>