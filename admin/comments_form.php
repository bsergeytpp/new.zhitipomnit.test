		<div class="comments-form-div">
			<h2>Добавить комментарий:</h2>
			<? 
				global $userLogin;
				$id = $_GET['id'];
				if(isset($_SESSION['user'])) {
					$userLogin = $_SESSION['user'];
				}
				if($userLogin === null) {
					echo '<div class="warning-message">Гостям не разрешено осталять комментарии. <a href="../users/login.php">Войдите</a> или <a href="../users/reg_users.php">зарегистрируйстесь</a>.</div>';
				} 
				else {
			?>
					<form onsubmit="return false;" method="POST" class="comments-form">
						<input name="comments-parent" type="hidden" value=""></input>
						<input name="comments-location-id" type="hidden" value="<? echo $id; ?>"></input>
						<input name="security-token" type="hidden" value="<? echo $_SESSION['token']; ?>"></input>
						<p><span>Логин:</span> <input name="comments-login" readonly="readonly" required type="text" value="<? echo $userLogin; ?>"></input></p>
						<p><span>Email:</span> <input name="comments-email" readonly="readonly" required type="text" value="<? echo getUserEmail($userLogin); ?>"></input></p>
						<p><span>Текст:</span> <textarea class="comments-textarea" name="comments-text" size="50"></textarea></p>
						<p><input type="submit" class="comments-post-button" value="Добавить"></input></p>
					</form>
			<? } ?>
		</div>
		<script>
			var timer;
			clearTimeout(timer);
			document.addEventListener('DOMContentLoaded', function(e) { 
				appendScript('scripts/tinymce/tinymce.min.js');
				timer = setTimeout(function() {
					initTinyMCE('.comments-textarea', false, '100%');
				}, 1000);
			});
		</script>