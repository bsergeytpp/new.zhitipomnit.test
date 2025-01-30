		<?
			//require_once "admin_security/session.inc.php";
		?> 
		<div class="comments-list-div">
			<h2>Комментарии:</h2>
			<? 
				require_once(__DIR__.'/../functions/functions.php');
				
				if(isset($_GET['id'])) {
					getComments($_GET['id']); 
				}
				else if(isset($_GET['comments-location-id'])) {
					getComments($_GET['comments-location-id']); 
				}
			?>
		</div>