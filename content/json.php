<? 
	require_once '../admin/session.inc.php';
	
	if(isset($_SESSION['admin'])) {
		header("Content-type: text/plain; charset=utf-8");
		header("IsAdmin: ".$_SESSION['admin']);
	}
	
	/*if(isset($_SESSION['admin']))
		echo $_SESSION['admin'];//json_encode($_SESSION['admin']);
	else echo false;*/
?>