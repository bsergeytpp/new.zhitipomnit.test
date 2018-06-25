<? 
	require_once "admin/admin_security/session.inc.php";
	// заголовок страницы и путь к контенту
	$title = ''; $path = '';
	$params = isset($_GET['pages']) ? $_GET['pages'] : '';
	switch($params) {
		case 'main': 
			$title = 'Главная страница'; 
			$path = 'content/main.php'; 
			break;
		case 'news': 
			$title = 'Новости'; 
			$path = 'content/news.php'; 
			break;
		case 'publ': 
			$title = 'Статьи'; 
			$path = 'content/publ.php'; 
			break;
		case 'contacts': 
			$title = 'Контакты'; 
			$path = 'content/contacts.php'; 
			break;
		case 'memory': 
			$title = 'Книга Памяти'; 
			$path = 'content/memory.php'; 
			break;
		case 'about': 
			$title = 'О Фонде'; 
			$path = 'content/about.php'; 
			break;
		case 'mail': 
			$title = 'Почта'; 
			$path = 'content/mail.php'; 
			break;
		case 'press': 
			$title = 'Газета'; 
			$path = 'content/press.php'; 
			break;
		case 'search':
			$title = 'Поиск по Книге Памяти'; 
			$path = 'content/search.php'; 
			break;
		case 'guestbook':
			$title = 'Гостевая книга'; 
			$path = 'content/guestbook.php'; 
			break;
		default:
			$title = 'Главная страница';
			$path = [
				'content/main.php',
				'content/news.php',
				'content/publ.php'
			];
			break;
	}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><? echo 'Жить и Помнить -'.$title; ?></title>
        <link type="text/css" rel="StyleSheet" href="styles/styles.css" />
		<link rel="shortcut icon" href="favicon.png" type="image/png">
    </head>
    <body>
        <!--<div id="background-div"></div>-->
        <?
            include "content/header.php";
        ?>
		<div class="clear-div"></div>
        <div id="wrapper" class="blocks">
            <?
				//include "content/users.php";
			?>
			<?
				include "content/menu.php";
			?>
            <div class="article ">
                <?
					includeContent($path);
				?>
            </div>
            <div class="clear-div"></div>
        </div>
        <?
            include "content/bottom.php";
        ?> 
        <script src="scripts/jslib.js"></script>
        <script src="scripts/script.js"></script>
        <script src="scripts/admin.class.js"></script>
        <script src="scripts/user.class.js"></script>
		<!--<script src="scripts/tinymce/tinymce.min.js"></script>-->
    </body>
</html>