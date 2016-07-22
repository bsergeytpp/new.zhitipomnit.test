		<h2>Комментарии:</h2>
		<? getComments('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>
		<h2>Добавить комментарий:</h2>
		<? 
			if($userLogin === null) {
				echo 'Гостям не разрешено осталять комментарии. <a href="../users/login.php">Войдите</a> или <a href="../users/reg_users.php">зарегистрируйстесь</a>.';
			} 
			else {
		?>
				<form action="admin/save_comments.php" method="POST" class="comments-form">
					<p><span>Логин:</span> <input name="comments-login" readonly="readonly" required type="text" value="<? echo $userLogin; ?>"></input></p>
					<p><span>Email:</span> <input name="comments-email" readonly="readonly" required type="text" value="<? echo getUserEmail($userLogin); ?>"></input></p>
					<p><span>Текст:</span> <textarea class="comments-textarea" name="comments-text" size="50"></textarea></p>
					<p><input type="submit" class="comments-post-button" value="Добавить"></p>
				</form>
				<script>
					document.addEventListener('DOMContentLoaded', function(e) { 
						initTinyMCE('.comments-textarea', false, '100%');
					});
				</script>
		<? } ?>