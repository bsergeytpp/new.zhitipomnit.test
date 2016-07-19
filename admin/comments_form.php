		<h2>Комментарии:</h2>
		<table border='1'> 
			<? getComments('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>
		</table>
		<h2>Добавить комментарий:</h2>
        <form action="admin/save_comments.php" method="POST">
			<p>Логин: <input name="comments-login" readonly="readonly" required type="text" value="<? echo $userLogin; ?>"></input></p>
			<p>Email: <input name="comments-email" readonly="readonly" required type="text" value="<? echo getUserEmail($userLogin); ?>"></input></p>
			<p>Текст: <textarea class="comments-textarea" name="comments-text" size="50"></textarea></p>
			<p><input type="submit" value="Добавить"></p>
		</form>
		<script>
			document.addEventListener('DOMContentLoaded', function(e) { 
				initTinyMCE('.comments-textarea', false);
			});
		</script>