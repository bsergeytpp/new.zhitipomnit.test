/*
	Функция создания редактора TinyMCE
	- принимает 
		1) имя класса для элемента, который станет полем для редактирования 
		2) переменную определяющую будет ли редактор встроенным в поле
*/
function initTinyMCE(className, isInline) {
	tinymce.init({
		inline: isInline,
		selector: className,
		language: 'ru_RU',
		plugins: 'code',
		paste_data_images: true
	});
}

/*
	Функция для кнопки редактирования\сохранения:
	- принимает шаблон (новости, статьи, пользователи)
	- вешает на каждую кнопку по событию
	- проверяет текст кнопки и либо сохраняет изменения, либо вызывает редактор TinyMCE
*/
function editBtnOnClick(pattern) {
	var editBtns = document.getElementsByClassName("edit-btn");
		
	for(var i=0, len=editBtns.length; i<len; i++) {
		editBtns[i].addEventListener('click', function(e) {
			var target = e.target;
			
			if(target === e.currentTarget) {
				e.stopPropagation();
				return;
			}
			
			var parent = this.parentNode;
			var prevNode = parent.previousSibling;
	
			if(this.innerHTML.indexOf('Редактировать') != -1) {
				var editedArea = prevNode.getElementsByClassName('selected')[0];
				
				if(!editedArea) return;
				
				// не редактировать ID
				if(editedArea == prevNode.firstChild) return;
				
				editedArea.className = pattern + '-textarea';
				initTinyMCE('.' + pattern + '-textarea', true);
				this.innerHTML = '<strong>Сохранить</strong>';
				parent.style.background = 'lightgray';
				prevNode.style.background = 'lightgray';
			}
			else if(this.innerHTML.indexOf('Сохранить') != -1) {
				if(!checkActiveEditors(pattern + '-textarea')) {
					var updatedText = tinymce.activeEditor.getContent();
					var name = tinymce.activeEditor.getElement().getAttribute('name');
					var id = prevNode.firstChild.innerHTML;
					saveEditedText(updatedText, id, name, pattern);
				}
				else {
					for(var i=0, totalEditors=tinymce.editors.length; i<totalEditors; i++) {
						var elem = tinymce.editors[i].getElement();
						var elemParent = elem.parentNode;
						var elemId = elemParent.firstChild.innerHTML;
						var elemName = elem.getAttribute('name');
						saveEditedText(tinymce.editors[i].getContent(), elemId, elemName, pattern);
					}
				}
				document.location.reload(true);
			}
		}, false);
	}
}

/*
	Функция сохранения изменений в полях таблицы
	- принимает 
		1) отредактированный текст 
		2) id элемента 
		3) имя столбца 
		4) шаблон (новости, статьи, пользователи)
	- создает XML запрос и отправляет его на страницу update_шаблон.php
*/
function saveEditedText(text, id, name, pattern) {
	var data = "id=" + encodeURIComponent(id) + "&" +
			   "text=" + encodeURIComponent(text) + "&" +
			   "name=" + encodeURIComponent(name);
	var request = new XMLHttpRequest();
	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			
			(request.status != 200) 
			? console.log('Ошибка: ' + request.responseText)
			: console.log('Запрос отправлен. Все - хорошо.');
		}
	};
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	request.open('POST', 'update_' + pattern + '.php', true);
	request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	request.send(data);
}

/*
	Функция проверяет активные редакторы TinyMCE
	- принимает шаблон
*/
function checkActiveEditors(pattern) {
	if(tinymce.editors.length == 1) return false;
	
	return (confirm('Остались несохраненные данные. Отбросить их и сохранить только последнюю правку?')) 	
	? false
	: true;
}

function saveSettings() {
	var table = document.getElementsByClassName('settings-table')[0];
	var inputs = table.getElementsByTagName('input');
	var data = [];
	
	for(var i=0, len=inputs.length; i<len; i++) {
		var name = inputs[i].getAttribute('name');
		var inputData = encodeURIComponent(inputs[i].getAttribute('value'));
		switch(name) {
			case 'NEWS': data['NEWS'] = inputData; break;
			case 'OLDNEWS': data['OLDNEWS'] = inputData; break;
			case 'PUBLS': data['PUBLS'] = inputData; break;
			case 'PRESS': data['PRESS'] = inputData; break;
			default: break;
		}
	}
	
	sendRequest(data, 'POST', 'save_settings.php', 'application/x-www-form-urlencoded');
}

function sendRequest(data, reqType, reqTarget, contentType) {
	var request = new XMLHttpRequest();
	var reqData = '';
	
	for(var key in data) {
		reqData += '&' + key + '=' + data[key];
	}
		
	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			
			(request.status != 200) 
			? console.log('Ошибка: ' + request.responseText)
			: console.log('Запрос отправлен. Все - хорошо.');
		}
	};
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	request.open(reqType, reqTarget+'?'+reqData, true);
	request.setRequestHeader("Content-Type", contentType);
	request.send(data);
}

/*
	Функция удаляет выделение ячейки таблицы
	- принимает выделенной родителя ячейки
*/
function removeSelection(parent) {
	var selectedElems = parent.getElementsByClassName('selected');
	
	for(var i=0, len=selectedElems.length; i<len; i++) {
		selectedElems[i].classList.remove('selected');
	}
}

/*
	Функция посылает AJAX запрос на получение списка категорий логов
*/
function getLogsTypes() {
	var request = new XMLHttpRequest();

	var request = new XMLHttpRequest();
	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			(request.status != 200) 
			? console.log('func: getLogsTypes; Ошибка: ' + request.responseText)
			: console.log('func: getLogsTypes; Запрос отправлен. Все - хорошо.');
			
			var form = document.getElementsByClassName('comments-form')[0];
			var select = form.getElementsByTagName('select')[0];
			var options = select.getElementsByTagName('option');
			
			if(select.getAttribute('name') === 'log-type') {
				var result = request.responseText;
				var resultObject = null;
				
				if(typeof result === 'string') {
					try {
						resultObject = JSON.parse(result);
					}
					catch(e) {
						console.log('func: getLogsTypes; Пришла не JSON строка: ' + e.toString());
					}
				}
				
				if(resultObject !== null) {
					for(var i=0, len=options.length; i<len-1; i++) {
						options[i].innerHTML = resultObject[i]['log_type_category'];
						options[i].value = resultObject[i]['log_type_id'];
						console.log("DATA: " + resultObject[i]['log_type_category'] + ':' + resultObject[i]['log_type_id']);
					}
				}
				else {
					console.log('func: getLogsTypes; output: ' + result);
				}
			}
		}
	};
	
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	
	setTimeout(function() {
		request.open('GET', 'get_logs_type.php', true);
		request.send();
	}, 1500);
}