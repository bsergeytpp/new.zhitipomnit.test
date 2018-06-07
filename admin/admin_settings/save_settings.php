<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
		
		if(isset($_POST['NEWS'])    && 
		   isset($_POST['OLDNEWS']) && 
		   isset($_POST['PUBLS'])   && 
		   isset($_POST['PRESS'])   && 
		   isset($_POST['LOGS'])) {
			$sqlData = [
				'NEWS_MAXCOUNT' => $_POST['NEWS'],
				'OLDNEWS_MAXCOUNT' => $_POST['OLDNEWS'],
				'PUBLS_MAXCOUNT' => $_POST['PUBLS'],
				'PRESS_MAXCOUNT' => $_POST['PRESS'],
				'LOGS_MAXCOUNT' => $_POST['LOGS']
			];
			updateSettings($sqlData, $userLogin, 'site_settings');
		}
		else if(isset($_POST['news_style'])) {
			$sqlData = [
				'news-style' => $_POST['news_style']
			];
			updateSettings($sqlData, $userLogin, 'user_settings');
		}
	}
?>