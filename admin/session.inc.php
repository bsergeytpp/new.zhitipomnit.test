<?
	session_start();
	if($_SESSION['admin'] !== true) {
		if(strpos($_SERVER['REQUEST_URI'], 'admin') !== false) {
			if(isset($_SESSION['user'])) {
				if($_SESSION['user'] !== null) {
					echo "Доступ запрещен!<br>";
					echo "<a href='../users/user_profile.php'>Назад к профилю</a>";
				}
				else {
					header('Location: /users/login.php');
				}
				exit;
			}
		}
	}
?>