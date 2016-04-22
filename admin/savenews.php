<?
	require_once "session.inc.php";
	require_once "secure.inc.php";
	require_once "../functions/functions.php";
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$newsArr[] = clearStr($_POST['news-date']);
		$newsArr[] = clearStr($_POST['news-text']);
		$newsArr[] = clearStr($_POST['news-date']);
		$newsStr = serialize($newsArr);

		//echo "Серилизованная новость: $newsStr";
		file_put_contents('../content/news/'.$newsArr[0].'.txt', $newsStr);
	}
	else echo "Ничего не было передано...";
?>