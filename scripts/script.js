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
	//console.log(respTime);
	
}, {passive: true} );

/***********************/

function Admin() {
	this._isAdmin = false;
	this._XMLHttpRequest = null;
	this._editBtns = [];
	this._responseObject;
	this._editDiv = null;
};

Admin.prototype.getIsAdmin = function() {
	return this._isAdmin;
};

// выясняем являемся ли мы админом
Admin.prototype.checkIfAdmin = function() {
	var self = this;
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function () {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout);
			var resp = self._XMLHttpRequest.getResponseHeader('IsAdmin');
			
			if(resp !== null) {
				console.log("Вы - Админ. Поздравляю!");
				self._isAdmin = true;
				//self.checkForEditableContent();
			}
			else {
				console.log("Вы - не Админ. Херово!");
			}
			//appendScript('scripts/tinymce/tinymce.min.js');
		}
	};
	var timeout = setTimeout(function() {
		self._XMLHttpRequest.abort();
	}, 60*1000);
	this._XMLHttpRequest.open('HEAD', 'content/json.php', true);
	this._XMLHttpRequest.send();
};

// проверяем есть ли на странице редактируемые элементы
Admin.prototype.checkForEditableContent = function() {
	var elem = null;
	
	if(document.getElementsByClassName('article-news').length > 0) {
		elem = document.getElementsByClassName('article-news');
	} 
	
	if(document.getElementsByClassName('news-full-container').length > 0) {
		elem = document.getElementsByClassName('news-full-container');
	}
	
	if(document.getElementsByClassName('article-publs').length > 0) {
		elem = document.getElementsByClassName('article-publs');
	}
	
	if(document.getElementsByClassName('publs-full-container').length > 0) {
		elem = document.getElementsByClassName('publs-full-container');
	}
	
	if(elem != null) this.addEditBtn(elem);
	
	// редактирвоание комментариев
	if(document.getElementsByClassName('comments-table').length > 0) {
		elem = document.getElementsByClassName('comments-table');
		this.addCommentsEditBtn(elem);
	}
};

// добавляем еще один TR к каждому комментарию
Admin.prototype.addCommentsEditBtn = function(elem) {
	appendScript('scripts/tinymce/tinymce.min.js');
	
	for(var i=0, len=elem.length; i<len; i++) {
		var commId = elem[i].getElementsByTagName('A')[0].innerHTML;
		elem[i].getElementsByTagName('TBODY')[0].appendChild(createEditCommentsTr(commId));
	}
	
	this.initCommentsEditBtns(elem);
};

// вешаем события на кнопки редактировать/удалить
Admin.prototype.initCommentsEditBtns = function(elem) {
	for(var i=0, len=elem.length; i<len; i++) {
		var btns = elem[i].getElementsByTagName('A');
		
		for(var j=0, btnsLen=btns.length; j<btnsLen; j++) {
			btns[j].addEventListener('click', function(e) {
				var target = e.target;
				
				if(target.className == 'edit-comm') {
					e.preventDefault();
					//console.log('Редактирование: '+target.getAttribute('data-id'));
					var commentsTextTd = target.parentNode.parentNode.parentNode.children[1].children[3];
					//console.log('commentsTextTd: '+commentsTextTd);
					commentsTextTd.classList.add('edit-this');
					initTinyMCE('.edit-this', true);	// делаем из td объект tinymce
				}
				else if(target.className == 'del-comm') {
					console.log('Удаление: '+target.getAttribute('data-id'));
					
				}
			}, false);
		}
	}
};

// создаем TR с кнопками редактировать/удалить 
function createEditCommentsTr(commId) {
	var tr = document.createElement('TR');
	tr.classList.add('comments-edit');
	var editTd = document.createElement('TD');
	var removeTd = document.createElement('TD');
	var infoTd = document.createElement('TD');
	infoTd.setAttribute('colspan', 3);
	infoTd.innerHTML = '<strong>Управление</strong>';
	editTd.innerHTML = '<a href="#" class="edit-comm" data-id="'+commId+'">Редактировать</a>';
	removeTd.innerHTML = '<a href="#" class="del-comm" data-id="'+commId+'">Удалить</a>';
	tr.appendChild(infoTd);
	tr.appendChild(editTd);
	tr.appendChild(removeTd);
	
	return tr;
}

// функция для добавления кнопки редактирования (новости/статьи)
Admin.prototype.addEditBtn = function(elem) {
	appendScript('scripts/tinymce/tinymce.min.js');
	
	// создаем для каждого редактируемого элемента кнопку
	for(var i=0, len=elem.length, firstChild; i<len; i++) {
		this._editBtns[i] = document.createElement('div');
		this._editBtns[i].className = 'admin-edit-button';
		firstChild = elem[i].children[0];
		elem[i].insertBefore(this._editBtns[i], firstChild);
	}

	this.initAdminEdit();
};

// вешаем на каждую кнопку событие на нажатие
Admin.prototype.initAdminEdit = function() {
	var self = this;
	for(var i=0, len=this._editBtns.length; i<len; i++) {
		(function() {
			self._editBtns[i].addEventListener('click', self.addHandlerOnEditBtns.bind(self), false);
		})();
	}
};

// функция добавляет обработчики на кнопки DIVa, в котором редактируются данные новости/статьи
Admin.prototype.addHandlerOnEditBtns = function(e) {
	var id = e.target.parentNode.getAttribute('id');
	var className = e.target.parentNode.className;
	var self = this;
	
	this._getElemByDBId(className, id, function() {		// ищем элементы для редактирования по их ID
		var response = this.responseText;
		
		if(typeof response === 'string') {
			self._responseObject = JSON.parse(response);
		}
		
		if(typeof self._responseObject === 'object') {
			var editElem = document.getElementsByClassName('admin-edit-elem')[0];
			
			if(editElem !== undefined) {
				self._editDiv = null;
				document.body.removeChild(editElem);
			}
	
			// создаем все необходимые элементы
			self._createEditDiv(className);
			
			// делаем из textarea объект tinymce
			initTinyMCE('.admin-edit-elem textarea', false);
			
			// вешаем на кнопки события
			self._editDiv.addEventListener('click', function(e) {
				var target = e.target;
				e.preventDefault();
				
				if(target.innerHTML === 'Отменить') {
					e.stopPropagation();
					document.body.removeChild(this);	// удаляем div редактирования
				}
				else if(target.innerHTML === 'Сохранить') {
					e.stopPropagation();
					var updatedText = tinymce.activeEditor.getContent();
					// запрос на сохранение элемента
					var reqTarget = (this.className === 'article-news') ? 'news' : 'publs';
					this._sendSaveRequest({
						'id': id,
						'text': updatedText
					   },
					   'POST', 
					   'admin/update_'+reqTarget+'.php', 
					   'application/x-www-form-urlencoded');
					document.body.removeChild(this);	// удаляем div редактирования
				} 
				
			}, false);
		}
		else console.log(response);
	});
};

// функция создает DIV элемент, в котором можно редактировать элементы новости или статьи
Admin.prototype._createEditDiv = function(className) {
	var div = document.createElement('div');
	var form = document.createElement('form');
	var textarea = document.createElement('textarea');
	var saveBtn = document.createElement('a');
	var closeBtn = document.createElement('a');
	saveBtn.innerHTML = 'Сохранить';
	saveBtn.setAttribute('href', '#');
	closeBtn.innerHTML = 'Отменить';
	closeBtn.setAttribute('href', '#');
	div.className = 'admin-edit-elem'; 
	
	if(className === 'news-full-container') {
		form.innerHTML = 'ID: ' + this._responseObject['news_id'] + ' | ' + 
						 'Загловок: ' + this._responseObject['news_header'];
		textarea.innerHTML = this._responseObject['news_text'];
	}
	else {
		form.innerHTML = 'ID: ' + this._responseObject['publs_id'] + ' | ' + 
						 'Загловок: ' + this._responseObject['publs_header'];
		textarea.innerHTML = this._responseObject['publs_text'];
	} 
		
	form.appendChild(textarea);
	form.appendChild(saveBtn);
	form.appendChild(closeBtn);
	div.appendChild(form);
	document.body.appendChild(div);
	
	this._editDiv = div;
};

// функция применяет изменения новости или статьи
/*
	argArr -> параметры (id, текст...) элемента
	reqType -> тип запроса (обычно POST)
	reqTarget -> какой php файл используем для сохранения 
	contentType -> Content-Type в заголовок
*/
Admin.prototype._sendSaveRequest = function(argArr, reqType, reqTarget, contentType) {
	var data = '', j = 1;
	var self = this;
	
	for(var key in argArr) {
		var val = argArr[key];
		data += key + '=' + val;
		(j++ < Object.keys(argArr).length) ? data += '&' : console.log('I dunno');
	}
	
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function() {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout);
			if(self._XMLHttpRequest.status != 200) {
				console.log('Ошибка: ' + self._XMLHttpRequest.responseText);
			}
			else console.log('Запрос отправлен. Все - хорошо.');
		}
	};
	var timeout = setTimeout(function() {
		self._XMLHttpRequest.abort();
	}, 60*1000);
	this._XMLHttpRequest.open(reqType, reqTarget, true);
	this._XMLHttpRequest.setRequestHeader("Content-Type", contentType);
	this._XMLHttpRequest.send(data);
}

// функция делает AJAX запрос на выборку новости/статьи по ID
Admin.prototype._getElemByDBId = function(className, id, callback) {
	// новость или статья определяет переменная pattern
	var pattern = (className === 'news-full-container') ? 'news' : 'publs' ;
	var self = this;
	
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function () {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout); 
			
			if(self._XMLHttpRequest.status != 200) {			// сервер сказал "НЕТ"
				console.log('wha?');
			}
			else {
				var resp = self._XMLHttpRequest.responseText;
				if(resp != null) {								// в ответ что-то пришло
					if(typeof callback  == 'function') {
						callback.call(self._XMLHttpRequest);	// в ответ приходит json строка, которая отдается в виде параметра в функцию callback
					}
				}
				else {
					console.log("Херово!");
				}	
			}
		}
	};
	
	var timeout = setTimeout(function() {
		self._XMLHttpRequest.abort();
	}, 60*1000);
	this._XMLHttpRequest.open('GET', 'admin/admin_'+pattern+'/get_'+pattern+'_by_id.php?id=' + id, true);
	this._XMLHttpRequest.send();
};

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

// создаем класс Admin
function createAdminClass() {
	var admin = new Admin();
	admin.checkIfAdmin();
	setTimeout(function() { 
		console.log('admin: ' + admin.getIsAdmin()); 
		if(admin.getIsAdmin()) {
			//appendScript('scripts/tinymce/tinymce.min.js');
			admin.checkForEditableContent();	// расставляем кнопки редактирования
		}
	}, 1000);									// даем время на выполнение запроса в checkIfAdmin
}

addEventListenerWithOptions(document, 'DOMContentLoaded', createAdminClass, {passive: true});

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
				
				//console.log("loginTd: "+ loginTd[2].innerHTML);
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
	
	var urlArr = decodeURIComponent(location.search.substr(1)).split('&');
	var pair, urlParams = new Object;
	
	for(var i=0, len=urlArr.length; i<len; i++) {
		pair = urlArr[i].split("=");
		urlParams[pair[0]] = pair[1];
	}
	
	var pageNum = getUrlParam('page', urlParams);
	
	if(!pageNum) pageNum = 1;
				
	if(target == this.firstChild) {
		if(target.innerHTML.indexOf("«")) {
			if(pageNum != 1) {
				urlParams['page'] = --pageNum;
				urlArr = [];
				
				for(var elem in urlParams) {
					urlArr.push(elem + "=" + urlParams[elem]); 
				}
				location.search = urlArr.join('&');
				
			}
		}
	}
	else if(target == this.lastChild) {
		if(target.innerHTML.indexOf("«")) {
			if(pageNum != this.children.length-2) {
				urlParams['page'] = ++pageNum;
				urlArr = [];
				
				for(var elem in urlParams) {
					urlArr.push(elem + "=" + urlParams[elem]); 
				}
				location.search = urlArr.join('&');
			}
		}
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
		link.setAttribute('href', 'index.php?pages=news&custom-news-date=' + linkHref);
		//console.log(link.getAttribute('href'));
	}
}

// функция для исправления ссылок в общем списке старых статей
function replacePressLinks() {
	var press = document.body.getElementsByClassName('article-press');
	
	for(var i=0, len=press.length; i<len; i++) {
		var str = press[i].getElementsByTagName('A')[0];
		str.setAttribute('href', 'index.php?pages=press&custom-press=' + str.getAttribute('href').substring(0, 5));
		//console.log(press[i].getAttribute('href'));
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

/*function checkIfAdmin(callback) {
	var request = new XMLHttpRequest();
	request.onreadystatechange = function () {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			var resp = request.getResponseHeader('IsAdmin');
			
			if(resp !== null) {
				console.log("Вы - Админ. Поздравляю!");
				if(typeof callback == 'function') {
					callback.call(request);
				}
			}
			else {
				console.log("Вы - не Админ. Херово!");
			}
		}
	};
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	request.open('HEAD', 'content/json.php', true);
	request.send();
}*/

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
			//console.log('Parent: '+parent_id);
			for(var j=0; j<comm_tables.length; j++) {
				var temp_tr = comm_tables[j].getElementsByTagName('tr')[1];
				var temp_id = temp_tr.firstChild.firstChild.innerHTML;
				//console.log('Current id: '+temp_id);
				if(temp_id == parent_id) {
					//console.log('Parent is found: '+comm_tables[j]);
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
			? console.log('Ошибка: ' + request.responseText)
			: console.log('Запрос отправлен. Все - хорошо.');
		}
	};
	
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	
	request.open('POST', 'admin/save_comments.php', true);
	request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	request.send(data);
	console.log('Отправили запрос');
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
	/*$.ajax({ url: 'admin/comments_form.php',
		 data: {location: window.location.toString()},
		 type: 'get',
		 success: function(output) {
					  console.log(output);
					  var wrapper = document.getElementsByClassName('comments-wrapper')[0];
					  wrapper.innerHTML = '';
					  wrapper.innerHTML = output;
				  }
	});*/
	var wrapper = document.getElementsByClassName('comments-wrapper')[0];
	var height = window.getComputedStyle(wrapper).getPropertyValue('height');
	var commentsDiv = wrapper.getElementsByClassName('comments-list-div')[0];
	wrapper.style.height = height;
	wrapper.style.opacity = 0.5;
	
	var request = new XMLHttpRequest();
	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			(request.status != 200) 
			? console.log('Ошибка: ' + request.responseText)
			: console.log('Запрос отправлен. Все - хорошо.');
			tinymce.EditorManager.execCommand('mceRemoveEditor', true, 'comments-text');
			wrapper.removeChild(commentsDiv);
			wrapper.innerHTML = request.responseText + wrapper.innerHTML;
			tinymce.EditorManager.execCommand('mceAddEditor', true, 'comments-text');
			wrapper.style.height = '';
			wrapper.style.opacity = '';
			makeCommentsTree();
		}
	};
	
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	
	setTimeout(function() {
		request.open('GET', 'admin/comments_list.php?location='+encodeURIComponent(window.location.href), true);
		request.send();
	}, 1500);
}