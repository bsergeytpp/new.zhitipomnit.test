<?
	class DBClass {
		public $link = NULL;
		public $result = NULL;
		private $provider = 'PGSQL';
		private static $_instance = null; 
		
		private function __construct() {
			//if(isset($conStr)) $this->connectionString = $conStr;
		}
		
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
			$this->provider = $provider;
			
			switch($provider) {
				case "PGSQL": $str = 'pgsql:'; break;
				case "MYSQL": $str = 'mysql:'; break;
				default: return false;
			}

			$str .= 'host='.$connStr['host'].';dbname='.$connStr['dbname'];
			$this->link = new PDO($str, $connStr['user'], $connStr['password']);
			
			/*if(function_exists('pg_connect')) {
				$this->link = pg_connect($connStr);
				$this->provider = 'PGSQL';
			}
			else if(function_exists('mysqli_connect')) {
				$this->link = mysqli_connect($connStr);
				$this->provider = 'MYSQL';
			}
			else {
				return false;
			}*/
		}
		
		public function executeQuery($query, $params, $prepName) {	
			if($this->link === NULL) {
				echo 'Соединение с базой данных не установлено';
				return false;
			}
			
			if($params) {
				$this->result = $this->link->prepare($query);
				$this->result->execute($params);
			}
			else {
				$this->result = $this->link->query($query);
			}
		
			/*if($params && $prepName) {
				$this->result = pg_prepare($this->link, $prepName, $query) or die('Error: '. pg_last_error());
				$this->result = pg_execute($this->link, $prepName, $params) or die('Error: '. pg_last_error());
			}
			else {
				$this->result = pg_query($this->link, $query) or die('Error: '. pg_last_error());
			}*/
			
			return $this->result;
		}
		
		public function getLink() {
			return $this->link;
		}
	}
?>