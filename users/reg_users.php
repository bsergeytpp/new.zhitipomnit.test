<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Форма регистрации пользователей</title>
    </head>
    <body>
        <div id="background-div"></div>
		<h2>Форма регистрации пользователей:</h2>
        <form action="save_user.php" method="post">
			<p>Логин: <input type="text" name="user-login" required></p>
			<p>Пароль: <input type="password" name="user-password" required></p>
			<p>Email: <input type="email" name="user-email" required></input></p>
			<p>Группа: <input name="user-group" type="text"></input></p>
			<p><input type="submit" value="Добавить"></p>
		</form>
    </body>
</html>