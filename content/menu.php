			<div class="nav">
                <ul>
                    <li><a href="index.php?pages=news">Новости</a></li>
                    <li><a href="index.php?pages=about">О фонде</a></li>
                    <li><a href="index.php?pages=publ">Публикации</a></li>
                    <li><a href="index.php?pages=contacts">Контакты</a></li>
                    <li><a href="index.php?pages=press">Газета</a></li>
                    <li><a href="index.php?pages=mail">Наша почта</a></li>
                    <li><a href="#">Карты Калиниского фронта</a></li>
                    <li><a href="index.php?pages=memory">Книга Памяти</a></li>
                    <li>
						<form action="index.php" method="get">
							Открыть новость от</i>
							<input type="hidden" name="pages" value="news">
							<input type="date" name="custom-news-date" required pattern="[0-9]{2}-[0-9]{2}-[0-9]{2}">
							<input type="submit" value="Открыть">
						</form>
					</li>
                </ul>
            </div>