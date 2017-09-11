<?
	require_once (__DIR__."/../functions/admin_functions.php");
	require_once (__DIR__."/../admin_security/secure.inc.php");
	require_once (__DIR__."/../admin_security/session.inc.php");

	if($_SERVER['REQUEST_METHOD'] === 'POST') {
		if($_POST['json']) {
			$json = json_decode($_POST['json'], true);
			echo "<div class='success-message'>Пришла JSON строка</div>";
			
			$config = parse_ini_file(__DIR__.'/../config.ini');
			$connectStr = "host=".$config['host'].
						  " port=".$config['port'].
						  " dbname=".$config['dbname'].
						  " user=".$config['user'].
						  " password=".$config['password'];
			
			$link = pg_connect($connectStr);
			
			$lastname = $json['lastname'];
			
			if($lastname === null) break;
			
			$firstname = $json['firstname'];
			$middlename = $json['middlename'];
			$about = $json['text'];
			$letter = $json['letter'];
			
			$query = "INSERT INTO persons (person_lastname, person_firstname, person_middlename, person_about, person_letter)
					  VALUES ($1, $2, $3, $4, (SELECT letter_id FROM letters WHERE letter_sign = $5))";
			
			$result = pg_prepare($link, 'add_persons', $query) 
				or die("ERROR: ".pg_last_error());
			$result = pg_execute($link, 'add_persons', array($lastname, $firstname, $middlename, $about, $letter)) 
				or die("ERROR: ".pg_last_error());
			
			if($result) {
				echo "<div class='success-message'>Данные добавлены</div>";
			}
			else {
				echo "<div class='error-message'>Ошибка добавления</div>";
			}
		}
		else echo "<div class='error-message'>Ошибка данных</div>";
	}
	else echo "<div class='error-message'>Ошибка запроса</div>";
?>