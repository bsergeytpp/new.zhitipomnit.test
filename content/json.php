<? 
	require_once '../admin/admin_security/session.inc.php';
	
	if(isset($_SESSION['admin'])) {
		if($_SESSION['admin'] === true) {
			header("Content-type: text/plain; charset=utf-8");
			header("IsAdmin: ".$_SESSION['admin']);
		}
	}
?>