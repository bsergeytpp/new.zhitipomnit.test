<?
	require_once (__DIR__."/../functions/admin_functions.php");
	
	function logOut() {
		//deleteSessionDB();
		require "session.inc.php";
		
		if(isset($sessionHandler)) {
			$sessionHandler->destroy(session_id());
		}
		session_destroy();
		
		if(isset($_SESSION['admin'])) {
			unset($_SESSION['admin']);
		}
		
		header('Location: ../../users/login.php');
		exit;
	}
	
	function checkUser($login, $password) {
		global $db;
		
		$query = "SELECT user_id, user_login, user_password, user_group, user_email FROM users WHERE user_login = ?";
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