<?
	session_start();
	if(!isset($_SESSION['admin'])) {
		if(strpos($_SERVER['REQUEST_URI'], 'admin') !== false) {
			header('Location: /admin/login.php?ref='.$_SERVER['REQUEST_URI']);
			exit;
		}
	}
?>