                <? require 'classes/classes.php'; ?>
				<h2>Новости</h2>
                <div id="news-container">
					<!--<div class="clear-div"></div>-->
					<?
						$customNewsDate = isset($_GET['custom-news-date']) ? $_GET['custom-news-date'] : '';
						$pageNum = isset($_GET['page']) ? $_GET['page'] : '1';
						$newsClass = new NewsClass($customNewsDate, $pageNum);
						
						if(!$customNewsDate) {
							//getAllNews($pageNum);
							$newsClass->getAllNews();
							//echo "<script>document.addEventListener('DOMContentLoaded', function() { displayNewsImage(); }, false);</script>";
							echo "<h3 class='full-width'><a href='index.php?pages=news&custom-news-date=all-old'>Старые новости</a></h3>";
						}
						else if($customNewsDate == 'all-old') {
							//getAllOldNews($pageNum);
							$newsClass->getAllOldNews();
							echo "<script>document.addEventListener('DOMContentLoaded', function() { replaceNewsLinks(); }, false);</script>";
							echo "<h3 class='full-width'><a href='index.php?pages=news&page=$pageNum'>Последние новости</a></h3>";
						}
						else {
							//echo getSingleNews($customNewsDate, $pageNum);
							echo $newsClass->getSingleNews();
						}
					?>
                </div>
                <div class="clear-div"></div>
