<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/session.inc.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");

	$personLetter = '0';
	
	if($_SERVER['REQUEST_METHOD'] == 'POST') {
		$personLetter = $_POST['select-letter'];
	}
	
	function getPersonsToTable($letterId) {
		global $db;
		
		$query = "SELECT person_id, person_lastname, person_firstname, person_middlename, person_about, person_photo FROM persons";
			
		// если задана буква
		if($letterId > 0) {
			$query .= " WHERE person_letter = $letterId";
		}
		
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
	<form action="manage_persons.php" method="POST" class="letters-form">
		<p>
			<span>Искать только букву</span>
			<select name="select-letter">
				<option value="1">А
				<option value="2">Б
				<option value="3">В
				<option value="4">Я
				<option value="5">А
				<option value="6">Б
				<option value="7">В
				<option value="8">Я
				<option value="9">А
				<option value="10">Б
				<option value="11">В
				<option value="12">Я
				<option value="13">А
				<option value="14">Б
				<option value="15">В
				<option value="16">Я
				<option value="17">А
				<option value="18">Б
				<option value="19">В
				<option value="20">Я
				<option value="21">А
				<option value="22">Б
				<option value="23">В
				<option value="24">Я
				<option value="25">А
				<option value="26">Б
				<option value="27">В
				<option value="28">Я
				<option value="0" selected>All
			</select>
		</p>
		<p><input type="submit" class="letter-post-button" value="Отправить"></p>
	</form>
	<table border='1'>
		<tr>
			<th>ID</th>
			<th>Фамилия</th>
			<th>Имя</th>
			<th>Отчество</th>
			<th>Текст</th>
			<th>Фото</th>
		</tr>
		<? getPersonsToTable($personLetter); ?>
	</table> 
	<script>
		document.addEventListener('DOMContentLoaded', getPersonsLetters);
	</script>
</body>
</html>