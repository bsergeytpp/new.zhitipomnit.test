<?
	require_once "../functions/functions.php";
	require_once "session.inc.php";
	require_once "secure.inc.php";

	function getNewsToTable() {
		global $link;
		$link = connectToPostgres();
		
		$query = 'SELECT * FROM news';
		$res = pg_query($link, $query) or die('Query error: '. pg_last_error());
		
		while($row = pg_fetch_assoc($res)) {
			echo '<tr>';
			foreach($row as $val) {
				echo '<td>' . $val . '</td>';
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
</head>
<body>
	<h1>Управление новостями</h1>
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
</body>
</html>