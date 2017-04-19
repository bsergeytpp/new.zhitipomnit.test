<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");

	function getPersonsToTable() {
		global $db;
		
		$query = "SELECT person_id, person_lastname, person_firstname, person_middlename, person_about, person_photo FROM persons";
		$res = $db->executeQuery($query, null, null);
		
		$personsArr = [
			0 => 'person_id',
			1 => 'person_lastname',
			2 => 'person_firstname',
			3 => 'person_middlename',
			4 => 'person_about',
			5 => 'person_photo'
		];
		
		while($row = $res->fetch(PDO::FETCH_ASSOC)) {
			$i = 0;
			echo '<tr>';
			foreach($row as $val) {
				switch($i) {
					case 0: echo '<td name='.$personsArr[$i].'>' . $val . '</td>'; break;
					case 1: echo '<td name='.$personsArr[$i].'>' . $val . '</td>'; break;
					case 2: echo '<td name='.$personsArr[$i].'>' . $val . '</td>'; break;
					case 3: echo '<td name='.$personsArr[$i].'>' . $val . '</td>'; break;
					case 4: echo '<td name='.$personsArr[$i].'>' . $val . '</td>'; break;
					case 5: echo '<td name='.$personsArr[$i].'><img src="' . $val . '" alt=""></td>'; break;
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
	<title>Управление Книгой Памяти</title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<script src="../../scripts/tinymce/tinymce.min.js"></script>
	<script src="../scripts/admin_script.js"></script>
	<link type="text/css" rel="StyleSheet" href="../styles/admin_styles.css" />
</head>
<body>
	<h1>Управление Книгой Памяти</h1>
	<a href="/admin">Назад к админке</a>
	<h3>Доступные действия:</h3>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Фамилия</th>
			<th>Имя</th>
			<th>Отчество</th>
			<th>Текст</th>
			<th>Фото</th>
		</tr>
		<? getPersonsToTable(); ?>
	</table> 
</body>
</html>