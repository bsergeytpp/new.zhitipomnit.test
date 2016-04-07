                <h2>Новости</h2>
                <div id="news-container">
					<!--<div class="clear-div"></div>-->
					<?
						$customNewsDate = isset($_GET['custom-news-date']) ? $_GET['custom-news-date'] : '';
						$pageNum = isset($_GET['page']) ? $_GET['page'] : '1';
						file_put_contents('content/templates/smth.php', '');
						if(!$customNewsDate) {
							getAllNews($pageNum);
							//echo "<script>document.addEventListener('DOMContentLoaded', function() { displayNewsImage(); }, false);</script>";
							echo "<h3 class='full-width'><a href='index.php?pages=news&custom-news-date=all-old'>Старые новости</a></h3>";
						}
						else if($customNewsDate == 'all-old') {
							getAllOldNews($pageNum);
							echo "<script>document.addEventListener('DOMContentLoaded', function() { replaceNewsLinks(); }, false);</script>";
							echo "<h3 class='full-width'><a href='index.php?pages=news&page=$pageNum'>Последние новости</a></h3>";
						}
						else {
							echo getSingleNews($customNewsDate, $pageNum);
						}
						
					//	$link = connectToPostgres();
					//	$query = 'SELECT * FROM News';
					//	$res = pg_query($link, $query) or die('Query error: '. pg_last_error());
					//	pg_close($link);
						//$row = pg_fetch_all($res, PGSQL_ASSOC);
					//	while($row = pg_fetch_assoc($res))
					//		print_r($row);
					?>
                </div>
                <div class="clear-div"></div>
