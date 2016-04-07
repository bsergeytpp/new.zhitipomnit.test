                <h2>Публикации</h2>
				<strong><a href="index.php?pages=publ">Назад</a></strong>
                <div id="publs-container">
					<div class="clear-div"></div>
					<?
						$customPubl = isset($_GET['custom-publ']) ? $_GET['custom-publ']: '';
						$pageNum = isset($_GET['page']) ? $_GET['page']: '1';
						if(!$customPubl) {
							getPubls($pageNum);
							/*foreach($publs as $cur) {
								echo $cur;
							}*/
						}
						else echo getSinglePubl($customPubl);
					?>
                </div>
                <div class="clear-div"></div>