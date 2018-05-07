	<div class="header">
		<div class="header-main">
		<picture>
			<source srcset="images/header-logo.png">
			<img src="images/header-logo.png" alt="">
		</picture>
		<!--<img src="images/header-logo.png" alt="">-->
		<!--<h2>Фонд "Жить и Помнить"</h2>-->
			<div class="header-links">
				<ul>
					<li><a href="index.php">Главная</a></li>
					<li><a href="index.php?pages=news">Новости</a></li>
					<li><a href="index.php?pages=about">О фонде</a></li>
					<li><a href="index.php?pages=publ">Публикации</a></li>
					<li><a href="index.php?pages=press">Газета</a></li>
					<li><a href="index.php?pages=mail">Почта</a></li>
					<li><a href="index.php?pages=search">Поиск</a></li>
					<li><a href="index.php?pages=contacts">Контакты</a></li>
					<?
						$userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
						if($userLogin) {
							echo "<li><a href='users/user_profile.php?user_login=$userLogin'>Профиль</a></li>";
							echo "<li><a href='users/login.php?logout'>Выйти</a></li>";
						}
						else {
							echo "<li><a href='users/login.php'>Войти</a></li>";
						}
					?>
				</ul>
			</div>
		</div>
	</div>