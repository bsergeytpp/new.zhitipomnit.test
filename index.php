<? 
	require_once "admin/admin_security/session.inc.php";
	$titlePath = getPageTitlePath();
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><? echo 'Жить и Помнить -'.$titlePath['title']; ?></title>
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
					includeContent($titlePath['path']);
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