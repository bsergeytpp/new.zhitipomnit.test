                <h2>Публикации</h2>
				<strong><a href="index.php?pages=publ">Назад</a></strong>
                <div id="publs-container">
					<div class="clear-div"></div>
					<?
						$customPubl = isset($_GET['custom-publ']) ? $_GET['custom-publ']: '';
						if(!$customPubl) {
							$publs = getPubls();
							foreach($publs as $cur) {
								echo $cur;
							}
						}
						else echo getSinglePubl($customPubl);
					?>
                </div>
                <div class="clear-div"></div>