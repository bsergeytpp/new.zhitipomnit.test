<? 
	require_once '../sessions/session.inc.php';

	if($_SERVER['REQUEST_METHOD'] == 'HEAD') {
		if(isset($_SESSION['admin'])) {
			if($_SESSION['admin'] === true) {
				header("Content-type: text/plain; charset=utf-8");
				header("IsAdmin: ".$_SESSION['admin']);
			}
		}
		if(isset($_SESSION['user'])) {
			if($_SESSION['user'] !== null) {
				header("Content-type: text/plain; charset=utf-8");
				header("UserLogin: ".$_SESSION['user']);
			}
			else {
				header("Content-type: text/plain; charset=utf-8");
				header("UserLogin: ".null);
			}
		}
	}
?>