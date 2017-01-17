                <? 
					require 'classes/News.class.php'; 
				?>
				<h2>Новости</h2>
                <div id="news-container">
					<!--<div class="clear-div"></div>-->
					<?
						$customNewsDate = isset($_GET['custom-news-date']) ? $_GET['custom-news-date'] : '';
						$newsId = isset($_GET['id']) ? $_GET['id'] : '';
						$pageNum = isset($_GET['page']) ? $_GET['page'] : '1';
						$type = isset($_GET['type']) ? $_GET['type'] : 'db';
						global $link;
						$link = connectToPostgres();
					
						/*if($link) {
							$newsClass = new DbNewsClass($newsId, $customNewsDate, $pageNum, 'db');
						}
						else {
							$newsClass = new OtherNewsClass($customNewsDate, $pageNum, 'other');
						}*/
						
						if($type === 'db') {
							if($link) {
								$newsClass = new DbNewsClass($newsId, $customNewsDate, $pageNum, 'db');
								
								if($newsId !== '') {
									echo $newsClass->getSingleNews();
									include "admin/comments.php";
								}
								else {
									echo $newsClass->getNews();
									echo "<h3 class='full-width'><a href='index.php?pages=news&type=old'>Старые новости</a></h3>";
									if($link) pg_close($link);
								}
							}
						}
						else if($type === 'old') {
							$newsClass = new OldNewsClass($customNewsDate, $pageNum, 'old');
							
							if(!$customNewsDate) {
								echo $newsClass->getNews();
								echo "<script>document.addEventListener('DOMContentLoaded', function() { replaceNewsLinks(); }, {passive: true});</script>";
								echo "<h3 class='full-width'><a href='index.php?pages=news&page=".$pageNum."'>Последние новости</a></h3>";
							}
							else {
								echo $newsClass->getSingleNews();
							}
						}
						else if($type === 'other') {
							$newsClass = new OtherNewsClass($customNewsDate, $pageNum, 'other');
							
							if(!$customNewsDate) {
								echo $newsClass->getNews();
								echo "<h3 class='full-width'><a href='index.php?pages=news&page=".$pageNum."'>Последние новости</a></h3>";
							}
							else {
								echo $newsClass->getSingleNews();
							}
						}
					?>
                </div>
                <div class="clear-div"></div>
