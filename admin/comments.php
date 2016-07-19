<?
	require_once "admin_security/session.inc.php";
	require_once "admin_security/secure.inc.php";
	require_once "functions/admin_functions.php";
	global $link;
	$link = connectToPostgres();
	$userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
	
	include "save_comments.php";
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
			<p>Логин: <input name="comments-login" readonly="readonly" required type="text" value="<? echo $userLogin; ?>"></input></p>
			<p>Email: <input name="comments-email" readonly="readonly" required type="text" value="<? echo getUserEmail($userLogin); ?>"></input></p>
			<p>Текст: <textarea name="comments-text" size="50" required type="text"></textarea></p>
			<p><input type="submit" value="Добавить"></p>
		</form>
    </body>
</html>