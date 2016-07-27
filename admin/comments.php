	<?
		if(session_status() !== PHP_SESSION_ACTIVE) {
			session_start();
		}	
	?>
	<div class='comments-wrapper'>
		<? 
			include "comments_list.php"; 
			include "comments_form.php";
		?>
	</div>