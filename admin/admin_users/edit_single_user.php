<?
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if($db->getLink()) {
			if(isset($_GET['user_id'])) {
				$userId = $_GET['user_id'];
				$query = "SELECT * FROM users WHERE user_id = ? ORDER BY user_id";
				$result = $db->executeQuery($query, array($userId), 'get_single_user_query');
				$userArr = $result->fetch(PDO::FETCH_ASSOC);
			}
			else {
				$query = "SELECT * FROM users WHERE user_id = ? ORDER BY user_id";
				$result = $db->executeQuery($query, array('1'), 'get_single_user_query');
				$userArr = $result->fetch(PDO::FETCH_ASSOC);
			}
		}
	}	
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Форма редактирования пользователя</title>
		<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
    </head>
    <body>
        <a href="/admin">Назад к админке</a><br>
		<h2>Форма редактирования пользователя:</h2>
        <form action="update_users.php" method="post" enctype="multipart/form-data">
			<p>Логин: <input type="text" name="user-login" size="100" required value="<? echo $userArr['user_login']; ?>"></p>
			<p>Email: <input type="text" name="user-email" size="100" required value="<? echo $userArr['user_email']; ?>"></p>
			<p>Группа: <input type="text" name="user-group" size="100" required value="<? echo $userArr['user_group']; ?>"></p>
			<p>Дата регистрации: <input type="datetime" name="user-reg" size="100" required value="<? echo $userArr['user_reg_date']; ?>"></p>
			<p>Последний вход: <input type="datetime" name="user-last" size="100" required value="<? echo $userArr['user_last_seen']; ?>"></p>
			<input name="user-id" type="hidden" value="<? echo $userId; ?>"></input>
			<p><input type="submit" value="Изменить"></p>
		</form>
    </body>
</html>