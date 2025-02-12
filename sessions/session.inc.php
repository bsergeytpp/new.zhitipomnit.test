<?
	require_once (__DIR__."/../functions/functions.php");

	$sessionHandler = DBSessionHandler::getInstance();
	//session_set_save_handler($sessionHandler, true);
	//session_start();
	
	$isAdmin = (isset($_SESSION['admin'])) ? $_SESSION['admin'] : null;
	$userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
	
	if($isAdmin !== true) {
		if(strpos($_SERVER['REQUEST_URI'], 'admin') == true && strpos($_SERVER['REQUEST_URI'], 'save_comments') == false) {
			if($userLogin !== null) {
				echo "<div class='error-message'>Доступ запрещен!</div>";
				echo "<a href='../../users/user_profile.php'>Назад к профилю</a>";
			}
			else {
				header('Location: ../../users/login.php');
			}
			exit;
		}
	}
?>