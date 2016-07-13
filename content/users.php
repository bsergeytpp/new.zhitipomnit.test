	<div class="users-div">
		<div class="users-info">
			<ul>
				<li>
					<? 
						if($_SESSION['user']) {
							echo "Логин: ".$_SESSION['user'];
						}
						else {
							echo "Вы не авторизованы";
						}
					?>
				</li>
			</ul>
		</div>
		<div class="users-actions">
			<? 
				if($_SESSION['user']) {
					echo "<a href='../users/user_profile.php'>Профиль</a>";
					echo "<a href='../users/login.php?logout'>Выйти</a>";
				}
				else {
					echo "<a href='../users/login.php'>Войти</a>";
				}
			?>
		</div>
		<div class="users-switcher"> >> </div>
	</div>