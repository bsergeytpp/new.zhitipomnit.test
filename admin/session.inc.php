<?
	session_start();
	if(!isset($_SESSION['admin'])) {
		header('Location: /admin/login.php?ref='.$_SERVER['REQUEST_URI']);
		exit;
	}
?>