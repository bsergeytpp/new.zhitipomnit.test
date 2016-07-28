<?
	require_once (__DIR__."/../functions/admin_functions.php");
	
	function logOut() {
		session_destroy();
		
		if(isset($_SESSION['admin'])) {
			unset($_SESSION['admin']);
		}
		
		header('Location: ../../users/login.php');
		exit;
	}
	
	function checkUser($login, $password) {
		global $link;
		
		if(!$link) $link = connectToPostgres();

		$query = "SELECT user_login, user_password, user_group, user_email FROM users Where user_login = '".$login."'";
		$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
		
		if($result === false) return false;
		
		$row = pg_fetch_array($result);
		
		if(password_verify($password, $row['user_password'])) {
			echo "Password is correct";
			return $row;
		}
		
		return false;
	}
?>