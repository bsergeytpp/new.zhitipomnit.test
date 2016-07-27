                <? 
					require 'classes/News.class.php'; 
				?>
				<h2>Новости</h2>
                <div id="news-container">
					<!--<div class="clear-div"></div>-->
					<?
						$customNewsDate = isset($_GET['custom-news-date']) ? $_GET['custom-news-date'] : '';
						$pageNum = isset($_GET['page']) ? $_GET['page'] : '1';
						global $link;
						$link = connectToPostgres();
					
						if($link) {
							$newsClass = new DbNewsClass($customNewsDate, $pageNum);
						}
						else {
							$newsClass = new OtherNewsClass($customNewsDate, $pageNum);
						}
						
						if(!$customNewsDate) {
							$newsClass->getNews();
							//echo "<script>document.addEventListener('DOMContentLoaded', function() { displayNewsImage(); }, false);</script>";
							echo "<h3 class='full-width'><a href='index.php?pages=news&custom-news-date=all-old'>Старые новости</a></h3>";
							if($link) pg_close($link);
						}
						else if($customNewsDate == 'all-old') {
							$newsClass = new OldNewsClass($customNewsDate, $pageNum);
							$newsClass->getNews();
							echo "<script>document.addEventListener('DOMContentLoaded', function() { replaceNewsLinks(); }, {passive: true});</script>";
							echo "<h3 class='full-width'><a href='index.php?pages=news&page=".$pageNum."'>Последние новости</a></h3>";
						}
						else {
							$newsClass = checkNewsExistence($customNewsDate, $pageNum);
							echo $newsClass->getSingleNews();
							include "admin/comments.php";
						}
					?>
                </div>
                <div class="clear-div"></div>
