<?
	session_start();
	$isAdmin = (isset($_SESSION['admin'])) ? $_SESSION['admin'] : null;
	$userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
	if($isAdmin !== true) {
		if(strpos($_SERVER['REQUEST_URI'], 'admin') !== false) {
			if($userLogin !== null) {
				echo "Доступ запрещен!<br>";
				echo "<a href='../../users/user_profile.php'>Назад к профилю</a>";
			}
			else {
				header('Location: ../../users/login.php');
			}
			exit;
		}
	}
?>