function Admin() {
	'use strict';
	this._XMLHttpRequest = null;
	this._editBtns = [];
	this._responseObject;
	this._editDiv = null;
	this._commentsTables = null;
	
	var isAdmin = false;
	var self = this;
	
	// выясняем являемся ли мы админом
	var checkIfAdmin = function() {
		self._XMLHttpRequest = new XMLHttpRequest();
		self._XMLHttpRequest.onreadystatechange = function () {
			if(self._XMLHttpRequest.readyState == 4) {
				clearTimeout(timeout);
				var resp = self._XMLHttpRequest.getResponseHeader('IsAdmin');
				DEBUG("func: checkIfAdmin; output: RESP: "+resp);
				if(resp !== null) {
					DEBUG("func: checkIfAdmin; output: Вы - Админ. Поздравляю!");
					isAdmin = true;
				}
				else {
					DEBUG("func: checkIfAdmin; output: Вы - не Админ. Херово!");
					admin = null;
				}
			}
		};
		var timeout = setTimeout(function() {
			self._XMLHttpRequest.abort();
		}, 60*1000);
		self._XMLHttpRequest.open('HEAD', 'content/json.php', true);
		self._XMLHttpRequest.send();
	};
	
	checkIfAdmin();
	
	this.getIsAdmin = function() {
		return isAdmin;
	};
};

Admin.prototype.getIsAdmin = function() {
	'use strict';
	return this.getIsAdmin();
};

// проверяем есть ли на странице редактируемые элементы
Admin.prototype.checkForEditableContent = function() {
	'use strict';
	var elem = null;
	
	if(document.getElementsByClassName('article-news').length > 0) {
		elem = document.getElementsByClassName('article-news');					// если это список новостей
	} 
	
	if(document.getElementsByClassName('news-full-container').length > 0) {
		elem = document.getElementsByClassName('news-full-container');			// если это полная новость
	}
	
	if(document.getElementsByClassName('article-publs').length > 0) {	
		elem = document.getElementsByClassName('article-publs');				// если это список статей
	}
	
	if(document.getElementsByClassName('publs-full-container').length > 0) {
		elem = document.getElementsByClassName('publs-full-container');			// если это полная статья
	}
	
	if(elem != null) this.addEditBtn(elem);
};

// проверяем есть ли на странице редактируемые комментарии
Admin.prototype.checkForComments = function() {
	'use strict';
	if(document.getElementsByClassName('comments-table').length > 0) {
		this._commentsTables = document.getElementsByClassName('comments-table');
		this.addCommentsEditBtn();
	}
};

// добавляем еще один TR к каждому комментарию
// TODO: проверить, есть ли кнопки перед добавлением
Admin.prototype.addCommentsEditBtn = function() {
	'use strict';
	if(typeof tinymce === 'undefined') {
		appendScript('scripts/tinymce/tinymce.min.js');							
	}
	
	// если комментарии есть
	if(this._commentsTables === null) return;
	
	for(var i=0, len=this._commentsTables.length; i<len; i++) {
		var commId = this._commentsTables[i].getElementsByClassName()[0];
		var editTr = this.createEditCommentsTr(commId.getElementsByTagName('A')[0].innerHTML);
		this._commentsTables[i].getElementsByTagName('TBODY')[0].appendChild(editTr);
	}
	
	this.initCommentsEditBtns();
};

// вешаем события на кнопки редактировать/удалить/сохранить
Admin.prototype.initCommentsEditBtns = function() {
	'use strict';
	var self = this;
	
	for(var i=0, len=this._commentsTables.length; i<len; i++) {
		var btns = this._commentsTables[i].getElementsByClassName('admin-edit');
		
		for(var j=0, btnsLen=btns.length; j<btnsLen; j++) {
			(function() {
				btns[j].addEventListener('click', self.addHandlerOnCommentsEditBtns.bind(self), false);
			})();
		}
	}
};

// описываем события для кнопок (редактировать/удалить/сохранить)
Admin.prototype.addHandlerOnCommentsEditBtns = function(e) {
	'use strict';
	var target = e.target;
				
	if(target.classList.contains('edit-comm')) {
		e.preventDefault();
		
		if(target.innerHTML === 'Редактировать') {
			var totalEditors = 1;
			if(tinymce.activeEditor.getElement.id === 'comments-text') {	// если есть форма комментирования, то пропускаем ее
				totalEditors = 2;
			} 
			if(tinymce.editors.length > totalEditors) {						// уже есть редактируемый комментарий
				if(confirm('Уже начато редактирование комментария №{}. Отменить изменения и редактировать комментарий №{} ?')) {
					this.disablePrevEditors();								// убираем предыдущие объект tinymce
					this.initEditorForComment(target);						// делаем из td объект tinymce
				}
				else return; 												// решили закончить с предыдущим комментарием
			} 
			else {
				this.initEditorForComment(target);							// делаем из td объект tinymce
			}
		}
		else if(target.innerHTML === 'Сохранить') {
			e.stopPropagation();
			var id = target.getAttribute('data-id');
			var updatedText = tinymce.activeEditor.getContent();
			var adminLogin = document.getElementsByClassName('users-info')[0];
			adminLogin = adminLogin.getElementsByTagName('li')[0].innerHTML.trim().substr(7);
			updatedText += "<em>Редактировано администратором " + adminLogin + " | " + new Date().toLocaleString() + '</em>';
			DEBUG('func: addHandlerOnCommentsEditBtns; output: ' + id + "|" + updatedText);
			// запрос на сохранение элемента
			this._sendSaveRequest({
				'comment-id': id,
				'comment-text': updatedText
			   },
			   'POST', 
			   'admin/admin_comments/update_comment.php', 
			   'application/x-www-form-urlencoded');
			updateCommentsWrapper();
		}
	}
	else if(target.classList.contains('del-comm')) {
		e.preventDefault();
		e.stopPropagation();
		var id = target.getAttribute('data-id');
		if(confirm('Точно удалить комментарий №'+id+'?')) { 
			DEBUG('func: addHandlerOnCommentsEditBtns; output: Удаление: '+target.getAttribute('data-id'));			
			// запрос на удаление элемента
			this._sendSaveRequest({
				'comment-id': id
			   },
			   'POST', 
			   'admin/admin_comments/delete_comment.php', 
			   'application/x-www-form-urlencoded');
			updateCommentsWrapper();
		}
		else return;
 	}
};

// инициализируем объект tinymce
Admin.prototype.initEditorForComment = function(elem) {
	'use strict';
	var elemParent = findParent(elem, 'comments-table');
	
	if(elemParent === null) return;
	var commentsTextTd = elemParent.getElementsByClassName('comment-text')[0]; // нашли текст комментария
	DEBUG('func: initEditorForComment; output: commentsTextTd: '+commentsTextTd);
	DEBUG('func: initEditorForComment; output: Редактирование: '+elem.getAttribute('data-id'));
	var commId = elem.getAttribute('data-id');
	commentsTextTd.classList.add('edit-this');
	elem.innerHTML = 'Сохранить';
	initTinyMCE('.edit-this', true, 'auto', 'auto');
};

// убираем предыдущий объект tinymce и меняем назначение кнопок
Admin.prototype.disablePrevEditors = function() {
	'use strict';
	var prevTinymceElems = document.getElementsByClassName('edit-this');
	var saveLinks = document.getElementsByClassName('edit-comm');
	var activeEditorId = tinymce.activeEditor.getParam('id');
	tinymce.remove('#'+activeEditorId);
	
	for(var i=0, len=prevTinymceElems.length; i<len; i++) {
		if(prevTinymceElems[i].classList.contains('edit-this')) {
			prevTinymceElems[i].classList.remove('edit-this');
		}
	}
	
	for(i=0, len=saveLinks.length; i<len; i++) {
		if(saveLinks[i].innerHTML === 'Сохранить') {
			saveLinks[i].innerHTML = 'Редактировать';
		}
	}
};

// создаем TR с кнопками редактировать/удалить 
Admin.prototype.createEditCommentsTr = function(commId) {
	'use strict';
	var tr = document.createElement('TR');
	tr.classList.add('comments-edit');
	var editTd = document.createElement('TD');
	var removeTd = document.createElement('TD');
	var infoTd = document.createElement('TD');
	infoTd.setAttribute('colspan', 3);
	infoTd.innerHTML = '<strong>Управление</strong>';
	editTd.innerHTML = '<a href="#" class="admin-edit edit-comm " data-id="'+commId+'">Редактировать</a>';
	removeTd.innerHTML = '<a href="#" class="admin-edit del-comm" data-id="'+commId+'">Удалить</a>';
	tr.appendChild(infoTd);
	tr.appendChild(editTd);
	tr.appendChild(removeTd);
	
	return tr;
};

// функция для добавления кнопки редактирования (новости/статьи)
Admin.prototype.addEditBtn = function(elem) {
	'use strict';
	if(typeof tinymce === 'undefined') {
		appendScript('scripts/tinymce/tinymce.min.js');
	}
	
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
	'use strict';
	var self = this;
	for(var i=0, len=this._editBtns.length; i<len; i++) {
		(function() {
			self._editBtns[i].addEventListener('click', self.addHandlerOnEditBtns.bind(self), false);
		})();
	}
};

// функция добавляет обработчики на кнопки DIVa, в котором редактируются данные новости/статьи
Admin.prototype.addHandlerOnEditBtns = function(e) {
	'use strict';
	var id = e.target.parentNode.getAttribute('id');
	var className = e.target.parentNode.className;
	var self = this;
	
	this._getElemByDBId(className, id, function() {		// ищем элементы для редактирования по их ID
		var response = this.responseText;
		
		if(typeof response === 'string') {
			try{
				self._responseObject = JSON.parse(response);
			}
			catch(e) {
				DEBUG('func: _getElemByDBId; Пришла не JSON строка: ' + e.toString());
			}
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
					var reqTarget = (className.includes('news')) ? 'news' : 'publs';
					self._sendSaveRequest({
						'id': id,
						'text': updatedText,
						'name': reqTarget+'_text'
					   },
					   'POST', 
					   'admin/admin_'+reqTarget+'/update_'+reqTarget+'.php', 
					   'application/x-www-form-urlencoded');
					document.body.removeChild(this);	// удаляем div редактирования
				} 
				
			}, false);
		}
		else DEBUG('func: addHandlerOnEditBtns; output: ' + response);
	});
};

// функция создает DIV элемент, в котором можно редактировать элементы новости или статьи
Admin.prototype._createEditDiv = function(className) {
	'use strict';
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
	
	if(className.includes('news')) {
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
	'use strict';
	var data = '', j = 1;
	var self = this;
	
	for(var key in argArr) {
		var val = argArr[key];
		data += key + '=' + val;
		(Object.keys(argArr).length > j++) ? data += '&' : DEBUG('func: _sendSaveRequest; Параметр всего 1');	// TODO: тут лажа какая-то
	}
	DEBUG("func: _sendSaveRequest; data: " + data);
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function() {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout);
			if(self._XMLHttpRequest.status != 200) {
				DEBUG('func: _sendSaveRequest; output: Ошибка: ' + self._XMLHttpRequest.responseText);
			}
			else {
				DEBUG('func: _sendSaveRequest; output: Запрос отправлен. Все - хорошо. Ответ сервера: ' + self._XMLHttpRequest.responseText);
				//updateCommentsWrapper();
			}
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
	'use strict';
	// новость или статья
	var pattern = (className.includes('news')) ? 'news' : 'publs';
	var self = this;
	
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function () {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout); 
			
			if(self._XMLHttpRequest.status != 200) {			// сервер сказал "НЕТ"
				DEBUG('func: _getElemByDBId; output: wha?');
			}
			else {
				var resp = self._XMLHttpRequest.responseText;
				if(resp != null) {								// в ответ что-то пришло
					if(typeof callback  == 'function') {
						callback.call(self._XMLHttpRequest);	// и это json строка, 
					}											// которая отдается в виде параметра в функцию callback
				}
				else {
					DEBUG("func: _getElemByDBId; output: Херово!");
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

// создаем объект класса Admin
admin = null;
function createAdminClass() {
	'use strict';
	admin = new Admin();
	//admin.checkIfAdmin();
	admin.setPrivilege();						
}

// для админа ставим кнопки редактирования
Admin.prototype.setPrivilege = function() {
	'use strict';
	var self = this;
	setTimeout(function() { 
		DEBUG('func: setPrivilege; output: admin: ' + self.getIsAdmin());
		
		if(self.getIsAdmin()) {
			//appendScript('scripts/tinymce/tinymce.min.js');
			self.checkForEditableContent();			// расставляем кнопки редактирования
			self.checkForComments();
		}
		else {
			DEBUG('func: setPrivilege; output: Вы не админ. Хватит хулиганить!');
		}
	}, 1500);										// даем время на выполнение запроса в checkIfAdmin
};

addEventListenerWithOptions(document, 'DOMContentLoaded', createAdminClass, {passive: true});