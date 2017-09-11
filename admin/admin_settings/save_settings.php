<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	if($_SERVER['REQUEST_METHOD'] == 'GET') {
		if( isset($_GET['NEWS']) && 
			isset($_GET['OLDNEWS']) &&
			isset($_GET['PUBLS']) &&
			isset($_GET['PRESS'])) {
				$GLOBALS['NEWS_MAXCOUNT'] = (string)$_GET['NEWS'];
				$GLOBALS['OLDNEWS_MAXCOUNT'] = (string)$_GET['OLDNEWS'];
				$GLOBALS['PUBLS_MAXCOUNT'] = (string)$_GET['PUBLS'];
				$GLOBALS['PRESS_MAXCOUNT'] = (string)$_GET['PRESS'];
				echo "Данные из JavaScript: $NEWS_MAXCOUNT"; //$_GET['NEWS'] : $_GET['OLDNEWS'] : $_GET['PUBLS'] : $_GET['PRESS']";
			}
	}
?>