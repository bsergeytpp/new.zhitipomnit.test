<?
	class PressClass {
		private $pressArr = [];
		private $pressName = '';
		private $pressPage = 1;
		
		public function __construct($name, $page) {
			if(isset($name)) $this->pressName = $name;
			if(isset($page)) $this->pressPage = $page;
		}
		
		public function getPress() {
			$press = file_get_contents('content/press/gazeta.html');
			$this->createPressList($press);
		}
		
		public function setPressName($name) {
			if(isset($name)) $this->pressName = $name;
		}
		
		public function setPressPage($page) {
			if(isset($page)) $this->pressPage = $page;
		}
		
		public function getPressName() {
			if($this->pressName !== '')  return $this->pressPage;
			else return false;
		}
		
		public function getSinglePress() {
			echo "<strong><a href='index.php?pages=press'>Назад</a></strong><br>";
			$this->pressArr = $this->getPressArray();
			$totalPress = count($this->pressArr);
			
			for($i=0, $j=1; $i<$totalPress; $i++, $j++) {
				echo "<a class='article-press-links' href='index.php?pages=press&custom-press=$this->pressName&page=$i'>Страница $j</a>";
			}
			
			echo "<div class='clear-div'></div>";
			
			switch($this->pressPage) {
				case 1: echo $this->getPressPage($this->pressArr, 0); break;
				case 2: echo $this->getPressPage($this->pressArr, 1); break;
				case 3: echo $this->getPressPage($this->pressArr, 2); break;
				case 4: echo $this->getPressPage($this->pressArr, 3); break;
				default: echo $this->getPressPage($this->pressArr, 0); break;
			}
		}
		
		private function getPressArray() {			
			for($i=0,$j=1;$i<4;$i++,$j++) {
				$press = file_get_contents("content/press/$this->pressName/$j.html");
				
				if(!mb_detect_encoding($press, 'UTF-8', true)) {
					$press = mb_convert_encoding($press, "UTF-8", 'windows-1251');
				}
				
				// ошибки в большом кол-ве файлов
				$pattern = [
					'materials',
					'<img src="../../images/m1.gif" width="100%" height="28" border="0" />',
					'log.jpg',
					'<img src="../../images/" width="687" height="153" />',
					'<img src="../../images/m2.gif" width="100%" height="21" border="0" />',
					'style=padding-top: 10""',
					'style=padding-top:10""',
					'style=padding-top:10"',
					'style=padding-top: 10"',
					'style=padding-top: 10 ""',
					'bgcolor="#FFFFFF""'
				];
				$replacement = array_fill(0, 11, '');
				$replacement[0] = "content/press/$this->pressName/materials";
				$press = str_replace($pattern, $replacement, $press);
				$press = preg_replace("/Фонд Жить и Помнить/", '', $press, 1);
				$press = strip_tags($press, '<h1><h2><h3><p><strong><a><img><ul><ol><li>');	
				$this->pressArr[] = $press;
			}
			
			return $this->pressArr;
		}
		
		private function getPressPage() {
			return $this->pressArr[$this->pressPage];	// откуда pressPage ?
		}
		
		private function createPressList($press) {
			$this->pressArr = explode(PHP_EOL, $press);
			$totalPress = count($this->pressArr);
			
			if($totalPress < PRESS_MAXCOUNT) {
				echo $this->pressArr;
				return;
			}
			
			$list = getULlist($totalPress, PRESS_MAXCOUNT, 'index.php?pages=press&page=', $this->pressPage);
			
			echo $list;
			echo implode(getSampleOfArray($this->pressPage, PRESS_MAXCOUNT, $this->pressArr));
			echo $list;
		}
	}
?>