<?
	require_once "../functions/functions.php";
	
	function logOut() {
		session_destroy();
		header('Location: /admin/login.php');
		exit;
	}
	
	function checkUser($login, $password) {
		global $link;
		
		if(!$link) $link = connectToPostgres();

		$query = "SELECT user_login, user_password, user_group FROM users Where user_login = '".$login."'";
		$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
		
		if($result === false)	return false;
		
		$row = pg_fetch_array($result);
		
		if($row['user_password'] == $password) 
			return true;
		
		return false;
	}
?>