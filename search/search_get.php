<?
	if($_SERVER['REQUEST_METHOD'] === 'GET') {
		$config = parse_ini_file(__DIR__.'/../config.ini');
		$connectStr = "host=".$config['host'].
					  " port=".$config['port'].
					  " dbname=".$config['dbname'].
					  " user=".$config['user'].
					  " password=".$config['password'];
		$link = pg_connect($connectStr);
		if($_GET['search']) {
			$search = $_GET['search'];
			$query = "SELECT person_id, 
					 (SELECT letter_sign FROM letters WHERE letter_id = person_letter), 
					 person_lastname, person_firstname, person_middlename, person_about, person_photo 
					 FROM persons 
					 WHERE to_tsvector(person_lastname  || ' ' || 
									   person_about     || ' ' || 
									   person_firstname || ' ' || 
									   person_middlename) @@ plainto_tsquery('$search')";
		}
		else {
			$query = "SELECT person_id, 
					 (SELECT letter_sign FROM letters WHERE letter_id = person_letter), 
					 person_lastname, person_firstname, person_middlename, person_about, person_photo 
					 FROM persons";
		}
		$result = pg_query($link, $query) or die("ERROR: ".pg_last_error());
			
		if($result) {
			while($row = pg_fetch_assoc($result)) {
				echo "<tr><th>#</th><th>Буква</th><th>Фамилия</th><th>Имя</th><th>Отчество</th></tr>";
				echo "<tr>";
				$i = 0;
				
				foreach($row as $val) {
					switch($i) {
						case 0: echo "<td>". $val ."</td>"; break;
						case 1: echo "<td>". $val ."</td>"; break;
						case 2: echo "<td>". $val ."</td>"; break;
						case 3: echo "<td>". $val ."</td>"; break;
						case 4: echo "<td>". $val ."</td>"; break;
						case 5: 
							echo '</tr><tr><th colspan="3">Текст</th><th colspan="2">Фото</th></tr>';
							echo '<tr><td colspan="3">'. $val .'</td>'; 
							break;
						case 6: 
							if($val !== null) echo '<td colspan="2"><img src="'. $val .'" alt="" ></td>';
							else echo '<td colspan="2">Без фотографии</td>';
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
		echo "Запрос не GET";
	}
?>