'use strict';
addEventListenerWithOptions(document, 'DOMContentLoaded', changeNewsStyle, {});
addEventListenerWithOptions(document, 'DOMContentLoaded', addNavigationToList, {});
addEventListenerWithOptions(document, 'DOMContentLoaded', userSwitcher, {});
addEventListenerWithOptions(document, 'DOMContentLoaded', addLinksToCommentsId, {});
addEventListenerWithOptions(document, 'DOMContentLoaded', makeCommentsTree, {});
addEventListenerWithOptions(document, 'DOMContentLoaded', updatePageTitle, {});
addEventListenerWithOptions(getElems(['respond-button']), 'click', setCommentsParentId, {});
addEventListenerWithOptions(getElems(['comments-num']), 'click', openNewsComments, {});
addEventListenerWithOptions(getElems(['alt-news-comments-div']), 'click', openNewsComments, {});
addEventListenerWithOptions(document, 'mouseup', postComment, {});
addEventListenerWithOptions(document, 'mouseup', postGuestbookMessage, {});
addEventListenerWithOptions(document, "touchstart", function(e) {}, {passive: true} );
addEventListenerWithOptions(document, "touchmove", function(e) {}, {passive: true} );
addEventListenerWithOptions(document, "touchend", function(e) {}, {passive: true} );
addEventListenerWithOptions(document, "wheel", function(e) {
	//var respTime = performance.now() - e.timeStamp;
	//DEBUG('', "event on wheel; respTime: " + respTime);
}, {passive: true} );

// общая функция-событие на прокрутку
/*
	- меняет ширину блока с контентом
	- показывает/скрывает кнопку прокрутки
*/
addEventListenerWithOptions(document, 'scroll', function(e) {
	var doc = document;
    var article = getElems(['article', 0]);
	var articleWidth = window.getComputedStyle(article).getPropertyValue('width');

	if(window.innerWidth < 680) return;
	
	if((window.pageYOffset || doc.documentElement.scrollTop) > 600) {
		if(articleWidth !== '100%') {
		   article.style.marginLeft = 0;    
		}
	}
	else {
		article.style.marginLeft = "";
	}

	// делаем элементы при прокрутке ненажимаемыми
	var timer, body = doc.body;
	clearTimeout(timer);
	
	body.classList.contains('disable-hover', true);
	
	timer = setTimeout(function() {
		body.classList.remove('disable-hover');
	}, 500);
}, {});

// смена стиля новостей news-style
function changeNewsStyle() {
	var div = getElems(['news-style']);
	
	if(!div) return;

	div[0].addEventListener('mouseup', changeNews, false);
}

function changeNews(e) {
	var target = e.target;
	
	if(target.tagName !== 'IMG') return;
	
	var type = target.getAttribute('alt');
		
	if(type !== 'classic' && type !== 'alt') return;
	
	document.cookie = "newsStyle="+type;
	window.location.reload(false); 
}

// выводит последний комментарий
function postComment(e) {
	var target = e.target;
	
	if(!checkClass(target, ['comments-post-button'])) return;
	
	e.preventDefault();
	removeActiveTinymceEditors();
	
	var commentsForm = getElems(['comments-form', 0]).elements;
	var text = tinymce.activeEditor.getContent();
	var login = commentsForm['comments-login'].value;
	var parentId = commentsForm['comments-parent'].value;
	var id = commentsForm['comments-location-id'].value;
	var token = commentsForm['security-token'].value;

	if(!checkCookieToken(token)) {
		console.log("Ошибка безопасности.");
		return;
		//request.setRequestHeader("X-CSRF-TOKEN", csrfCookie[1]);
	}
	
	if(!id) {
		id = getParamFromLocationSearch('id');
	}
	
	var data = "comments-text=" + encodeURIComponent(text) + "&" +
			   "comments-login=" + encodeURIComponent(login) + "&" +
			   "comments-parent=" + encodeURIComponent(parentId) + "&" +
			   "token=" + encodeURIComponent(token) + "&" +
			   "comments-location-id=" + encodeURIComponent(id);
	
	sendAjaxRequest(data, 'admin/save_comments.php');
	updateCommentsWrapper();
	commentsForm['comments-parent'].value = "";
	target.value = "Добавить";
}

// добавляет событие по клику на нумерацию
function addNavigationToList() {
	var ul = getElems(['elems-list']);
	
	if(!ul) return;
	
	for(var i=0, len=ul.length; i<len; i++) {
		ul[i].addEventListener('mouseup', navigateUlList, false);
	}
}

// мини-профиль на основном сайте
function userSwitcher() {
	var usersDiv = getElems(['users-div', 0]);
	var switcher = getElems(['users-switcher', 0], usersDiv);
	
	if(!usersDiv || !switcher) return;
	
	switcher.addEventListener('mouseup', function(e) {
		var style = window.getComputedStyle(usersDiv);
		var left = parseInt(style.getPropertyValue('left'));
		
		if(left < 0) {
			usersDiv.style.left = 0;
			usersDiv.style.color = 'white';
		}
		else usersDiv.style.left = '';
	}, false);
}

// делаем ссылку на профиль автора комментария
function addLinksToCommentsId() {
	var commentsTable = getElems(['comments-table']);
	
	if(!commentsTable) return;
	
	for(var tableElem of commentsTable) {
		var trs = getElems(['', -1, 'TR'], tableElem);
		
		for(var trsElem of trs) {
			if(trsElem.classList.contains('comments-respond') ||
			   trsElem.classList.contains('comments-edit')) continue;
						
			var loginTd = getElems(['', -1, 'TD'], trsElem);
			
			if(!loginTd || !loginTd[2]) continue; // TD с ником автора
			
			DEBUG(addLinksToCommentsId.name, "loginTd: "+ loginTd[2].textContent);
			var userLogin = loginTd[2].textContent;
			var commId = loginTd[0].textContent;
			var profileName = loginTd[2].textContent;
			loginTd[2].textContent = "";
			var profileLink = createDOMElem({
				tagName: 'A', 
				args: [{name: 'href', value: '../users/user_profile.php?user_login='+userLogin}], 
				innerText: profileName
			});
			loginTd[2].appendChild(profileLink);
		}
	}
}

// инициализируем элемент TinyMCE
function initTinyMCE(className, isInline, width, height) {
	if(!className) return;
	
	if(!isInline) isInline = false;
	
	if(!width) width = 700;
	
	if(!height) height = 300;

	tinymce.init({
		inline: isInline,
		selector: className,
		language: 'ru',
		plugins: 'code',
		paste_data_images: true,
		width: width,
		height: height,
		license_key: 'gpl'
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
	var pair, urlParams = {};
	
	// запоминаем параметры и их значения
	for(var urlElem of urlArr) {
		pair = urlElem.split("=");
		urlParams[pair[0]] = pair[1];
	}
	
	var pageNum = getUrlParam('page', urlParams);
	
	// одна страница есть всегда
	if(!pageNum) pageNum = 1;

	// самый первый/последний элемент списка
	if(target === this.firstChild || target === this.lastChild) {
		var listNav = target.textContent;
		// идем назад
		if(listNav.indexOf("«") !== -1 && pageNum != 1) {	// ES6: listNav.includes("«"), no IE support
			DEBUG(navigateUlList.name, "Назад: " + listNav);
			urlParams['page'] = --pageNum;
		}
		// идем вперед
		else if(listNav.indexOf("»") !== -1 && (pageNum != this.children.length-2)) {	// ES6: listNav.includes("»"), no IE support
			DEBUG(navigateUlList.name, "Вперед: " + listNav);
			urlParams['page'] = ++pageNum;
		}
		
		// создаем новый URL и открываем его
		urlArr = [];
		for(var elem in urlParams) {
			urlArr.push(elem + "=" + urlParams[elem]); 
		}
		location.search = urlArr.join('&');
	}
}

// функция для исправления ссылок в общем списке старых новостей
function replaceNewsLinks() {
	var container = getElems(['article', 0]);
	var parents = getElems(['', -1, 'P'], container);
	
	if(!parents) return;
	
	for(var parentElem of parents) {
		var link = getElems(['', 0, 'A'], parentElem);
		var linkHref = link.getAttribute('href');
		
		if(!link || linkHref === '#') continue;
		
		linkHref = linkHref.substring(0, linkHref.length - 5); // (length - 5) -> .html
		link.setAttribute('href', 'index.php?pages=news&type=old&custom-news-date=' + linkHref);
		DEBUG(replaceNewsLinks.name, linkHref);
	}
}

// функция для исправления ссылок в общем списке старых газетах
function replacePressLinks() {
	var press = getElems(['article-press']);
	
	if(!press) return;
	
	for(var pressElem of press) {
		var str = getElems(['', 0, 'A'], pressElem);
		var strHref = str.getAttribute('href');
		DEBUG(replacePressLinks.name, strHref);
		str.setAttribute('href', 'index.php?pages=press&custom-press=' + strHref.substring(0, 5));
	}
}

// функция для исправления ссылок в полной газете
function replacePressPagesLinks() {
	var pressContainer = getElems('press-container');
	var pagesLinks = getElems(['', -1, 'A'], pressContainer);
	
	if(!pagesLinks) return;
	
	for(var linkElem of pagesLinks) {
		if(checkClass(linkElem, ['article-press-links'])) continue;
		
		var strHref = linkElem.getAttribute('href');
		
		// внешний домен
		if(strHref.substring(0,4) === 'http') continue;
		
		// берем ссылку на вторую страницу газеты за основу (чтобы был атрибут page)
		var newHref = getElems(['article-press-links', 1]).getAttribute('href');
		// убираем номер страницы
		newHref = newHref.substring(0, newHref.length - 1);
		linkElem.setAttribute('href', newHref + strHref[0]);
	}
}

// функция для исправления стилей старых новостей
function changeStyle() {
	var newsContainer = getElems('news-container');
	
	if(!newsContainer) return;
	
	newsContainer.style.display = 'block';
}

// не используется: добавляет миниатюру к новости
function displayNewsImage() {
	var imgs = getElems(['article-news-image']);
	
	if(!imgs) return;
	
	for(var imgElem of imgs) {
		if(isFileExists(imgs[i].getAttribute('src'))) {
			imgElem.style.display = 'block';
		}
		else imgElem.style.display = 'none';
	}
}

// делаем дерево комментариев
function makeCommentsTree() {
	var comm_tables = getElems(['comments-table']);
	
	if(!comm_tables) return;
	
	for(var i=0, len=comm_tables.length; i<len; i++) {
		var tr = getElems(['comments-content', 0], comm_tables[i]);	
		var parent_id = tr.children[1].textContent;
		
		if(parent_id === '') {
			comm_tables[i].parentNode.style.width = '100%';
			continue;
		}
		
		// самый верхний уровень
		DEBUG(makeCommentsTree.name, 'Parent: '+parent_id);
		for(var j=0; j<len; j++) {
			var temp_tr = getElems(['comments-content', 0], comm_tables[j]);
			var temp_id = getElems(['comment-id', 0], temp_tr);
			DEBUG(makeCommentsTree.name, 'Current id: '+temp_id);
			
			if(temp_id.textContent !== parent_id) continue;			
			
			DEBUG(makeCommentsTree.name, 'Parent is found: '+comm_tables[j]);
			comm_tables[j].parentNode.appendChild(comm_tables[i].parentNode);
		}
	}
}

// при клике на кнопку "Ответить" записываем в скрытое поле формы комментирования ID комментария
// ссылаемся на ID в тексте кнопки "Ответ"
function setCommentsParentId(e) {
	var target = e.target;
	
	if(!checkClass(target, ['respond-button'])) return;
	
	var parent = target.parentNode; 									// TD - родитель кнопки "Ответить"
	
	while(parent.tagName !== 'TABLE') {
		//if(!checkClass(parent, ['comments-respond'])) break;
		parent = parent.parentNode;
	}
	
	e.preventDefault();

	var parentLink = getElems(['', 0, 'A'], parent);
	var parentId = getElems(['comment-id', 0], parent).textContent;		// TR -> TR>A>textNode (ID комментария, на который отвечаем)
	var parentAuthor = parentLink.getAttribute('href');
	parentAuthor = parentAuthor.substr(parentAuthor.indexOf('=')+1);	// только ник
	var commentsInput = getElems(['comments-form', 0]).elements['comments-parent'];
	console.log("parentId: "+parentId);
	
	if(commentsInput.tagName !== 'INPUT') return;
	
	commentsInput.value = parentId;
	var postBtn = getElems(['comments-post-button', 0]);
	postBtn.value = 'Ответ сообщению '+parentId+' за авторством '+parentAuthor;
	postBtn.focus();
}

// открываем новость на уровне комментариев
function openNewsComments(e) {
	var target = e.target;
	var link, linkClass, parent;

	// для обоих стилей новостей
	if(!checkClass(target, ['comments-num', 'alt-news-comments-div'])) {
		parent = findParent(target, 'comments-num');
		
		if(!parent) {
			parent = findParent(target, 'alt-news-comments-div');
		}
	}
	
	if(parent) target = parent;
	
	if(checkClass(target, ['comments-num'])) {
		target = findParent(target, 'article-news');
		linkClass = 'article-news-more';
	}
	else if(checkClass(target, ['alt-news-comments-div'])) {
		target = findParent(target, 'alt-news-div');
		linkClass = 'alt-news-more';
	} 

	link = getElems([linkClass, 0], target);
	link = link.getAttribute('href');
	
	if(link) window.location.href = link + '#comments-wrapper';
}

function checkCookieToken(token) {
	var secToken = '';
	var allCookies = document.cookie.split('; ');
	
	for(var cookieElem of allCookies) {
		if(cookieElem.indexOf('sec-token') === -1) continue;
		
		secToken = decodeURIComponent(cookieElem.substring(10));	// sec-token:
	}
	
	return (secToken === token) ? true : false;
}

function removeActiveTinymceEditors() {
	if(tinymce.get().length <= 1) return;
	
	for(var editor of tinymce.get()) {
		editor.destroy();
	}
}

// AJAX запрос к странице
function sendAjaxRequest(data, url) {
	var request = new XMLHttpRequest();
	
	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			
			(request.status != 200) 
			? DEBUG(sendAjaxRequest.name, 'Ошибка: ' + request.responseText)
			: DEBUG(sendAjaxRequest.name, 'Запрос отправлен. Ответ сервера: '+request.responseText);
		}
	};
	
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	
	request.open('POST', url, true);
	request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	request.send(data);
	DEBUG(sendAjaxRequest.name, 'Отправили запрос');
}

// обновляем родительский элемент с комментариями
function updateCommentsWrapper() {
	var wrapper = getElems('comments-wrapper');
	var height = window.getComputedStyle(wrapper).getPropertyValue('height');
	var commentsDiv = getElems(['comments-list-div', 0], wrapper);
	
	// визульано показываем, что что-то происходит =)
	wrapper.style.height = height;
	wrapper.style.opacity = 0.5;
	
	var request = new XMLHttpRequest();
	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			(request.status != 200) 
			? DEBUG(updateCommentsWrapper.name, 'Ошибка: ' + request.responseText)
			: DEBUG(updateCommentsWrapper.name, 'Запрос отправлен.');
			
			for(var editor of tinymce.get()) {
				tinymce.EditorManager.execCommand('mceRemoveEditor', true, editor.id);
			}

			if(commentsDiv) {
				wrapper.removeChild(commentsDiv);
			}
			// вставляем обновленный список комментариев + старую форму
			wrapper.innerHTML = DOMPurify.sanitize(request.responseText) + DOMPurify.sanitize(wrapper.innerHTML);
			initTinyMCE('.comments-textarea', false, '100%');
			wrapper.style.height = '';
			wrapper.style.opacity = '';
			
			addLinksToCommentsId();
			
			if(window.admin) {
				admin.setPrivilege();
			}
			else if(window.user) {
				user.checkForUserComments();
			}
			
			makeCommentsTree();
			addEventListenerWithOptions(getElems(['respond-button']), 'click', setCommentsParentId, {});
		}
	};
	
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	
	setTimeout(function() {
		var id = getParamFromLocationSearch('id');
		request.open('GET', 'admin/comments_list.php?comments-location-id='+encodeURIComponent(id), true);
		request.send();
	}, 1500);
}

// добавляем к заголовку название новости/статьи
function updatePageTitle() {
	var doc = document;
	var title = doc.title;
	var params = window.location.search;
	var container;
	var	header;
	
	function findElem(tagName, parent) {
		if(!parent) parent = document;
		
		return tagName ? getElems(['', 0, tagName], parent) : false;
	}
	
	// старые новости
	if(params.indexOf('old') !== -1) {
		var pageParams = params.split('&');
		var newsContainer = getElems('news-container');
		
		var i=0;
		var tags = ['H1', 'H2', 'H3', 'STRONG', 'P'];
		var hElem = false;
		
		// ищем текст для заголовка
		while(i < tags.length) {
			if(tags[i] === 'STRONG') {
				hElem = getElems(['', 1, 'STRONG'], parent);	// пропускаем первый strong -> 'К новостям'

				if(hElem && hElem.parentNode.tagName === 'P') hElem = false;
			}
			else hElem = findElem(tags[i], newsContainer);
			
			if(!hElem) {
				i++;
				continue;
			}
			
			if(hElem.innerText !== '') {
				container = hElem;
				break;
			}
			i++;
		}

		for(var paramsElem of pageParams) {
			if(paramsElem.indexOf('custom-news-date') === -1) continue;
			
			header = paramsElem.substr(-8);	// дата новости
			
			if(container) {
				header += ': ' + container.textContent;
			}
			
			doc.title = header.substr(0, 50) + '...';
			return;
		}
		
		doc.title = 'Жить и Помнить - Старые новости';
		return; 						
	}
	
	// старые статьи
	if(params.indexOf('html') !== -1) {
		var parent = getElems('publs-container');
		var searchElems = ['H2', 'H3', 'P'];
		
		for(var searchElem of searchElems) {
			container = getElems(['', 0, searchElem], parent);
			
			if(!container || container.textContent === '') continue;

			header = container.textContent;
			doc.title = 'Жить и Помнить - Старые публикации ' + header.substr(0, 50) + '...';
			return;
		}
		
		doc.title = 'Жить и Помнить - Старые публикации';
		return;	
	}
	
	// новость
	if(title === 'Новости' && params.indexOf('custom-news-date') !== -1) {
		container = getElems(['news-full-container', 0]);
		
		// в старых новостях может не быть заголовков
		if(!container) return;
		
		header = getElems(['', 0, 'H4'], container);
	}
	// статья
	else if(title === 'Статьи' && params.indexOf('custom-publ') !== -1) {
		container = getElems(['publs-full-container', 0]);
		
		if(!container) return;
		
		header = getElems(['', 0, 'H3'], container);
	}
	// газета
	else if(title === 'Газета' && params.indexOf('custom-press') !== -1) {
		container = getElems('press-container');
		
		if(!container) return;
		
		header = getElems(['', 0, 'H1'], container);
	}
	
	if(!header) return;
	
	header = header.textContent;	
	doc.title = header.substr(0, 50) + '...';
}

// Функция поиска новости (вызывается из меню сайта)
/*
	- принимает форму из меню сайта
	- возвращает false для отмены стандартного поведения формы
*/
function newsSearch(form) {
	var date = form['custom-news-date'].value;
	DEBUG(newsSearch.name, 'form date => ' + date);
	
	if(!date) return false;
	
	var request = new XMLHttpRequest();
	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			
			if(request.status != 200) {
				DEBUG(newsSearch.name, 'Ошибка: ' + request.responseText);
			}
			else {
				DEBUG(newsSearch.name, 'Запрос отправлен.');
				
				var response = this.responseText;
				// вернулась строка
				if(typeof response === 'string') {
					try{
						response = JSON.parse(response);
					}
					catch(e) {
						DEBUG(newsSearch.name, 'Пришла не JSON строка: ' + e.toString());
					}
				}
				else {
					DEBUG(newsSearch.name, 'Пришла не строка: ' + request.responseText);
				}
				
				// строка оказалась формата JSON
				if(typeof response === 'object') {
					DEBUG(newsSearch.name, "PHP response: date => " + response['date'] + "; type => " + response['type']);
					
					if(response['type'] === 'db') {
						window.location.href = 'index.php?pages=news&custom-news-date='+response['date'];
					}
					else if(response['type'] === 'old') {
						window.location.href = 'index.php?pages=news&type=old&custom-news-date='+response['date'];
					}
				}
			}
		}
	};
	
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	
	request.open('GET', 'content/news_search.php?news_date='+encodeURIComponent(date), true);
	request.send();
	
	return false;
}

function postGuestbookMessage(e) {
	var target = e.target;
	
	if(!checkClass(target, ['guestbook-post-button'])) return;
	
	e.preventDefault();
	var guestbookForm = getElems(['guestbook-form', 0]);
	var text = guestbookForm.elements['guestbook-text'].value;
	var author = guestbookForm.elements['guestbook-author'].value;
	var email = guestbookForm.elements['guestbook-email'].value;
	var data = "guestbook-text=" + encodeURIComponent(text) + "&" +
			   "guestbook-author=" + encodeURIComponent(author) + "&" +
			   "guestbook-email=" + encodeURIComponent(email);
	
	sendAjaxRequest(data, 'content/guestbook.php');
	updateGuestbookDiv();
}

// обновляем элемент с гостевыми сообщениями
function updateGuestbookDiv() {
	var gbDiv = getElems('guestbook-container');

	if(!gbDiv) return;
	
	// визуально показываем, что что-то происходит =)
	gbDiv.style.opacity = 0.5;
	
	var request = new XMLHttpRequest();
	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			(request.status != 200) 
			? DEBUG(updateGuestbookDiv.name, 'Ошибка: ' + request.responseText)
			: DEBUG(updateGuestbookDiv.name, 'Запрос отправлен.');
			
			// вставляем обновленный список сообщение
			gbDiv.innerHTML = DOMPurify.sanitize(request.responseText);
			gbDiv.style.opacity = '';
			
			if(window.admin) {
				admin.setPrivilege();
			}
		}
	};
	
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	
	setTimeout(function() {
		request.open('GET', 'content/guestbook.php', true);
		request.send();
	}, 1500);
}

function logNotify(logText, logClass) {
	var logDiv = document.createElement('div');
	logDiv.classList.add('log-div');
	logDiv.innerText = logText;
	
	switch(logClass) {
		case 'error': logDiv.classList.add('log-div-error'); break;
		case 'warning': logDiv.classList.add('log-div-warning'); break;
		case 'info': logDiv.classList.add('log-div-info'); break;
		default: break;
	}
	
	document.body.appendChild(logDiv);
	
	setTimeout(function() {
		logDiv.classList.add('log-div-shown');
	}, 1000);
	
	setTimeout(function() {
		logDiv.classList.remove('log-div-shown');
	}, 4000);
	
	setTimeout(function() {
		logDiv.remove();
	}, 6000);
};