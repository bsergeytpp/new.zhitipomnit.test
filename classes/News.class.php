<?
	abstract class NewsClass {
		protected $newsDate = '';
		protected $pageNum = 1;
		protected $newsArr = [];
		protected $totalNews = null;
		
		abstract public function getNews();
		abstract public function getSingleNews();
		
		public function __construct($date, $page) {
			if(isset($date)) $this->newsDate = $date;
			if(isset($page)) $this->pageNum = $page;
		}
		
		public function setDate($date) {
			$this->newsDate = $date;
		}
		
		protected function createNewsList() {		
			// делаем ссылки на страницы списка		
			$list = getULlist($this->totalNews, NEWS_MAXCOUNT, 'index.php?pages=news&page=', $this->pageNum);

			// новостей мало, список не делаем
			if($this->totalNews <= NEWS_MAXCOUNT) {
				echo implode($this->newsArr);
				return;
			}
					
			echo $list, implode(getSampleOfArray($this->pageNum, NEWS_MAXCOUNT, $this->newsArr)), $list;
		}
		
		protected function createExceptNews($news) {
			$news[1] = exceptStr(strip_tags($news[1]));	// TODO: некрасиво
			$newsKeys = ['newsDate', 'newsText', 'newsUrl'];
			$news = array_combine($newsKeys, $news);
			$newsTemplate = file_get_contents('content/templates/news_template.php');
			$newsTemplate = replaceTemplateTags($newsTemplate, $news);

			return $newsTemplate;
		}

		protected function sortNews() {
			for($i=1; $i<$this->totalNews; $i++) {					
				for($j= $i-1; $j>=0; $j--) {
					$tempCur = reverseDate($this->newsArr[$j][0]);
					$tempNext = reverseDate($this->newsArr[$j+1][0]);
					
					if(intval($tempCur) < intval($tempNext)) {
						$temp = $this->newsArr[$j+1];
						$this->newsArr[$j+1] = $this->newsArr[$j];
						$this->newsArr[$j] = $temp;
					}
				}
			}
		}
	}
	
	class DbNewsClass extends NewsClass {
		public function getNews() {
			global $link;
			$query = 'SELECT * FROM news';
			$res = pg_query($link, $query) or die('Query error: '. pg_last_error());
			echo "<h4>Новости из базы данных</h4>";
			
			while($row = pg_fetch_assoc($res)) {
				$this->newsArr[] = $row;
			}
			
			$this->totalNews = count($this->newsArr);
			$this->sortNews();
			
			for($i = 0; $i<$this->totalNews; $i++) {
				$this->newsArr[$i] = $this->createExceptNews($this->newsArr[$i]);
			}

			$this->createNewsList();
		}
	
		public function getSingleNews() {
			global $link;
			
			if($link) {
				// Меняем формат даты
				/*$dateArr = explode('-', $this->newsDate);
				
				if(strlen((string)$dateArr[0]) > 2) {
					$this->newsDate = $dateArr[2].'-'.$dateArr[1].'-'.substr($dateArr[0], -2, 2);
				}
				
				$this->newsDate = implode('-', $dateArr);*/
				
				return $this->getSingleDbNews($this->pageNum);
			}
			else {
				echo "<h1>Такой новости не существует!</h1>";
				echo "<a href='index.php?pages=news&page=$this->pageNum'>К новостям</a>";
				return;
			}
		}
		
		private function getSingleDbNews($pageNum) {
			global $link;
			$query = "SELECT news_date, news_header, news_text FROM news WHERE news_date = '$this->newsDate'";
			$res = pg_query($link, $query) or die('Query error: '. pg_last_error());
			$news = pg_fetch_assoc($res);
			
			if(!$news) {
				echo "<h1>Такой новости не существует!</h1>";
				echo "<a href='index.php?pages=news&page=$pageNum'>К новостям</a>";
				return;
			}
			
			echo "<strong><a href='index.php?pages=news&page=$pageNum'>К новостям</a></strong>";
			$newsFull = file_get_contents('content/templates/news_full.php');
			$newsFull = str_replace(['newsDate', 'newsText'], [$news['news_date'], "<h4>".$news['news_header']."</h4>".$news['news_text']], $newsFull);
			echo $newsFull;
		}
	
		protected function createExceptNews($news) {
			$news['news_header'] = exceptStr(strip_tags($news['news_header']));
			$newsTemplate = file_get_contents('content/templates/news_template.php');
			$newsTemplate = str_replace(['newsDate', 'newsText', 'newsUrl'], [$news['news_date'], $news['news_header'], $news['news_date']], $newsTemplate);

			return $newsTemplate;
		}
		
		protected function sortNews() {
			for($i=1; $i<$this->totalNews; $i++) {					
				for($j=$i-1; $j>=0; $j--) {
					if($this->newsArr[$j]['news_date'] < $this->newsArr[$j+1]['news_date']) {
						$temp = $this->newsArr[$j+1];
						$this->newsArr[$j+1] = $this->newsArr[$j];
						$this->newsArr[$j] = $temp;
					}
				}
			}
		}
	}
	
	class OldNewsClass extends NewsClass {
		public function getNews() {
			$allNews = file_get_contents("content/news/archive_news.html");
			$allNews = strip_tags($allNews, '<p><strong><a>');
			$this->createNewsList($allNews);
		}
		
		public function getSingleNews() {
			if(file_exists('content/news/'.$this->newsDate.'.html')) {
				return $this->getSingleOldNews();
			}
			else {
				echo "<h1>Такой новости не существует!</h1>";
				echo "<a href='index.php?pages=news&page=$this->pageNum'>К новостям</a>";
				return;
			}
		}
		
		protected function createNewsList($oldNews) {
			$dom = new DOMDocument;
			$oldNews = mb_convert_encoding($oldNews, 'HTML-ENTITIES', "UTF-8");
			$dom->loadHTML($oldNews);		
			$p_elems = $dom->getElementsByTagName('p');
			$this->totalNews = $p_elems->length;
			
			// делаем ссылки на страницы списка
			$list = getULlist($this->totalNews, OLDNEWS_MAXCOUNT, 'index.php?pages=news&custom-news-date=all-old&page=', $this->pageNum);
			
			// новостей мало, список не делаем
			if($this->totalNews <= OLDNEWS_MAXCOUNT) {
				echo $oldNews;
				return;
			}
			
			// переносим DOM-элементы в новый документ и выводим его
			$dom2 = new DOMDocument;
			
			for($i = $this->pageNum*OLDNEWS_MAXCOUNT; $i>($this->pageNum*OLDNEWS_MAXCOUNT-OLDNEWS_MAXCOUNT); $i--) {
				if($p_elems->item($i-1) !== null) {
					$node = $dom2->importNode($p_elems->item($i-1), true);
					if(!$dom2->hasChildNodes()) {
						$dom2->appendChild($node);
						$firstChild = $dom2->firstChild;
						continue;
					}
					// вывод по убыванию даты
					$firstChild->parentNode->insertBefore($node, $firstChild);
					$firstChild = $dom2->firstChild;
				}
			}
			
			echo $list, $dom2->saveHTML(), $list;
		}
		
		private function getSingleOldNews() {		
			echo "<strong><a href='index.php?pages=news&custom-news-date=all-old&page=$this->pageNum'>К новостям</a></strong>";
			echo "<script>document.addEventListener('DOMContentLoaded', function() { changeStyle(); }, false);</script>";
			
			return $this->adaptOldNews(file_get_contents('content/news/'.$this->newsDate.'.html'));
		}
		
		private function adaptOldNews($newsToAdapt) {
			if(!mb_detect_encoding($newsToAdapt, "UTF-8", true)) {
				$newsToAdapt = mb_convert_encoding($newsToAdapt, "UTF-8", "windows-1251");
			}
			// ошибки в коде старых новостей
			$pattern = [
				'materials',
				'Фонд Жить и Помнить',
				'Новости фонда ЖИТЬ И ПОМНИТЬ',
				'<img src="../images/m1.gif" width="100%" height="28" border="0" />',
				'<img src="../images/m2.gif" width="100%" height="21" border="0" />',
				'style=padding-top: 10""',
				'style=padding-top:10""',
				'style=padding-top:10"',
				'style=padding-top: 10"',
				'style=padding-top: 10 ""',
				'"padding-left: 25; padding-right: 25; padding-top: 0; padding-bottom: 25""'
			];
			$replacement = array_fill(0, 11, '');
			$replacement[0] = 'content/news/materials';
			$adaptedNews = str_replace($pattern, $replacement, $newsToAdapt);
			$adaptedNews = strip_tags($adaptedNews, '<h1><h2><h3><p><strong><a><img><ol><ul><li>');

			return $adaptedNews;
		}
	}
	
	class OtherNewsClass extends NewsClass {
		public function getNews() {
			$dir = "content/news/";
			$dirNews = scandir($dir);
			echo "<h4>Новости из текстовых файлов</h4>";
			
			foreach($dirNews as $news) {
				$news_path = $dir.$news;
				if(file_exists($news_path) && is_file($news_path)) {
					if(substr($news_path, -3, 3) == 'txt') {
						$this->newsArr[] = unserialize(clearStr(file_get_contents($news_path)));
					}
				}
			}
			$this->totalNews = count($this->newsArr);
			$this->sortNews(false);
			
			for($i=0; $i<$this->totalNews; $i++) {
				$this->newsArr[$i] = $this->createExceptNews($this->newsArr[$i], false);
			}
		}
		
		public function getSingleNews() {
			if(file_exists('content/news/'.$this->newsDate.'.txt')) {
				return $this->getSingleModernNews();
			}
			else {
				echo "<h1>Такой новости не существует!</h1>";
				echo "<a href='index.php?pages=news&page=$this->pageNum'>К новостям</a>";
				return;
			}
		}
		
		private function adaptOtherNews($news) {
			unset($news[2]); // TODO: некрасиво
			$newsKeys = ['newsDate', 'newsText'];
			$news = array_combine($newsKeys, $news);
			$newsFull = file_get_contents('content/templates/news_full.php');
			$newsFull = replaceTemplateTags($newsFull, $news);
			
			return $newsFull;
		}
		
		private function getSingleOtherNews() {
			echo "<strong><a href='index.php?pages=news&page=$this->pageNum'>К новостям</a></strong>";
		
			return $this->adaptModernNews(unserialize(file_get_contents('content/news/'.$this->newsDate.'.txt')));
		}
	}
?>