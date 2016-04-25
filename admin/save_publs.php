<?
	require_once "session.inc.php";
	require_once "secure.inc.php";
	require_once "../functions/functions.php";
	global $link;
	$link = connectToPostgres();
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		if($link) {
			$header = clearStr($_POST['publs-header']);
			$text = clearStr($_POST['publs-text']);
			$query = "INSERT INTO publs (publs_header, publs_text)
					  VALUES ('$header', '$text')";
			$result = pg_query($link, $query) or die('Query error: '. pg_last_error());
			
			if($result === false) echo 'Публикация не была добавлена';
			else echo 'Публикация была добавлена';			
		}
		else {
			echo "Тут могла быть ваша Публикация.";
		}
	}
	else echo "Ничего не было передано...";
?>