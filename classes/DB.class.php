<?
	class DBClass {
		public $link = NULL;
		public $result = NULL;
		//private $connectionString = '';
		private static $_instance = null;
		
		private function __construct() {
			//if(isset($conStr)) $this->connectionString = $conStr;
		}
		
		private function __clone() {}
		
		public static function getInstance() {
			if(self::$_instance === null) {
				self::$_instance = new DBClass();
			}
			return self::$_instance;
		}
		
		public function connectToDB($connStr) {		
			if(!function_exists('pg_connect')) {
				return false;
			}
			
			$this->link = pg_connect($connStr);
		}
		
		public function executeQuery($query, $params, $prepName) {	
			if($this->link === NULL) {
				echo 'Соединение с базой данных не установлено';
				return false;
			}
		
			if($params && $prepName) {
				$this->result = pg_prepare($this->link, $prepName, $query) or die('Error: '. pg_last_error());
				$this->result = pg_execute($this->link, $prepName, $params) or die('Error: '. pg_last_error());
			}
			else {
				$this->result = pg_query($this->link, $query) or die('Error: '. pg_last_error());
			}
			
			return $this->result;
		}
		
		public function getLink() {
			return $this->link;
		}
	}
?>