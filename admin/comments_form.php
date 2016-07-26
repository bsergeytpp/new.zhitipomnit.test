		<?
			session_start();
			require_once(__DIR__.'/../functions/functions.php');
		?>
		<h2>Комментарии:</h2>
		<? 
			if(isset($_GET['location'])) {
				getComments($_GET['location']); 
			}
			else getComments('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); 
		?>
		<h2>Добавить комментарий:</h2>
		<? 
			global $userLogin;
			if(isset($_SESSION['user'])) {
				$userLogin = $_SESSION['user'];
			}
			if($userLogin === null) {
				echo 'Гостям не разрешено осталять комментарии. <a href="../users/login.php">Войдите</a> или <a href="../users/reg_users.php">зарегистрируйстесь</a>.';
			} 
			else {
		?>
				<form action="admin/save_comments.php" method="POST" class="comments-form">
					<input name="comments-parent" type="hidden" value=""></input>
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
		