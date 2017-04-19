<?
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../functions/admin_functions.php");

	function getPublsToTable() {
		global $db;
		
		$query = 'SELECT * FROM publs ORDER BY publs_id';
		$res = $db->executeQuery($query, null, null);
		
		$publsArr = [
			0 => 'publs_id',
			1 => 'publs_header',
			2 => 'publs_text',
		];
		
		while($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$i = 0;
			echo '<tr>';
			foreach($row as $val) {
				switch($i) {
					case 0: echo '<td name='.$publsArr[$i].'>' . $val . '</td>'; break;
					case 1: echo '<td name='.$publsArr[$i].'>' . $val . '</td>'; break;
					case 2: echo '<td name='.$publsArr[$i].'>' . $val . '</td>'; break;
					default: break;
				}
				$i++;
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
	<script src="../../scripts/tinymce/tinymce.min.js"></script>
	<script src="../scripts/admin_script.js"></script>
	<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
</head>
<body>
	<h1>Управление публикациями</h1>
	<a href="/admin">Назад к админке</a>
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