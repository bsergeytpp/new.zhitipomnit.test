<?
	require_once "../functions/functions.php";
	require_once "session.inc.php";
	require_once "secure.inc.php";

	function getNewsToTable() {
		global $link;
		$link = connectToPostgres();
		
		$query = 'SELECT * FROM news ORDER BY news_id';
		$res = pg_query($link, $query) or die('Query error: '. pg_last_error());
		
		$newsArr = [
			0 => 'news_id',
			1 => 'news_date',
			2 => 'news_header',
			3 => 'news_text'
		];
		
		while($row = pg_fetch_assoc($res)) {
			$i = 0;
			echo '<tr>';
			foreach($row as $val) {
				switch($i) {
					case 0: echo '<td name='.$newsArr[$i].'>' . $val . '</td>'; break;
					case 1: echo '<td name='.$newsArr[$i].'>' . $val . '</td>'; break;
					case 2: echo '<td name='.$newsArr[$i].'>' . $val . '</td>'; break;
					case 3: echo '<td name='.$newsArr[$i].'>' . $val . '</td>'; break;
					default: break;
				}
				$i++;
			}
			echo '</tr>';
			echo '<tr>';
			echo '<td class="edit-btn" colspan="3" style="cursor: pointer;"><strong>Редактировать</strong></td>';
			echo '<td class="delete-btn" colspan="1" style="cursor: pointer;"><strong>Удалить</strong></td>';
			echo '</tr>';
		}
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Управление новостями</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../scripts/tinymce/tinymce.min.js"></script>
	<script src="scripts/admin_script.js"></script>
	<link type="text/css" rel="StyleSheet" href="styles/admin_styles.css" />
</head>
<body>
	<h1>Управление новостями</h1>
	<a href="../admin">Назад</a>
	<h3>Доступные действия:</h3>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Date</th>
			<th>Header</th>
			<th>Text</th>
		</tr>
		<? getNewsToTable(); ?>
	</table> 
	<script> editBtnOnClick('news'); </script>
	<script>
		var table = document.getElementsByTagName('table')[0];
		table.addEventListener('click', function(e) {
			var target = e.target;
			
			if(target.tagName !== 'TD') return;
			
			removeSelection(table);
			target.classList.add('selected');
		}, false);
	</script>
</body>
</html>