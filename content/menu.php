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
                    <li><a href="index.php?pages=search">Поиск по Книге Памяти</a></li>
                    <li><a href="index.php?pages=guestbook">Гостевая книга</a></li>
                    <li>
						<form action="#" class="menu-form" onsubmit="return newsSearch(this)">
							Открыть новость от
							<input type="hidden" name="pages" value="news"></input>
							<input type="date" class="menu-date" placeholder="Формат: гггг-мм-дд" name="custom-news-date" required pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}"></input>
							<input type="submit" class="menu-submit" value="Открыть"></input>
						</form>
					</li>
                </ul>
            </div>