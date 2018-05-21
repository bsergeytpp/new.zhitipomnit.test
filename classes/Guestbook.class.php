<?
	require_once(__DIR__.'/../functions/functions.php');
	
	class GuestbookClass {
		private $db = null; 
		private $messages = [];
		private $totalMessages = -1;
		
		public function __construct() {
			$this->db = DBClass::getInstance();
		}
		public function __clone() {}
		
		public function getMessages() {
			$query = "SELECT * FROM guestbook ORDER BY gb_id";
			$res = $this->db->executeQuery($query, null, null);

			while($row = $res->fetch(PDO::FETCH_ASSOC)) {
				$this->messages[] = $row;
			}
			
			$this->totalMessages = count($this->messages);
			
			if($this->totalMessages < 1) {
				echo "<div class='error-message'>Сообщения не найдены</div>";
				return;
			}
			
			for($i=0; $i<$this->totalMessages; $i++) {
				$this->messages[$i] = $this->createMessageFromTemplate($this->messages[$i]);
			}
			
			$this->printMessages();
		}
		
		public function createMessageFromTemplate($message) {
			$messageKeys = ['gbId', 'gbDate', 'gbAuthor', 'gbText', 'gbEmail'];
			$message = array_combine($messageKeys, $message);
			$messageTemplate = file_get_contents(__DIR__.'/../content/templates/guestbook_template.php');
			$messageTemplate = replaceTemplateTags($messageTemplate, $message);
			
			return $messageTemplate;
		}
		
		public function printMessages() {
			echo implode($this->messages);
		}
		
		public function addMessage($date, $author, $text, $email) {
			$query = 'INSERT INTO guestbook (gb_date, gb_author, gb_text, gb_email) 
							 VALUES (?, ?, ?, ?)';
			$res = $this->db->executeQuery($query, array($date, $author, $text, $email), 'add_guestbook_message');
		}
		
		public function deleteMessage($messageId) {
			$query = 'DELETE FROM guestbook WHERE gb_id = ?';
			$res = $this->db->executeQuery($query, array($messageId), 'delete_guestbook_message');
		}
		
		public function editMessage($messageId, $messageText) {
			$query = 'UPDATE guestbook SET gb_text = ? WHERE gb_id LIKE ?';
			$res = $this->db->executeQuery($query, array($messageText, $messageId), 'edit_guestbook_message');
		}
	}
?>