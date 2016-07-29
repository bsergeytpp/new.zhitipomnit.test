<? 
	require "functions/functions.php"; 
	require_once "admin/admin_security/session.inc.php";
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width; initial-scale=1.0;">
        <title>Главная страница</title>
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
				include "content/users.php";
			?>
			<?
				include "content/menu.php";
			?>
            <div class="article ">
                <?
					$params = isset($_GET['pages']) ? $_GET['pages'] : '';
					switch($params) {
						case 'main': 
							include "content/main.php"; break;
						case 'news': 
							include "content/news.php"; break;
						case 'publ': 
							include "content/publ.php"; break;
						case 'contacts': 
							include "content/contacts.php"; break;
						case 'memory': 
							include "content/memory.php"; break;
						case 'about': 
							include "content/about.php"; break;
						case 'mail': 
							include "content/mail.php"; break;
						case 'press': 
							include "content/press.php"; break;
						default:
							include "content/main.php";
							include "content/news.php";
							break;
					}
				?>
            </div>
            <div class="clear-div"></div>
        </div>
        <?
            include "content/bottom.php";
        ?> 
        <div class="scroll-button" onclick="window.scrollTo(0,0);">↑ Наверх</div>
        <script src="scripts/script.js"></script>
		<script src="scripts/tinymce/tinymce.min.js"></script>
    </body>
</html>