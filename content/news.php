                <? 
					require 'classes/News.class.php'; 
				?>
				
				<div class="news-style" title="Отображение новостей">
						<h2>Новости</h2>
						<img src="images/news_classic.png" alt="classic" id="news-classic" title="Классическое" width="28">
						<img src="images/news_alt.png" alt="alt" id="news-alt" title="Альтернативное" width="28">
				</div>
                <div id="news-container">
					<!--<div class="clear-div"></div>-->
					<?
						$customNewsDate = isset($_GET['custom-news-date']) ? $_GET['custom-news-date'] : '';
						$newsId = isset($_GET['id']) ? $_GET['id'] : '';
						$pageNum = isset($_GET['page']) ? $_GET['page'] : '1';
						$type = isset($_GET['type']) ? $_GET['type'] : 'db';
						$style = isset($_COOKIE['newsStyle']) ? $_COOKIE['newsStyle'] : 'classic';
						
						if($type === 'db') {
							global $dbLink; global $db;
							if($dbLink && $db) {
								$newsClass = new DbNewsClass($newsId, $customNewsDate, $pageNum, 'db', $style);
								
								if($newsId !== '') {
									echo $newsClass->getSingleNews();
									include "comments/comments.php";
								}
								else {
									echo $newsClass->getNews();
									echo "<h3 class='full-width'><a href='index.php?pages=news&type=old'>Старые новости</a></h3>";
								}
							}
							else {
								echo "<div class='error-message'>Нет подключения к базе данных</div>";
							}
						}
						else if($type === 'old') {
							$newsClass = new OldNewsClass($customNewsDate, $pageNum, 'old', null);
							
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
							$newsClass = new OtherNewsClass($customNewsDate, $pageNum, 'other', null);
							
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
