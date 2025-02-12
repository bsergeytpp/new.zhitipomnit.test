<? 
	require_once "sessions/session.inc.php";
	$titlePath = getPageTitlePath();
?>
<!DOCTYPE html>
<html lang="ru">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
		<meta name="Description" content="Сайт по поиску и увековечению памяти жертв Второй мировой войны Жить и Помнить">
        <title><? echo 'Жить и Помнить -'.$titlePath['title']; ?></title>
        <link type="text/css" rel="StyleSheet" href="styles/styles.css" />
		<link rel="shortcut icon" href="favicon.png" type="image/png">
		<link rel="preload" href="images/background.jpg" as="image">
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
			<div class="article <? addWebpClass('article-mod', 'article-comp'); ?>">
                <?
					includeContent($titlePath['path']);
				?>
            </div>
            <div class="clear-div"></div>
        </div>
        <?
            include "content/bottom.php";
        ?> 
        <script src="scripts/purify.min.js"></script>
        <script src="scripts/jslib.js"></script>
        <script src="scripts/script.js"></script>
        <script src="scripts/admin.class.js" defer></script>
        <script src="scripts/user.class.js" defer></script>
		<!--<script src="scripts/tinymce/tinymce.min.js"></script>-->
    </body>
</html>