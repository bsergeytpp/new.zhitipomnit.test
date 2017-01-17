'use strict';
/*Passive event listeners for Blink*/
var supportsPassive = false;

try {
	var opts = Object.defineProperty({}, 'passive', {
		get: function() {
			supportsPassive = true;
		}
	});
	window.addEventListener("test", null, opts);
} catch (e) {}

function addEventListenerWithOptions(target, type, handler, options) {
	var optionsOrCapture = options;
	
	if (!supportsPassive) {
		optionsOrCapture = options.capture;
	}
	
	if(target.length !== undefined) {
		for(var i=0; i<target.length; i++) {
			target[i].addEventListener(type, handler, optionsOrCapture);
		}
	}
	else target.addEventListener(type, handler, optionsOrCapture);
}

addEventListenerWithOptions(document, "touchstart", function(e) {}, {passive: true} );
addEventListenerWithOptions(document, "touchmove", function(e) {}, {passive: true} );
addEventListenerWithOptions(document, "touchend", function(e) {}, {passive: true} );
addEventListenerWithOptions(document, "wheel", function(e) {
	//var respTime = performance.now() - e.timeStamp;
	//DEBUG("event on wheel; respTime: " + respTime);
	
}, {passive: true} );

/***********************/

var _DEBUG = true;

// общая функция-событие на прокрутку
/*
	- меняет ширину блока с контентом
	- показывает/скрывает кнопку прокрутки
*/
addEventListenerWithOptions(document, 'scroll', function(e) {
    var article = document.getElementsByClassName('article')[0];
    var scrollBtn = document.getElementsByClassName('scroll-button')[0];
	var articleWidth = window.getComputedStyle(article).getPropertyValue('width');
    //var header = document.getElementsByClassName('header')[0];
	if((window.pageYOffset || document.documentElement.scrollTop) > 550) {
		if(articleWidth !== '100%') {
		   article.style.marginLeft = 0;    
		   scrollBtn.classList.add("scroll-button-active");
		}
	}
	else {
		article.style.marginLeft = "";
		scrollBtn.classList.remove("scroll-button-active");
	}
	
	// делаем элементы при прокрутке ненажимаемыми
	var timer, body = document.body;
	clearTimeout(timer);
	
	if(!body.classList.contains('disable-hover')) {
		body.classList.add('disable-hover');
	}
	
	timer = setTimeout(function() {
		body.classList.remove('disable-hover');
	}, 500);
}, {passive: true});

// добавляет событие по клику на нумерацию
function addNavigationToList() {
	var ul = document.getElementsByClassName('news-list');
	
	if(!ul) return;
	
	for(var i=0; i<ul.length; i++) {
		ul[i].addEventListener('click', navigateUlList, false);
	}
}

addEventListenerWithOptions(document, 'DOMContentLoaded', addNavigationToList, {passive: true});

// ищем родителя элемента
function findParent(child, parentClass) {
	var parent = child.parentNode;

	while(parent && parent.parentNode) {
		parent = parent.parentNode;
		if(parent.classList.contains(parentClass)) return parent;
		if(parent.tagName === 'BODY') return null;
	}
	
	return null;
}

// мини-профиль на основном сайте
function userSwitcher() {
	var usersDiv = document.getElementsByClassName('users-div')[0];
	var switcher = usersDiv.getElementsByClassName('users-switcher')[0];
	
	switcher.addEventListener('click', function(e) {
		var style = window.getComputedStyle(usersDiv);
		var left = parseInt(style.getPropertyValue('left'));
		if(left < 0) {
			usersDiv.style.left = 0;
			usersDiv.style.color = 'white';
		}
		else usersDiv.style.left = '';
	}, false);
}

addEventListenerWithOptions(document, 'DOMContentLoaded', userSwitcher, {passive: true});

// делаем ссылку на профиль автора комментария
function addLinksToCommentsId() {
	var commentsTable = document.getElementsByClassName('comments-table');
	
	if(commentsTable.length || commentsTable) {
		for(var j=0; j<commentsTable.length; j++) {
			var trs = commentsTable[j].getElementsByTagName('TR');
			
			for(var i=0; i<trs.length; i++) {
				if(trs[i].classList.contains('comments-respond') ||
				   trs[i].classList.contains('comments-edit')) continue;
							
				var loginTd = trs[i].getElementsByTagName('TD');
				
				if(!loginTd[2]) continue;	// TD с ником автора
				
				DEBUG("func: addLinksToCommentsId; output: loginTd: "+ loginTd[2].innerHTML);
				var userLogin = loginTd[2].innerHTML;
				var commId = loginTd[0].innerHTML;
				loginTd[0].innerHTML = '<a href="../users/user_profile.php?user_login='+userLogin+'">'+commId+'</a>';
			}
		}
	}
}

addEventListenerWithOptions(document, 'DOMContentLoaded', addLinksToCommentsId, {passive: true});

// инициализируем элемент TinyMCE
function initTinyMCE(className, isInline, width, height) {
	if(!className) return;
	
	if(!isInline) isInline = false;
	
	if(!width) width = 400;
	
	if(!height) height = 170;

	tinymce.init({
		inline: isInline,
		selector: className,
		language: 'ru_RU',
		plugins: 'code',
		paste_data_images: true,
		width: width,
		height: height
	});
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
	var pair, urlParams = new Object;
	
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
		// идем назад
		if(target.innerHTML.indexOf("«") !== -1) {
			DEBUG("func: navigateUlList; output: Назад: " + target.innerHTML);

			if(pageNum != 1) {
				urlParams['page'] = --pageNum;
				urlArr = [];
			}
		}
		// идем вперед
		else if(target.innerHTML.indexOf("»") !== -1) {
			DEBUG("func: navigateUlList; output: Вперед: " + target.innerHTML);

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

function getUrlParam(value, obj) {
	for(var param in obj) {
		if(param == value) {
			return obj[param];
		}
	}
	
	return false;
}

// функция для исправления ссылок в общем списке старых новостей
function replaceNewsLinks() {
	var container = document.body.getElementsByClassName('article')[0];
	var parents = container.getElementsByTagName('P');
	
	for(var i=0, len=parents.length; i<len; i++) {
		var link = parents[i].getElementsByTagName('A')[0];
		var linkHref = link.getAttribute('href').substring(0, link.getAttribute('href').length - 5); // (length - 5) -> .html
		link.setAttribute('href', 'index.php?pages=news&type=old&custom-news-date=' + linkHref);
		DEBUG('func: replaceNewsLinks; output: ' + link.getAttribute('href'));
	}
}

// функция для исправления ссылок в общем списке старых статей
function replacePressLinks() {
	var press = document.body.getElementsByClassName('article-press');
	
	for(var i=0, len=press.length; i<len; i++) {
		var str = press[i].getElementsByTagName('A')[0];
		str.setAttribute('href', 'index.php?pages=press&custom-press=' + str.getAttribute('href').substring(0, 5));
		DEBUG('func: replacePressLinks; output: ' + press[i].getAttribute('href'));
	}
}

// функция для исправления стилей старых новостей
function changeStyle() {
	document.getElementById('news-container').style.display = 'block';
}

// не используется: добавляет миниатюру к новости
function displayNewsImage() {
	var imgs = document.body.getElementsByClassName('article-news-image');
	
	for(var i=0, len=imgs.length; i<len; i++) {
		if(isFileExists(imgs[i].getAttribute('src'))) {
			imgs[i].style.display = 'block';
		}
		else imgs[i].style.display = 'none';
	}
}

// проверяем есть ли файл
function isFileExists(url) {
	var http = new XMLHttpRequest();
	http.open('HEAD', url, true);
	http.send();
	return http.status != 404;
}

// подключаем скрипт
function appendScript(src) {
	var script = document.createElement('script');
	script.src = src;
	document.body.appendChild(script);
}

// делаем дерево комментариев
function makeCommentsTree() {
	var comm_tables = document.getElementsByClassName('comments-table');
	
	for(var i=0; i<comm_tables.length; i++) {
		var tr = comm_tables[i].getElementsByTagName('tr')[1];
		var id = tr.firstChild.innerHTML;
		var parent_id = tr.children[1].innerHTML;
		
		if(parent_id !== '') {
			DEBUG('func: makeCommentsTree; output: Parent: '+parent_id);
			
			for(var j=0; j<comm_tables.length; j++) {
				var temp_tr = comm_tables[j].getElementsByTagName('tr')[1];
				var temp_id = temp_tr.firstChild.firstChild.innerHTML;
				//DEBUG('func: makeCommentsTree; output: Current id: '+temp_id);
				
				if(temp_id == parent_id) {
					DEBUG('func: makeCommentsTree; output: Parent is found: '+comm_tables[j]);
					comm_tables[j].parentNode.appendChild(comm_tables[i].parentNode);
				}
			}
		}
		else if(parent_id === '') {
			comm_tables[i].parentNode.style.width = '100%';
		}
	}
}

addEventListenerWithOptions(document, 'DOMContentLoaded', makeCommentsTree, {passive: true});

// при клике на кнопку "Ответить" записываем в скрытое поле формы комментирования ID комментария
// ссылаемся на ID в тексте кнопки "Ответ"
function setCommentsParentId(e) {
	var target = e.target;
	
	if(target.className !== 'respond-button') return;
	
	var parent = target.parentNode; 									// TD - родитель кнопки "Ответить"
	
	while(parent.tagName !== 'TABLE') {
		if(parent.className === 'comments-respond') break;				// ищем TR с классом 'comments-respond'
		parent = parent.parentNode;
	}
	
	e.preventDefault();

	var parentLink = parent.previousSibling.getElementsByTagName('A')[0];
	var parentId = parentLink.innerHTML; 								// TR -> TR>A>textNode (ID комментария, на который отвечаем)
	var parentAuthor = parentLink.getAttribute('href'); 				// автор комментария, на который отвечаем
	parentAuthor = parentAuthor.substr(parentAuthor.indexOf('=')+1);	// только ник
	var commentsInput = document.getElementsByClassName('comments-form')[0].elements['comments-parent'];

	if(commentsInput.tagName !== 'INPUT') return;
	
	commentsInput.value = parentId;
	
	var postBtn = document.getElementsByClassName('comments-post-button')[0];
	postBtn.value = 'Ответ сообщению '+parentId+' за авторством '+parentAuthor;
	postBtn.focus();
}

addEventListenerWithOptions(document.getElementsByClassName('respond-button'), 'click', setCommentsParentId, {});

// добавляем комментарии без перезагрузки страницы
function addCommentsAjax(commentsForm) {
	var text = tinymce.activeEditor.getContent();
	var login = commentsForm.elements['comments-login'].value;
	var parentId = commentsForm.elements['comments-parent'].value;
	var location = window.location;
	
	if(parentId === '') parentId = '';			// ???
	
	var data = "comments-text=" + encodeURIComponent(text) + "&" +
			   "comments-login=" + encodeURIComponent(login) + "&" +
			   "comments-parent=" + encodeURIComponent(parentId) + "&" +
			   "location=" + encodeURIComponent(location);
	var request = new XMLHttpRequest();
	
	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			
			(request.status != 200) 
			? DEBUG('func: addCommentsAjax; Ошибка: ' + request.responseText)
			: DEBUG('func: addCommentsAjax; Запрос отправлен. Все - хорошо. Ответ сервера: '+request.responseText);
		}
	};
	
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	
	request.open('POST', 'admin/save_comments.php', true);
	request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	request.send(data);
	DEBUG('func: addCommentsAjax; output: Отправили запрос');
}

addEventListenerWithOptions(document, 'click', function(e) {
	var target = e.target;
	if(target.className !== 'comments-post-button') return;
	e.preventDefault();
	addCommentsAjax(document.getElementsByClassName('comments-form')[0]);
	updateCommentsWrapper();
}, {});

// обновляем родительский элемент с комментариями
function updateCommentsWrapper() {
	var wrapper = document.getElementsByClassName('comments-wrapper')[0];
	var height = window.getComputedStyle(wrapper).getPropertyValue('height');
	var commentsDiv = wrapper.getElementsByClassName('comments-list-div')[0];
	
	// визульано показываем, что что-то происходит =)
	wrapper.style.height = height;
	wrapper.style.opacity = 0.5;
	
	var request = new XMLHttpRequest();
	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			(request.status != 200) 
			? DEBUG('func: updateCommentsWrapper; Ошибка: ' + request.responseText)
			: DEBUG('func: updateCommentsWrapper; Запрос отправлен. Все - хорошо.');
			
			// выключаем форму комментирования
			tinymce.EditorManager.execCommand('mceRemoveEditor', true, 'comments-text');
			// удаляем div с комментариями 
			wrapper.removeChild(commentsDiv);
			// вставляем обновленный список комментариев + старую форму
			wrapper.innerHTML = request.responseText + wrapper.innerHTML;
			// включаем обратно форму комментирования
			tinymce.EditorManager.execCommand('mceAddEditor', true, 'comments-text');
			// обнуляем стили
			wrapper.style.height = '';
			wrapper.style.opacity = '';
			
			makeCommentsTree();
			addLinksToCommentsId();
			if(window.admin) {
				admin.setPrivilege();
			}
		}
	};
	
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	
	setTimeout(function() {
		var location = window.location.origin + window.location.pathname + window.location.search;
		request.open('GET', 'admin/comments_list.php?location='+encodeURIComponent(location), true);
		request.send();
	}, 1500);
}

// добавляем к заголовку название новости/статьи
function updatePageTitle() {
	var title = document.title;
	var params = window.location.search;
	var container, header;
	
	// пропускаем старые новости/статьи
	if(params.indexOf('all-old') !== -1) {
		document.title = 'Старые ' + document.title;
		return; 						
	}
	
	// новость
	if(title === 'Новости' && params.indexOf('custom-news-date') !== -1) {
		container = document.getElementsByClassName('news-full-container')[0];
		
		// в старых новостях может не быть заголовков
		if(container === undefined) return;
		
		header = container.getElementsByTagName('H4')[0];
	}
	// статья
	else if(title === 'Статьи' && params.indexOf('custom-publ') !== -1) {
		container = document.getElementsByClassName('publs-full-container')[0];
		
		if(container === undefined) return;
		
		header = container.getElementsByTagName('H3')[0];
	}
	// газета
	else if(title === 'Газета' && params.indexOf('custom-press') !== -1) {
		container = document.getElementById('press-container');
		
		if(container === undefined) return;
		
		header = container.getElementsByTagName('H1')[0];
	}
	
	if(!header) return;
	
	// http://stackoverflow.com/questions/822452/strip-html-from-text-javascript
	header = header.innerHTML.replace(/<(?:.|\n)*?>/gm, '');	
	document.title += ' - ' + header.substr(0, 25) + '...';
}

addEventListenerWithOptions(document, 'DOMContentLoaded', updatePageTitle, {passive: true});

// Вывод отладочной информации
function DEBUG(str) {
	if(_DEBUG) {
		if(typeof str !== 'string') {
			var str = str.toString();
		}
		console.log(str);
	}
}

/* TODO 
	* innerHTML -> innerText
*/