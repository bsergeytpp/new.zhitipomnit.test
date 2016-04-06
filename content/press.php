                <h2>Газета</h2>
				<div id="press-container">
					<?
						$customPress = isset($_GET['custom-press']) ? $_GET['custom-press'] : '';
						if(!$customPress) {
							$press = file_get_contents('content/press/gazeta.html');
							echo $press;
							echo "<script>document.addEventListener('DOMContentLoaded', function() { replacePressLinks(); }, false);</script>";
						}
						else {
							echo "<strong><a href='index.php?pages=press'>Назад</a></strong><br>";
							$pressArr = getPressArray($customPress);
							for($i=0, $j=1; $i<count($pressArr); $i++, $j++) {
								echo "<a class='article-press-links' href='index.php?pages=press&custom-press=$customPress&page=$i'>Страница $j</a>";
							}
							echo "<div class='clear-div'></div>";
							$pageNum = isset($_GET['page']) ? $_GET['page'] : '0';
							switch($pageNum) {
								case 0: echo getPressPage($pressArr, 0); break;
								case 1: echo getPressPage($pressArr, 1); break;
								case 2: echo getPressPage($pressArr, 2); break;
								case 3: echo getPressPage($pressArr, 3); break;
								default: echo getPressPage($pressArr, 0); break;
							}
							
						}
					?>
				</div>
                <div class="clear-div"></div>