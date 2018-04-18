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
		language: 'ru',
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
				editBtnHandler.call(this, parent, prevNode, pattern);
			}
			else if(this.innerHTML.indexOf('Сохранить') != -1) {
				saveBtnHandler.call(this, prevNode, pattern);
			}
		}, false);
	}
}

// Вспомогательные функции для редактирования и сохранения
function editBtnHandler(parent, prevNode, pattern) {
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

function saveBtnHandler(prevNode, pattern) {
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
	var data = "";
	
	for(var i=0, len=inputs.length; i<len; i++) {
		var name = inputs[i].getAttribute('name');
		var inputData = encodeURIComponent(inputs[i].value);
		switch(name) {
			case 'NEWS': data += "NEWS=" + encodeURIComponent(inputData) + "&"; break;
			case 'OLDNEWS': data += "OLDNEWS=" + encodeURIComponent(inputData) + "&"; break;
			case 'PUBLS': data += "PUBLS=" + encodeURIComponent(inputData) + "&"; break;
			case 'PRESS': data += "PRESS=" + encodeURIComponent(inputData) + "&"; break;
			case 'LOGS': data += "LOGS=" + encodeURIComponent(inputData); break;
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

/*
	Функция посылает AJAX запрос на получение списка букв в Книги Памяти
*/
function getPersonsLetters() {
	var request = new XMLHttpRequest();

	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			(request.status != 200) 
			? console.log('func: getPersonsLetters; Ошибка: ' + request.responseText)
			: console.log('func: getPersonsLetters; Запрос отправлен. Все - хорошо.');
			
			var form = document.getElementsByClassName('letters-form')[0];
			var select = form.getElementsByTagName('select')[0];
			var options = select.getElementsByTagName('option');
			
			if(select.getAttribute('name') === 'select-letter') {
				var result = request.responseText;
				var resultObject = null;
				
				if(typeof result === 'string') {
					try {
						resultObject = JSON.parse(result);
					}
					catch(e) {
						console.log('func: getPersonsLetters; Пришла не JSON строка: ' + e.toString());
					}
				}
				
				if(resultObject !== null) {
					for(var i=0, len=options.length; i<len-1; i++) {
						options[i].innerHTML = resultObject[i]['letter_sign'];
						options[i].value = resultObject[i]['letter_id'];
						console.log("DATA: " + resultObject[i]['letter_sign'] + ':' + resultObject[i]['letter_id']);
					}
				}
				else {
					console.log('func: getPersonsLetters; output: ' + result);
				}
			}
		}
	};
	
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	
	setTimeout(function() {
		request.open('GET', 'get_persons_letters.php', true);
		request.send();
	}, 1500);
}

document.addEventListener('DOMContentLoaded', addNavigationToList, false);

// добавляет событие по клику на нумерацию
function addNavigationToList() {
	var ul = getElems(['elems-list']);
	
	if(!ul) return;
	
	for(var i=0, len=ul.length; i<len; i++) {
		ul[i].addEventListener('mouseup', navigateUlList, false);
	}
}

// создаем нумерованный список для навигации по материалам
function navigateUlList(e) {
	var target = e.target;
	
	if(target === e.currentTarget) {
		e.stopPropagation();
		return;
	}
	
	// рабиваем часть URL по параметрам
	var urlArr = decodeURIComponent(location.search.substr(1)).split('&');
	var pair, urlParams = {};
	
	// запоминаем параметры и их значения
	for(var i=0, len=urlArr.length; i<len; i++) {
		pair = urlArr[i].split("=");
		urlParams[pair[0]] = pair[1];
	}
	
	var pageNum = getUrlParam('page', urlParams);
	
	// одна страница есть всегда
	if(!pageNum) pageNum = 1;

	// самый первый/последний элемент списка
	if(target === this.firstChild || target === this.lastChild) {
		var listNav = target.textContent;
		// идем назад
		if(listNav.indexOf("«") !== -1) {	// ES6: listNav.includes("«"), no IE support
			console.log("Назад: " + listNav);

			if(pageNum != 1) {
				urlParams['page'] = --pageNum;
				urlArr = [];
			}
		}
		// идем вперед
		else if(listNav.indexOf("»") !== -1) {	// ES6: listNav.includes("»"), no IE support
			console.log("Вперед: " + listNav);

			if(pageNum != this.children.length-2) {
				urlParams['page'] = ++pageNum;
				urlArr = [];
			}
		}
		// создаем новый URL и открываем его
		for(var elem in urlParams) {
			urlArr.push(elem + "=" + urlParams[elem]); 
		}
		location.search = urlArr.join('&');
	}
}