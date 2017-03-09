<?
	if(session_status() !== PHP_SESSION_ACTIVE) session_start();
	require_once "../functions/functions.php";
	global $db;
	
	if($_SERVER['REQUEST_METHOD'] === 'GET') {
		if($db->getLink()) {
			$query = "SELECT person_id, 
					  (SELECT letter_sign FROM letters WHERE letter_id = person_letter), 
					  person_lastname, person_firstname, person_middlename, person_about, person_photo 
					  FROM persons";
			
			if(isset($_GET['search'])) {
				$search = trim(strip_tags($_GET['search']));
				$search = filter_var($search, FILTER_SANITIZE_STRING);
				$query .= "	WHERE to_tsvector(person_lastname  || ' ' || 
										      person_about     || ' ' || 
										      person_firstname || ' ' || 
										      person_middlename) @@ plainto_tsquery('$search')";
			}
			
			$result = $db->executeQuery($query, null, null);
				
			if($result) {
				while($row = $result->fetch(PDO::FETCH_ASSOC)) {
					echo "<tr><th>#</th><th>Буква</th><th>Фамилия</th><th>Имя</th><th>Отчество</th></tr>";
					echo "<tr>";
					$i = 0;
					
					foreach($row as $val) {
						switch($i) {
							case 0: echo "<td class='pers-id'>". $val ."</td>"; break;
							case 1: echo "<td class='pers-letter'>". $val ."</td>"; break;
							case 2: echo "<td class='pers-lastname'>". $val ."</td>"; break;
							case 3: echo "<td class='pers-firstname'>". $val ."</td>"; break;
							case 4: echo "<td class='pers-middlename'>". $val ."</td>"; break;
							case 5: 
								echo '</tr><tr><th colspan="3">Текст</th><th colspan="2">Фото</th></tr>';
								echo '<tr><td colspan="3" class="pers-text">'. $val .'</td>'; 
								break;
							case 6: 
								if($val !== null) echo '<td colspan="2" class="pers-photo"><img src="'. $val .'" alt="" ></td>';
								else echo '<td colspan="2" class="pers-photo">Без фотографии</td>';
								break;
							default: break;
						}
						$i++;
					}
					echo "</tr>";
				}
			}
			else {
				echo "Ошибка добавления\n";
			}
		}
		else {
			echo "Соединение не установлено.";
		}
	}
	else {
		echo "Запрос не удался.";
	}
?>