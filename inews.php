<? include "functions/functions.php"; ?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Test</title>
        <link type="text/css" rel="StyleSheet" href="styles/styles.css" />
    </head>
    <body>
        <!--<div id="background-div"></div>-->
        <?
            include "content/header.php";
        ?>
        <div class="clear-div"></div>
        <div id="wrapper" class="blocks">
            <?
				include "content/menu.php";
			?>
            <div class="article ">
                <?
					include "content/news.php";
				?>
            </div>
            <div class="clear-div"></div>
        </div>
        <?
            include "content/bottom.php";
        ?> 
        <div class="scroll-button" onclick="window.scrollTo(0,0);">↑ Наверх</div>
        <script src="scripts/script.js"></script>
    </body>
</html>