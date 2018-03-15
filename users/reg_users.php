<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Форма регистрации пользователей</title>
		<script type="text/javascript">
		  var onloadCallback = function() {
			grecaptcha.render('recaptcha', {
			  'sitekey' : '6LcUgUwUAAAAAN0l_K6I6teyN9YxQdCfwQYKE_ZH'
			});
		  };
		</script>
	</head>
    <body>
        <div id="background-div"></div>
		<h2>Форма регистрации пользователей:</h2>
        <form action="save_user.php" method="post">
			<p>Логин: <input type="text" name="user-login" required></p>
			<p>Пароль: <input type="password" name="user-password" required></p>
			<p>Email: <input type="email" name="user-email" required></input></p>
			<p>Код-безопасности: <div name="recaptcha" id="recaptcha" required></div></p> 
			<p><input type="submit" value="Добавить"></p>
		</form>
		<script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit" async defer></script>
    </body>
</html>