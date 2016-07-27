		<div class="comments-list-div">
			<h2>Комментарии:</h2>
			<? 
				require_once(__DIR__.'/../functions/functions.php');
				
				if(isset($_GET['location'])) {
					getComments($_GET['location']); 
				}
				else getComments('http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']); 
			?>
		</div>