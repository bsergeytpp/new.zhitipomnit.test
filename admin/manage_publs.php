<?
	require_once "../functions/functions.php";
	require_once "session.inc.php";
	require_once "secure.inc.php";

	function getPublsToTable() {
		global $link;
		$link = connectToPostgres();
		
		$query = 'SELECT * FROM publs';
		$res = pg_query($link, $query) or die('Query error: '. pg_last_error());
		
		while($row = pg_fetch_assoc($res)) {
			echo '<tr>';
			foreach($row as $val) {
				echo '<td>' . $val . '</td>';
			}
			echo '</tr>';
			echo '<tr>';
			echo '<td class="edit-btn" colspan="2" style="cursor: pointer;"><strong>Редактировать</strong></td>';
			echo '<td class="delete-btn" style="cursor: pointer;"><strong>Удалить</strong></td>';
			echo '</tr>';
		}
	}
	
?>
<!DOCTYPE html>
<html>
<head>
	<title>Управление публикациями</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../scripts/tinymce/tinymce.min.js"></script>
	<script src="scripts/admin_script.js"></script>
</head>
<body>
	<h1>Управление публикациями</h1>
	<h3>Доступные действия:</h3>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Header</th>
			<th>Text</th>
		</tr>
		<? getPublsToTable(); ?>
	</table> 
	<script> editBtnOnClick('publs'); </script>
</body>
</html>