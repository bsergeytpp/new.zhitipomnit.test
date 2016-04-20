                <? require 'classes/Press.class.php' ?>
				<h2>Газета</h2>
				<div id="press-container">
					<?
						$customPress = isset($_GET['custom-press']) ? $_GET['custom-press'] : '';
						$pageNum = isset($_GET['page']) ? $_GET['page'] : '1';
						$pressClass = new PressClass($customPress, $pageNum);
						
						if(!$customPress) {
							$pressClass->getPress();
							echo "<script>document.addEventListener('DOMContentLoaded', function() { replacePressLinks(); }, false);</script>";
						}
						else {
							if(!$pressClass->getPressName()) {
								$pressClass->setPressName($customPress);
							}
							
							$pressClass->setPressPage($pageNum);
							$pressClass->getSinglePress($pageNum);
						}
					?>
				</div>
                <div class="clear-div"></div>