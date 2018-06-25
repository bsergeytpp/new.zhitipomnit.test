<?
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if($db->getLink()) {
			if(isset($_GET['publId'])) {
				$publId = $_GET['publId'];
				$query = "SELECT * FROM publs WHERE publs_id = ? ORDER BY publs_id";
				$result = $db->executeQuery($query, array($publId), 'get_single_publs_query');
				$publsArr = $result->fetch(PDO::FETCH_ASSOC);
			}
			else {
				$query = "SELECT * FROM publs WHERE publs_id = ? ORDER BY publs_id";
				$result = $db->executeQuery($query, array('1'), 'get_single_publs_query');
				$publsArr = $result->fetch(PDO::FETCH_ASSOC);
			}
		}
	}	
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Форма редактирования публикаций</title>
		<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
		<script src="../../scripts/tinymce/tinymce.min.js"></script>
		<script type="text/javascript">
		  tinymce.init({
			selector: '#publ-textarea',
			height: 300,
			width: 800,
			language: 'ru',
			plugins: 'code',
			paste_data_images: true
		  });
		</script>
    </head>
    <body>
        <a href="/admin">Назад к админке</a><br>
		<h2>Форма редактирования публикаций:</h2>
        <form action="update_publs.php" method="post">
			<p>Заголовок: 
				<input type="text" name="publ-header" size="100" required value="<? echo $publsArr['publs_header']; ?>">
			</p>
			<p>Текст: <textarea id="publ-textarea" name="publ-text" size="50">
				<? echo $publsArr['publs_text']; ?>
			</textarea></p>
			<input name="publ-id" type="hidden" value="<? echo $publId; ?>"></input>
			<p><input type="submit" value="Изменить"></p>
		</form>
    </body>
</html>