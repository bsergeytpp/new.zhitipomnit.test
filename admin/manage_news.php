<?
	require_once "../functions/functions.php";
	require_once "session.inc.php";
	require_once "secure.inc.php";

	function getNewsToTable() {
		global $link;
		$link = connectToPostgres();
		
		$query = 'SELECT * FROM news';
		$res = pg_query($link, $query) or die('Query error: '. pg_last_error());
		
		while($row = pg_fetch_assoc($res)) {
			echo '<tr>';
			foreach($row as $val) {
				echo '<td>' . $val . '</td>';
			}
			echo '</tr>';
			echo '<tr>';
			echo '<td class="edit-btn" colspan="3" style="cursor: pointer;"><strong>Редактировать</strong></td>';
			echo '<td class="delete-btn" colspan="1" style="cursor: pointer;"><strong>Удалить</strong></td>';
			echo '</tr>';
		}
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Управление новостями</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../scripts/tinymce/tinymce.min.js"></script>
</head>
<body>
	<h1>Управление новостями</h1>
	<h3>Доступные действия:</h3>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Date</th>
			<th>Header</th>
			<th>Text</th>
		</tr>
		<? getNewsToTable(); ?>
	</table> 
	<script>
		var editBtns = document.getElementsByClassName("edit-btn");
		
		for(var i=0; i<editBtns.length; i++) {
			editBtns[i].addEventListener('click', function(e) {
				var target = e.target;
				var parent = this.parentNode;
				var prevNode = parent.previousSibling;
				var textArea = prevNode.lastChild;

				if(this.innerHTML.indexOf('Редактировать') != -1) {
					textArea.className = 'news-textarea';
					initTinyMCE();
					this.innerHTML = '<strong>Сохранить</strong>';
				}
				else if(this.innerHTML.indexOf('Сохранить') != -1) {
					var updatedText = tinymce.activeEditor.getContent();
					var newsId = prevNode.firstChild.innerHTML;
					console.log(newsId);
					var updatedNews = "id=" + encodeURIComponent(newsId) + "&" + 
									  "text=" + encodeURIComponent(updatedText);
					var request = new XMLHttpRequest();
					request.open('POST', 'update_news.php', false);
					request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
					request.setRequestHeader("Content-Length", updatedNews.length);
					request.send(updatedNews);
					document.location.reload(true);
				}
			}, false);
		}
		
		function initTinyMCE() {
			tinymce.init({
				inline: true,
				selector: '.news-textarea',
				language: 'ru_RU',
				plugins: 'code',
				paste_data_images: true
			});
		}
	</script>
</body>
</html>