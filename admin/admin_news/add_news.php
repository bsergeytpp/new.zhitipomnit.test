<?
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Форма добавления новости</title>
		<script src="/scripts/tinymce/tinymce.min.js"></script>
		<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
		<script type="text/javascript">
		  tinymce.init({
			selector: '#news-textarea',
			height: 300,
			width: 1000,
			language: 'ru',
			plugins: 'code',
			paste_data_images: true
		  });
		</script>
    </head>
    <body>
        <a href="/admin">Назад к админке</a><br>
		<h2>Форма добавления новости:</h2>
        <form action="save_news.php" method="post" enctype="multipart/form-data">
			<p>Дата: <input type="date" name="news-date" size="10" required></p>
			<p>Заголовок: <input type="text" name="news-header" size="20" required></p>
			<p>Текст: <textarea id="news-textarea" name="news-text" size="50"></textarea></p>
			<!--<p>Есть логотип: <input name="hasImage" type="checkbox" required>Да/Нет</input></p>-->
			<p>Добавить картинку:</p> 
			<p class="image-file">[$IMAGE1]<input type="file" name="news-image[]">
				<br>Расположение картинки: 
				<select size="1" name="image-align[]">
					<option value="left">Слева</option>
					<option selected value="center">По центру</option>
					<option value="right">Справа</option>
				</select>
				<br>Размер картинки:<br>
				ширина: <input type="number" min="20" max="500" name="image-width[]"> px<br>
				высота: <input type="number" min="20" max="500" name="image-height[]"> px
			</p>
			<p><a class="add-image-btn" href="#more-images">+</a></p>
			<p><input type="submit" value="Добавить"></p>
		</form>
    </body>
	<script>
		var addBtn = document.getElementsByClassName('add-image-btn')[0];
		addBtn.addEventListener('click', function (e) {
			var target = e.target;
			var targetParent = target.parentNode;
			var prevFileField = targetParent.previousElementSibling;
			var newFileField = prevFileField.cloneNode(true);
			var totalFileFields = document.getElementsByClassName('image-file').length + 1;
			console.log(totalFileFields);
			
			newFileField.firstChild.textContent = "[$IMAGE" + totalFileFields + "]";
			targetParent.parentNode.insertBefore(newFileField, targetParent);
		}, false);
	</script>
</html>