	<div class="users-div">
		<div class="users-info">
			<ul>
				<li>
					<?
						$userLogin = (isset($_SESSION['user'])) ? $_SESSION['user'] : null;
						if($userLogin) {
							echo "Логин: ".$userLogin;
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
				if($userLogin) {
					echo "<a href='../users/user_profile.php?user_login=$userLogin'>Профиль</a>";
					echo "<a href='../users/login.php?logout'>Выйти</a>";
				}
				else {
					echo "<a href='../users/login.php'>Войти</a>";
				}
			?>
		</div>
		<div class="users-switcher"> >> </div>
	</div>