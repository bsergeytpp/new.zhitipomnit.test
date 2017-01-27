<?
	require_once (__DIR__."/../functions/admin_functions.php");
	
	function logOut() {
		deleteSessionDB();
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

		$query = "SELECT user_id, user_login, user_password, user_group, user_email FROM users WHERE user_login = $1";
		pg_query($link, "DEALLOCATE ALL");
		$result = executeQuery($query, array($login), 'check_user');
		
		if($result === false) return false;
		
		$row = pg_fetch_array($result);
		
		if(password_verify($password, $row['user_password'])) {
			echo "Password is correct";
			return $row;
		}
		
		return false;
	}
?>