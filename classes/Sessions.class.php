<?
	require_once (__DIR__.'/../functions/functions.php'); 
	
	class DBSessionHandler implements \ SessionHandlerInterface {
		private $db = null;
		private $sessionId = null;
		private $sessionTime = 7200; // 2 hours
		private $user = 'guest';
		private $ip = 'none';
		private $userAgent = 'none';
		private $data = null;
		private static $_instance = null; 
		
		function __destruct() {
			$this->db = null;
			$this->sessionId = null;
			$this->data = null;
			$this->instance = null;
		}
		
		public static function getInstance() {
			if(self::$_instance === null) {
				self::$_instance = new DBSessionHandler();
			}
			return self::$_instance;
		}

		public function open($savePath, $sessionName) {
			$this->db = DBClass::getInstance();
			$this->sessionId = session_id();
			$this->getIpAdress();
			$this->getUserAgent();
			
			if($this->sessionId === '') {
				$cookieId = $this->getSessionCookie();
				//error_log("LOG: cookie id => $cookieId", 0);

				return false;
			}
			
			if($this->db->getLink()) {
				$query = 'INSERT INTO sessions (session_hash, session_data, session_username, session_last_seen, session_ip, session_user_agent) 
						  VALUES (?, ?, ?, NOW(), ?, ?) 
						  ON CONFLICT (session_hash) DO UPDATE SET session_last_seen = NOW()';

				$res = $this->db->executeQuery($query, array($this->sessionId, $this->data, $this->user, $this->ip, $this->userAgent), 'open_session');
				//error_log("LOG: open new session with hash => $this->sessionId", 0);
			}
		}

		public function close() {
			//error_log("LOG: close session with hash => $this->sessionId", 0);
			return true;
		}

		public function read($sessionId) {	
			if($sessionId !== $this->sessionId) return;
			
			if($this->db->getLink()) {
				$query = "SELECT session_data FROM sessions WHERE session_hash = ?";
				$res = $this->db->executeQuery($query, array($this->sessionId), 'read_session');
				
				//error_log("LOG: read session with hash => $this->sessionId", 0);
				
				if($res === false) return false;
				
				$this->data = $res->fetchColumn();
				$res->closeCursor();
			}

			return $this->data;
		}

		public function write($sessionId, $sessionData) {
			if($sessionId !== $this->sessionId) return;
			
			$this->data = $sessionData;
			
			if($this->db->getLink()) {
				$query = "INSERT INTO sessions (session_hash, session_data, session_username, session_last_seen, session_ip, session_user_agent)
						  VALUES (?, ?, ?, NOW(), ?, ?) 
						  ON CONFLICT (session_hash) DO UPDATE SET session_data = ?, session_last_seen = NOW()";
				$res = $this->db->executeQuery($query, array($this->sessionId, $this->data, $this->user, $this->ip, $this->userAgent, $this->data), 'write_session');
				//error_log("LOG: write session with hash => $this->sessionId", 0);
			}
		}

		public function destroy($sessionId) {
			if($sessionId !== $this->sessionId) return;

			if($this->db->getLink()) {
				$query = "DELETE FROM sessions WHERE session_hash = ?";
				$res = $this->db->executeQuery($query, array($this->sessionId), 'destroy_session');
				
				//error_log("LOG: destroy session with hash => $this->sessionId", 0);
				
				if($res === true) {
					setcookie("PHPSESSID", "", time() - 3600);
					return true;
				}
			}
			
			return false;
		}

		public function gc($maxlifetime) {
			if($this->db->getLink()) {
				$query = "DELETE FROM sessions WHERE session_last_seen < (NOW() - INTERVAL '?' SECOND)";
				$res = $this->db->executeQuery($query, array($this->sessionTime), 'gc_session');
				//error_log("LOG: clear session with hash => $this->sessionId", 0);
			}
		}
		
		public function setUser($userLogin) {
			if($userLogin && $this->sessionId) {
				$this->user = $userLogin;
				if($this->db->getLink()) {
					$query = "UPDATE sessions SET session_username = ? WHERE session_hash = ?";
					$res = $this->db->executeQuery($query, array($userLogin, $this->sessionId), 'session_update_user');
					//error_log("LOG: setUser for session with hash => $this->sessionId", 0);
				}
			}
		}
		
		public function getIpAdress() {
			if(isset($_SERVER['REMOTE_ADDR']) && $this->sessionId) {
				$this->ip = $_SERVER['REMOTE_ADDR'];
			}
			
			return false;
		}
		
		public function getUserAgent() {
			if(isset($_SERVER['HTTP_USER_AGENT']) && $this->sessionId) {
				return $this->userAgent = $_SERVER['HTTP_USER_AGENT'];
			}
			
			return false;
		}
		
		public function getSessionCookie() {
			if(isset($_COOKIE["PHPSESSID"])) {
				return $_COOKIE["PHPSESSID"];
			}
			
			return false;
		}
		
		public function getData() {
			return $this->data;
		}
	}
?>