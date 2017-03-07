                <h2>Поиск по Книге Памяти</h2>
				<div class="search">
					<form class="search-form">
						<input class="search-textarea" type='textarea'></input>
						<input class="search-submit" type='submit'></input>
					</form>
				</div>
				<table></table>
				<?
					
				?>
                <div class="clear-div"></div>
				<script>
					var table = document.getElementsByTagName('table')[0];
					var divs = document.getElementsByTagName('div');

					// из базы данных в таблицу
					getTrsFromDB();
					
					function getTrsFromDB(search) {
						var req = new XMLHttpRequest();
						req.onreadystatechange = function() {
							if(req.readyState == 4) {
								if(req.status === 200) {
									//console.log("SERVER: "+ req.responseText);
									var trs = req.responseText;
									table.innerHTML = trs; 
								}
								else {
									console.log("ERROR: "+ req.responseText);
								}
							}
						};

						if(search) {
							req.open('GET', '/search/search_get.php?search='+search, true);
						}
						else req.open('GET', '/search/search_get.php', true);
						req.send();
					}
					
					var form = document.getElementsByClassName('search-form')[0];
					var submit = form.getElementsByClassName('search-submit')[0];
					
					submit.onclick = function(e) {
						var target = e.target;
						console.log('Отправили запрос');
						var search = form.getElementsByClassName('search-textarea')[0].value;
						getTrsFromDB(search);
						return false;
					};
					
				</script>