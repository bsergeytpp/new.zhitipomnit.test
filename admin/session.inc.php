<?
	session_start();
	if(!isset($_SESSION['admin'])) {
		if(strpos($_SERVER['REQUEST_URI'], 'admin') !== false) {
			if($_SESSION['user'] !== null) {
				echo "Доступ запрещен!<br>";
				echo "<a href='../users/user_profile.php'>Назад к профилю</a>";
			}
			else {
				header('Location: /admin/login.php?ref='.$_SERVER['REQUEST_URI']);
			}
			exit;
		}
	}
?>