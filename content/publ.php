                <? 
					require 'classes/Publs.class.php'; 
				?>
				<h2>Публикации</h2>
                <div id="publs-container">
					<div class="clear-div"></div>
					<?
						$customPubl = isset($_GET['custom-publ']) ? $_GET['custom-publ']: '';
						$pageNum = isset($_GET['page']) ? $_GET['page']: '1';
						global $link;
						$link = connectToPostgres();
						
						if($link) $publsClass = new DbPublsClass($pageNum);
						else $publsClass = new OtherPublsClass($pageNum);
						
						if(!$customPubl) {
							$publsClass->getPubls();
							echo "<h3 class='full-width'><a href='index.php?pages=publ&custom-publ=all-old'>Старые публикации</a></h3>";
						}
						else if($customPubl == 'all-old') {
							$publsClass = new OldPublsClass($pageNum);
							$publsClass->getPubls();
							echo "<h3 class='full-width'><a href='index.php?pages=publ'>Последние публикаци</a></h3>";
						}
						else {
							$publsClass = checkPublsExistence($pageNum, $customPubl);
							echo $publsClass->getSinglePubl($customPubl);
							include "admin/comments.php";
						}
					?>
                </div>
                <div class="clear-div"></div>