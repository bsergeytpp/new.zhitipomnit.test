<?
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if($db->getLink()) {
			if(isset($_GET['news_id'])) {
				$newsId = $_GET['news_id'];
				$query = "SELECT * FROM news WHERE news_id = ? ORDER BY news_id";
				$result = $db->executeQuery($query, array($newsId), 'get_single_news_query');
				$newsArr = $result->fetch(PDO::FETCH_ASSOC);
			}
			else {
				$query = "SELECT * FROM news WHERE news_id = ? ORDER BY news_id";
				$result = $db->executeQuery($query, array('1'), 'get_single_news_query');
				$newsArr = $result->fetch(PDO::FETCH_ASSOC);
			}
		}
	}	
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Форма редактирования новости</title>
		<script src="/scripts/tinymce/tinymce.min.js"></script>
		<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
		<script type="text/javascript">
		  tinymce.init({
			selector: '#news-textarea',
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
		<h2>Форма редактирования новости:</h2>
        <form action="update_news.php" method="post" enctype="multipart/form-data">
			<p>Дата: <input type="date" name="news-date" size="10" required value="<? echo $newsArr['news_date']; ?>"></p>
			<p>Заголовок: <input type="text" name="news-header" size="100" required value="<? echo $newsArr['news_header']; ?>"></p>
			<p>Текст: <textarea id="news-textarea" name="news-text" size="50"><? echo $newsArr['news_text']; ?></textarea></p>
			<input name="news-id" type="hidden" value="<? echo $newsId; ?>"></input>
			<p><strong>Добавить картинку:</strong></p> 
			<div class="image-upload-div">
				<p class="image-file">[$IMAGE1]<input type="file" name="news-image[]"></p>
				<ul>
					<li>Расположение картинки: 
						<select size="1" name="image-align[]">
							<option value="left">Слева</option>
							<option selected value="center">По центру</option>
							<option value="right">Справа</option>
						</select>
					</li>
					<li>Размер картинки:
						<ul>
							<li>ширина: <input type="number" min="20" max="500" name="image-width[]"> px</li>
							<li>высота: <input type="number" min="20" max="500" name="image-height[]"> px</li>
						</ul>
					</li>
				</ul>
			</div>
			<p><a class="add-image-btn" href="#more-images">+</a></p>
			<p><input type="submit" value="Изменить"></p>
		</form>
    </body>
	<script>
		var addBtn = document.getElementsByClassName('add-image-btn')[0];
		addBtn.addEventListener('click', function (e) {
			var linkElem = e.target;
			var parentElem = linkElem.parentNode;
			var addImageDiv = parentElem.previousElementSibling;
			var newAddImageDiv = addImageDiv.cloneNode(true);
			var totalFileFields = document.getElementsByClassName('image-upload-div').length + 1;
			var imageFile = newAddImageDiv.getElementsByClassName('image-file')[0];
			console.log(totalFileFields);
			
			imageFile.firstChild.textContent = "[$IMAGE" + totalFileFields + "]";
			parentElem.parentNode.insertBefore(newAddImageDiv, parentElem);
		}, false);
	</script>
</html>