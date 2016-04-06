                <?
					$customNewsName = $_GET['custom-news-date'];
					if(!$customNewsName) {
						$customNewsName = "01-03-12";
						echo getSingleOldNews($customNewsName);
					}
					else if($customNewsName == 'oldNews')
						echo getAllOldNews();
					// одна новость
					else echo getSingleOldNews($customNewsName);
				?>
				<!--<script>document.addEventListener('DOMContentLoaded', function() { replaceLinks(); }, false);</script>-->