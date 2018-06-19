                <? 
					require (__DIR__.'/../classes/Guestbook.class.php'); 
				?>
				
				<?
					global $dbLink; global $db;
					
					if($dbLink && $db) {
						$guestookClass = new GuestbookClass();
					}
					else {
						echo "<div class='error-message'>Нет подключения к базе данных</div>";
					}
					
					if($_SERVER['REQUEST_METHOD'] == 'POST') {
						if($dbLink) {
							if(isset($_POST['guestbook-author']) &&
								isset($_POST['guestbook-email']) &&
								isset($_POST['guestbook-text'])) {
								$gbAuthor = $_POST['guestbook-author'];
								$gbEmail = $_POST['guestbook-email'];
								$gbText = $_POST['guestbook-text'];
								$gbDate = date('Y-m-d H:i:sO');
								
								$guestookClass->addMessage($gbDate, $gbAuthor, $gbText, $gbEmail);
							}
						}
					}
				?>
				
                <div id="guestbook-container">
				<?
					if(isset($guestookClass)) {
						echo $guestookClass->getMessages();
					}
					else {
						echo "<div class='error-message'>Не создан класс гостевой книги</div>";
						exit;
					}
				?>
					<form onsubmit="return false;" method="POST" class="guestbook-form">
						<p><span>Имя:</span> <input name="guestbook-author" required type="text"></input></p>
						<p><span>Email:</span> <input name="guestbook-email" required type="text"></input></p>
						<p><span>Текст:</span> <textarea class="guestbook-textarea" name="guestbook-text" size="50"></textarea></p>
						<p><input type="submit" class="guestbook-post-button" value="Добавить"></input></p>
					</form>
                </div>
                <div class="clear-div"></div>
				<script>
					var timer;
					clearTimeout(timer);
					document.addEventListener('DOMContentLoaded', function(e) { 
						appendScript('scripts/tinymce/tinymce.min.js');
						timer = setTimeout(function() {
							initTinyMCE('.guestbook-textarea', false, '100%');
						}, 1000);
					});
				</script>