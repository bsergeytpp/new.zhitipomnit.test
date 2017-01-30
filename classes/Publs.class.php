<?
	abstract class PublsClass {
		protected $pageNum = 1;
		protected $totalPubls;
		
		public function __construct($page) {
			if(isset($page)) $this->pageNum = $page;
		}
		
		abstract public function getPubls();
		abstract public function getSinglePubl($date);
		
		abstract protected function createExceptPubl($publ);
		
		protected function createPublsList($publs) {
			if($this->totalPubls < PUBLS_MAXCOUNT) {
				echo implode($publs);
				return;
			}

			$list = getULlist($this->totalPubls, PUBLS_MAXCOUNT, 'index.php?pages=publ&page=', $this->pageNum);
			echo $list;
			echo implode(getSampleOfArray($this->pageNum, PUBLS_MAXCOUNT, $publs));
			echo $list;
		}	
	}
	
	class DbPublsClass extends PublsClass {
		private $db = null;

		public function __construct($page) {
			$this->db = DBClass::getInstance();
			parent::__construct($page);
		}
		
		public function getPubls() {
			$dbPubls = [];
			$query = "SELECT publs_id, publs_header FROM publs";
			$res = $this->db->executeQuery($query, null, null);
			
			while($row = pg_fetch_assoc($res)) {
				$dbPubls[] = $row;
			}
			
			$this->totalPubls = count($dbPubls);
			
			for($i=0; $i<$this->totalPubls; $i++) {
				$dbPubls[$i] = $this->createExceptPubl($dbPubls[$i]);
			}
			
			// сортируем статьи по ID
			$dbPubls = array_reverse($dbPubls);
			$this->createPublsList($dbPubls);
		}
		
		public function getSinglePubl($date) {
			echo "<strong><a href='index.php?pages=publ'>Назад</a></strong><br>";
			
			if($this->db->getLink()) {
				$query = 'SELECT * FROM publs WHERE publs_id = $1';
				$res = $this->db->executeQuery($query, array($date), 'get_publs');
				$row = pg_fetch_assoc($res);
				$publ = '<div id="'.$row['publs_id'].'" class="publs-full-container"><h3>'.$row['publs_header'].'</h3>'.$row['publs_text'].'</div>';
				
				return $publ;
			}
			else {
				echo "<h1>Такой статьи не существует!</h1>";
				echo "<a href='index.php?pages=publ&page=".$this->pageNum."'>К статьям</a>";
				return;
			}
		}
		
		protected function createExceptPubl($publ) {
			$publTemplate = file_get_contents('content/templates/publ_template.php');
			$pattern = ['publId', 'publUrl', 'publHeader'];
			$replacement = [$publ['publs_id'], $publ['publs_id'], $publ['publs_header']];
			$publTemplate = str_replace($pattern, $replacement, $publTemplate);
			
			return $publTemplate;
		}
	}
	
	class OldPublsClass extends PublsClass {
		public function getPubls() {
			$publsList = file('content/publ/publik.html');
			$oldPubls = [];
			
			foreach($publsList as $publ) {
				// получаем ссылку
				$a = strpos($publ, '"');
				$b = strpos($publ, '"', $a+1);
				$href = substr($publ, $a+1, --$b-$a);
				// получаем заголовок
				$a = strpos($publ, '>');
				$b = strpos($publ, '<', $a);
				$text = substr($publ, $a+1, --$b-$a);
				$oldPubl = [
					'link' => 'content/publ/'.$href,
					'text' => $text
				];
				$oldPubl = $this->createExceptPubl($oldPubl);
				$oldPubls[] = $oldPubl;
			}
			
			$this->totalPubls = count($oldPubls);
			$this->createPublsList($oldPubls);
		}
		
		protected function createPublsList($publs) {
			if($this->totalPubls < PUBLS_MAXCOUNT) {
				echo implode($publs);
				return;
			}

			$list = getULlist($this->totalPubls, PUBLS_MAXCOUNT, 'index.php?pages=publ&custom-publ=all-old&page=', $this->pageNum);
			echo $list;
			echo implode(getSampleOfArray($this->pageNum, PUBLS_MAXCOUNT, $publs));
			echo $list;
		}	
		
		public function getSinglePubl($date) {
			echo "<strong><a href='index.php?pages=publ&custom-publ=all-old'>Назад</a></strong><br>";
			
			if(substr($date, -4, 4) == 'html'){
				$publ = file_get_contents($date);
				
				if(!mb_detect_encoding($publ, "UTF-8", true)) {
					$publ = mb_convert_encoding($publ, "UTF-8", "windows-1251");
				}
				
				// ошибки в большом кол-ве файлов
				$pattern = [
					'materials',
					'ПУБЛИКАЦИИ',
					'<img src="../images/m1.gif" width="100%" height="28" border="0" />',
					'<img src="../images/m2.gif" width="100%" height="21" border="0" />',
					'<IMG SRC="../images/m2.gif" ALIGN=BOTTOM WIDTH=100% HEIGHT=21 BORDER=0>',
					'style=padding-top: 10""',
					'style=padding-top:10""',
					'style=padding-top:10"',
					'style=padding-top: 10"',
					'style=padding-top: 10 ""',
					'style="padding-top: 10""'
				];
				$replacemetnt = array_fill(0, 11, '');
				$replacemetnt[0] = "content/publ/materials";
				$publ = str_replace($pattern, $replacemetnt, $publ);
				$publ = preg_replace("/Фонд Жить и Помнить/", "", $publ, 1);
				$publ = strip_tags($publ, '<h1><h2><h3><p><strong><a><img><ol><ul><li>');
				
				return $publ;
			}
			else {
				echo "<h1>Такой статьи не существует!</h1>";
				echo "<a href='index.php?pages=publ&custom-publ=all-old&page=".$this->pageNum."'>К статьям</a>";
				return;
			}
		}
		
		protected function createExceptPubl($publ) {
			$publTemplate = file_get_contents('content/templates/publ_template.php');
			$pattern =["publUrl", "publHeader"];
			$replacement = [$publ['link'], $publ['text']];
			$publTemplate = str_replace($pattern, $replacement, $publTemplate);
			
			return $publTemplate;
		}
	}
	
	class OtherPublsClass extends PublsClass {
		public function getPubls() {
			$dir = "content/publ/";
			$publArr = scandir($dir);
			$otherPubls = [];
			
			foreach($publArr as $publName) {
				$publPath = $dir.$publName;
				
				if(file_exists($publPath) && is_file($publPath)) {
					if(substr($publPath, -3, 3) == 'txt') {
						$publ = file_get_contents($publPath);
						$publ = $this->createExceptPubl($publ);
						$otherPubls[] = $publ;
					}
				}
			}
			
			$this->totalPubls = count($otherPubls);
			$this->createPublsList($otherPubls);
		}
		
		public function getSinglePubl($date) {
			echo "<strong><a href='index.php?pages=publ'>Назад</a></strong><br>";
			
			if(substr($date, -3, 3) == 'txt') {
				$publArr = unserialize(file_get_contents($date));
				
				for($i=0; $i<3; $i++) {
					if($i == 2) $publ = $publArr[$i];
				}
				
				return $publ;
			}
			else {
				echo "<h1>Такой статьи не существует!</h1>";
				echo "<a href='index.php?pages=publ&page=".$this->pageNum."'>К статьям</a>";
				return;
			}	
		}
		
		protected function createExceptPubl($publ) {
			$publTemplate = file_get_contents('content/templates/publ_template.php');
			$publArr = unserialize($publ);
			$publKeys = ['publHeader', 'publUrl', 'publText'];
			$publArr = array_combine($publKeys, $publArr);
			$publTemplate = replaceTemplateTags($publTemplate, $publArr);
			
			return $publTemplate;
		}
	}
?>