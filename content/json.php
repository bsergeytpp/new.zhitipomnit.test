<? 
	require_once '../admin/session.inc.php';
	
	if(isset($_SESSION['admin'])) {
		header("Content-type: text/plain; charset=utf-8");
		header("IsAdmin: ".$_SESSION['admin']);
	}
?>