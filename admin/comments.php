<?
	require_once "admin_security/session.inc.php";
	require_once "admin_security/secure.inc.php";
	require_once "functions/admin_functions.php";
	include "save_comments.php";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <title>Комментарии</title>
		<script src="../scripts/tinymce/tinymce.min.js"></script>
		<!--<script type="text/javascript">
		  tinymce.init({
			selector: '#comments-textarea',
			height: 200,
			width: 400,
			language: 'ru_RU',
			plugins: 'code',
			paste_data_images: true
		  });
		</script>-->
    </head>
    <body>
        <div id="background-div"></div>
		<? include "comments_form.php"; ?>
    </body>
</html>