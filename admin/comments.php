<?
	require_once "session.inc.php";
	require_once "secure.inc.php";
	require_once "../functions/functions.php";
	global $link;
	$link = connectToPostgres();
	$user = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($link) {
			if(isset($_POST['comments-text']) && isset($_POST['comments-login'])) {
				$text = clearStr($_POST['comments-text']);
				$login = clearStr($_POST['comments-login']);
				$location = clearStr($_SERVER['HTTP_REFERER']);
				
				$user_id = getUserId($login);
				
				$query = "INSERT INTO comments (comments_author, comments_location, comments_text) " .
						 "VALUES ('$user_id', '$location', '$text')";
				$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
				
				if($result === false) echo 'Комментарий не был добавлен';
				else echo 'Комментарий был добавлен';			
			}
			else echo "Нет данных для обновления.";
		}
		else {
			echo "Нет соединения с БД.";
		}
	}
	
	function getUserId($login) {
		global $link;
		
		if($link) {
			$query = "SELECT user_id " .
					 "FROM users " . 
					 "WHERE user_login = '" . $login ."'";
			$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
			
			if($result === false) echo 'Пользователь не был найдет';
			else {
				return pg_fetch_result($result, 0, 0);
			} 	
		}
	}
	
	function getComments($uri) {
		global $link;
		
		if($link) {
			$query = "SELECT comments_author, comments_text " .
					 "FROM comments " . 
					 "WHERE comments_location = '" . pg_escape_string($uri) . "'";
			$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
			
			if($result === false) echo 'Ошибка в выборке комментариев';
			else {
				while($row = pg_fetch_assoc($result)) {
					echo "<tr>";
					foreach($row as $val) {
						echo "<td>". $val ."</td>";
					}
					echo "</tr>";
				}
			}
		}
	}
	
	function getUserEmail($user) {
		global $link;
		
		if($link) {
			$query = "SELECT user_email " .
					 "FROM users " . 
					 "WHERE user_login = '". pg_escape_string($user) . "'";
			$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
			
			if($result === false) echo 'Такого пользователя нет';
			else return pg_fetch_result($result, 0, 0);
		}
	}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Комментарии</title>
		<script src="../scripts/tinymce/tinymce.min.js"></script>
		<script type="text/javascript">
		  tinymce.init({
			selector: '#comments-textarea',
			height: 300,
			width: 1000,
			language: 'ru_RU',
			plugins: 'code',
			paste_data_images: true
		  });
		</script>
    </head>
    <body>
        <div id="background-div"></div>
		<h2>Комментарии:</h2>
		<table border='1'> 
			<? getComments('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); ?>
		</table>
		<h2>Добавить комментарий:</h2>
        <form action="comments.php" method="POST">
			<p>Логин: <input name="comments-login" readonly="readonly" required type="text" value="<? echo $user; ?>"></input></p>
			<p>Email: <input name="comments-email" readonly="readonly" required type="text" value="<? echo getUserEmail($user); ?>"></input></p>
			<p>Текст: <textarea name="comments-text" size="50" required type="text"></textarea></p>
			<p><input type="submit" value="Добавить"></p>
		</form>
    </body>
</html>