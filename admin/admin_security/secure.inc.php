<?
	require_once (__DIR__."/../functions/admin_functions.php");
	
	function logOut() {
		require_once (__DIR__."/../../sessions/session.inc.php");
		global $sessionHandler;
		
		if(isset($sessionHandler)) {
			$sessionHandler->destroy(session_id());
		}
		session_destroy();
		
		if(isset($_SESSION['admin'])) {
			unset($_SESSION['admin']);
		}
		
		header('Location: ../../users/login.php');
		//exit;
	}
	
	function checkUser($login, $password) {
		global $db;
		
		$query = "SELECT user_id, user_login, user_password, user_group, user_email FROM users WHERE user_login = ? AND user_deleted IS NOT TRUE";
		//pg_query($db->getLink(), "DEALLOCATE ALL");
		$result = $db->executeQuery($query, array($login), 'check_user');
		
		if($result === false) return false;
		
		$row = $result->fetch(PDO::FETCH_BOTH);	//TODO: не проверялось
		
		if(password_verify($password, $row['user_password'])) {
			echo "<div class='success-message'>Верный пароль</div>";
			return $row;
		}
		
		return false;
	}
?>