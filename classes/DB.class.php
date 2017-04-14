<?
	class DBClass {
		public $link = NULL;
		public $result = NULL;
		private $provider = 'PGSQL';
		private static $_instance = null; 
		
		private function __construct() {}
		
		private function __clone() {}
		
		function __destruct() {
			$this->link = null;
			$this->result = null;
			$this->instance = null;
		}
		
		public static function getInstance() {
			if(self::$_instance === null) {
				self::$_instance = new DBClass();
			}
			return self::$_instance;
		}
		
		public function connectToDB($connStr, $provider) {
			$str = '';
			
			if(isset($provider)) $this->provider = $provider;
			
			switch($provider) {
				case "PGSQL": $str = 'pgsql:'; break;
				case "MYSQL": $str = 'mysql:'; break;
				default: return false;
			}

			$str .= 'host='.$connStr['host'].';dbname='.$connStr['dbname'];
			try{
				$this->link = new PDO($str, $connStr['user'], $connStr['password'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
			}
			catch(PDOException $e) {
				echo addLogs(
					5, 'failed connection', 'Соединение оборвалось: ' . $e->getMessage(), 
					'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], 
					date('Y-m-d H:i:sO'), 
					true);
				exit;
			}
		}
		
		public function executeQuery($query, $params, $prepName) {	
			if($this->link === NULL) {
				echo "<div class='error-message'>Соединение с базой данных не установлено</div>";
				return false;
			}
			
			try {
				if($params) {
					$this->result = $this->link->prepare($query);
					$this->result->execute($params);
				}
				else {
					$this->result = $this->link->query($query);
				}
				
				return $this->result;
			}
			catch(PDOException $e) {
				echo addLogs(
					5, 'failed query', $e -> getCode() . ":" . $e -> getMessage(), 
					'http://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'], 
					date('Y-m-d H:i:sO'), 
					true);
				exit;
			}
		}
		
		public function getLink() {
			return $this->link;
		}
	}
?>