<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Форма регистрации пользователей</title>
		<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit"></script>
		<script type="text/javascript">
			turnstile.ready(function () {
			  turnstile.render("#captcha", {
				sitekey: "0x4AAAAAAA6rf0BJHFcFavfv"
			  });
			});
		</script>
	</head>
    <body>
        <div id="background-div"></div>
		<h2>Форма регистрации пользователей:</h2>
        <form action="save_user.php" method="post">
			<p>Логин: <input type="text" name="user-login" required></p>
			<p>Пароль: <input type="password" name="user-password" required></p>
			<p>Email: <input type="email" name="user-email" required></input></p>
			<p>Код-безопасности:<div class="cf-turnstile" id="captcha"></div></p>
			<p><input type="submit" value="Добавить"></p>
		</form>
    </body>
</html>