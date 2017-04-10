function Admin() {
	'use strict';
	this._XMLHttpRequest = null;
	this._editBtns = [];
	this._responseObject;
	this._editDiv = null;
	this._commentsTables = null;
	this._tempText = '';
	
	var isAdmin = false;
	var self = this;
	var login = null;
	
	// выясняем являемся ли мы админом
	(function() {
		self._XMLHttpRequest = new XMLHttpRequest();
		self._XMLHttpRequest.onreadystatechange = function () {
			if(self._XMLHttpRequest.readyState == 4) {
				clearTimeout(timeout);
				var resp = self._XMLHttpRequest.getResponseHeader('IsAdmin');
				var respLogin = self._XMLHttpRequest.getResponseHeader('UserLogin');
				DEBUG("checkIfAdmin", "RESP: "+resp);
				
				if(respLogin !== null) {
					if(resp !== null) {
						DEBUG("checkIfAdmin", "Вы - Админ. Поздравляю!");
						isAdmin = true;
						self.setPrivilege();
						User = user = null;
					}
					else {
						DEBUG("checkIfAdmin", "Вы - не Админ.");
						Admin = admin = null;
						createUserClass();
					}
				}
				else {
					DEBUG("checkIfAdmin", "Вы - не авторизованы.");
					Admin = admin = null;
					User = user = null;
				} 
			}
		};
		var timeout = setTimeout(function() {
			self._XMLHttpRequest.abort();
		}, 60*1000);
		self._XMLHttpRequest.open('HEAD', 'content/json.php', true);
		self._XMLHttpRequest.send();
	})();
	
	this.getAdmin = function() {
		return isAdmin;
	};
	
	this.getLogin = function() {
		return login;
	};
};

Admin.prototype.getIsAdmin = function() {
	'use strict';
	return this.getAdmin();
};

Admin.prototype.getAdminLogin = function() {
	'use strict';
	return this.getLogin();
};

// проверяем есть ли на странице редактируемые элементы
Admin.prototype.checkForEditableContent = function checkForEditableContent() {
	'use strict';
	var elems = document.getElementsByClassName('editable');
	
	// есть хотя бы один элемент
	if(elems.length > 0) {
		this.addEditBtn(elems);
	}
};

// проверяем есть ли на странице редактируемые комментарии
Admin.prototype.checkForComments = function checkForComments() {
	'use strict';
	var commentsTables = document.getElementsByClassName('comments-table');
	
	if(commentsTables.length > 0) {
		this._commentsTables = commentsTables;
		this.addCommentsEditBtn();
	}
};

// добавляем еще один TR к каждому комментарию
Admin.prototype.addCommentsEditBtn = function addCommentsEditBtn() {
	'use strict';
	if(typeof tinymce === 'undefined') {
		appendScript('scripts/tinymce/tinymce.min.js');							
	}
	
	// если комментарии есть
	if(this._commentsTables === null) return;
	
	// если кнопок пока нет
	if(document.getElementsByClassName('comments-edit').length > 0) return;
	
	for(var i=0, len=this._commentsTables.length; i<len; i++) {
		var commId = this._commentsTables[i].getElementsByClassName('comment-id')[0];
		var editTr = this.createEditCommentsTr(commId.getElementsByTagName('A')[0].textContent);
		this._commentsTables[i].getElementsByTagName('TBODY')[0].appendChild(editTr);
	}
	
	this.initCommentsEditBtns();
};

// вешаем события на кнопки редактировать/удалить/сохранить
Admin.prototype.initCommentsEditBtns = function initCommentsEditBtns() {
	'use strict';
	var self = this;
	
	for(var i=0, len=this._commentsTables.length; i<len; i++) {
		var btns = this._commentsTables[i].getElementsByClassName('admin-edit');
		
		for(var j=0, btnsLen=btns.length; j<btnsLen; j++) {
			(function() {
				btns[j].addEventListener('mouseup', self.addHandlerOnCommentsEditBtns.bind(self), false);
			})();
		}
	}
};

// описываем события для кнопок (редактировать/удалить/сохранить)
Admin.prototype.addHandlerOnCommentsEditBtns = function addHandlerOnCommentsEditBtns(e) {
	'use strict';
	var target = e.target;
	var self = this;
	var targetId = target.getAttribute('data-id');
	var targetText = target.textContent;
	
	if(target.classList.contains('edit-comm')) {
		e.preventDefault();
		
		if(targetText === 'Редактировать') {
			editComments.call(self, target, targetId);
		}
		else if(targetText === 'Сохранить') {
			e.stopPropagation();
			saveComments.call(self, targetId);
		}
	}
	else if(target.classList.contains('del-comm')) {
		e.preventDefault();
		e.stopPropagation();
		deleteComments.call(self, targetId);
 	}
};

/*
	Вспомогательные функции редактирования/удаления/сохранения комментариев
*/
function editComments(td, tdId) {
	var totalEditors = 1;
	
	if(tinymce.activeEditor.getElement.id === 'comments-text') {	// если есть форма комментирования, то пропускаем ее
		totalEditors = 2;
	}
	
	if(tinymce.editors.length > 1) {
		if(tinymce.editors[1].id === 'edit-textarea') {
			alert("Закончите текущее редактирование!");
			return;
		}
		
		if(tinymce.activeEditor.id === 'comments-text') {			// была выбрана форма комментирования
			tinymce.editors[1].focus();
		}
	}
	
	if(tinymce.editors.length > totalEditors) {						// уже есть редактируемый комментарий
		var prevEditor = tinymce.activeEditor.bodyElement.parentElement;
		prevEditId = prevEditor.getElementsByClassName('comment-id')[0].textContent;

		if(confirm('Уже начато редактирование комментария №'+prevEditId+
				   '. Отменить изменения и редактировать комментарий №'+tdId+' ?')) {
			this.disablePrevEditors();								// убираем предыдущие объект tinymce
			this.initEditorForComment(td);							// делаем новый объект tinymce
		}
		else return; 												// решили закончить с предыдущим комментарием
	} 
	else {
		if(this._tempText !== '') {
			this._tempText = '';
		}
		
		this.initEditorForComment(td);								// делаем новый объект tinymce
	}
}

function deleteComments(tdId) {
	if(confirm('Точно удалить комментарий №'+tdId+'?')) { 
		DEBUG(deleteComments.name, 'Удаление: '+tdId);			
		// запрос на удаление элемента
		removeActiveTinymceEditors();
		this._tempText = '';
		this._sendSaveRequest({
			'comment-id': tdId
		   },
		   'POST', 
		   'admin/admin_comments/delete_comment.php', 
		   'application/x-www-form-urlencoded');
		updateCommentsWrapper();
	}
	else return;
}

// TODO: фигово сделано
function saveComments(tdId) {
	// была выбрана форма комментирования
	if(tinymce.editors.length > 1) {
		if(tinymce.activeEditor.id === 'comments-text') {			
			tinymce.editors[1].focus();
		}
	}
	
	var updatedText = tinymce.activeEditor.getContent();
	var activeEditorId = tinymce.activeEditor.getParam('id');
	var editPos = updatedText.indexOf('<br><em class=');
	removeActiveTinymceEditors();
	this._tempText = '';
	
	if(editPos !== -1) {
		updatedText = updatedText.substr(0, editPos);
	}
	
	DEBUG(saveComments.name, tdId + "|" + updatedText);
	// запрос на сохранение элемента
	this._sendSaveRequest({
		'comment-id': tdId,
		'comment-text': updatedText,
		'comment-author': this.getAdminLogin()
	   },
	   'POST', 
	   'admin/admin_comments/update_comment.php', 
	   'application/x-www-form-urlencoded');
	updateCommentsWrapper();
}

// инициализируем объект tinymce
Admin.prototype.initEditorForComment = function initEditorForComment(td) {
	'use strict';
	var tdParent = findParent(td, 'comments-table');
	
	if(tdParent === null) return;
	
	var commentsTextTd = tdParent.getElementsByClassName('comment-text')[0]; // нашли текст комментария
	var commentsText = commentsTextTd.innerHTML;
	var editPos = commentsText.indexOf('<br><em class=');
	
	if(editPos !== -1) {
		this._tempText = commentsText;
		commentsTextTd.innerHTML = commentsText.substr(0, editPos);
	}
	
	DEBUG(initEditorForComment.name, 'commentsTextTd: '+commentsTextTd);
	DEBUG(initEditorForComment.name, 'Редактирование: '+td.getAttribute('data-id'));
	var commId = td.getAttribute('data-id');
	commentsTextTd.classList.add('edit-this');
	td.textContent = 'Сохранить';
	initTinyMCE('.edit-this', true, 'auto', 'auto');
};

// убираем предыдущий объект tinymce и меняем назначение кнопок
Admin.prototype.disablePrevEditors = function disablePrevEditors() {
	'use strict';	
	var prevTinymceElems = document.getElementsByClassName('edit-this');
	var saveLinks = document.getElementsByClassName('edit-comm');
	var activeEditorId = tinymce.activeEditor.getParam('id');

	for(var i=0, len=tinymce.editors.length; i<len; i++) {
		if(tinymce.editors[i].id !== 'comments-text') {
			tinymce.remove('#'+tinymce.editors[i].id);
		}
	}
	
	for(var i=0, len=prevTinymceElems.length; i<len; i++) {
		if(this._tempText !== '') {
			DEBUG(disablePrevEditors.name, 'this._tempText: '+this._tempText);
			DEBUG(disablePrevEditors.name, 'prevTinymceElems[i]: '+prevTinymceElems[i]);
			prevTinymceElems[i].innerHTML = this._tempText;
			this._tempText = '';
		}
		
		prevTinymceElems[i].classList.toggle('edit-this', false);
	}
	
	for(i=0, len=saveLinks.length; i<len; i++) {
		if(saveLinks[i].textContent === 'Сохранить') {
			saveLinks[i].textContent = 'Редактировать';
		}
	}
};

// создаем TR с кнопками редактировать/удалить 
Admin.prototype.createEditCommentsTr = function createEditCommentsTr(commId) {
	'use strict';
	var doc = document;
	var tr = doc.createElement('TR');
	tr.classList.add('comments-edit');
	var editTd = doc.createElement('TD');
	var removeTd = doc.createElement('TD');
	var infoTd = doc.createElement('TD');
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
Admin.prototype.addEditBtn = function addEditBtn(elems) {
	'use strict';
	if(typeof tinymce === 'undefined') {
		appendScript('scripts/tinymce/tinymce.min.js');
	}
	
	var divElem = document.createElement('div');
	divElem.className = 'admin-edit-button';
	
	// создаем для каждого редактируемого элемента кнопку
	for(var i=0, len=elems.length, firstChild; i<len; i++) {
		this._editBtns[i] = divElem.cloneNode();
		firstChild = elems[i].children[0];
		elems[i].insertBefore(this._editBtns[i], firstChild);
	}
	
	this.initAdminEdit();
};

// вешаем на каждую кнопку событие на нажатие
Admin.prototype.initAdminEdit = function initAdminEdit() {
	'use strict';
	var self = this;
	for(var i=0, len=this._editBtns.length; i<len; i++) {
		(function() {
			self._editBtns[i].addEventListener('mouseup', self.addHandlerOnEditBtns.bind(self), false);
		})();
	}
};

// функция добавляет обработчики на кнопки DIVa, в котором редактируются данные новости/статьи
Admin.prototype.addHandlerOnEditBtns = function addHandlerOnEditBtns(e) {
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
				DEBUG(addHandlerOnEditBtns.name, 'Пришла не JSON строка: ' + e.toString());
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
			
			// удаляем другие объекты tinymce
			if(tinymce.editors.length > 0) {
				self.disablePrevEditors();
			}
			
			// делаем из textarea объект tinymce
			initTinyMCE('.admin-edit-elem textarea', false);
			
			// вешаем на кнопки события
			self._editDiv.addEventListener('mouseup', function(e) {
				var target = e.target;
				var targetText = target.textContent;
				e.preventDefault();
				
				if(targetText === 'Отменить') {
					e.stopPropagation();
					
					if(tinymce.activeEditor.id === 'comments-text') {
						if(tinymce.editors.length > 1 && tinymce.editors[1].id !== 'comments-text') {
							removeActiveTinymceEditors();
						}
					}
					else tinymce.activeEditor.destroy();
					
					document.body.removeChild(this);	// удаляем div редактирования
				}
				else if(targetText === 'Сохранить') {
					e.stopPropagation();
					var updatedText = tinymce.activeEditor.getContent();
					// запрос на сохранение элемента
					var reqTarget = (className.indexOf('news') > -1) ? 'news' : 'publs';
					self._sendSaveRequest({
						'id': id,
						'text': updatedText,
						'name': reqTarget+'_text'
					   },
					   'POST', 
					   'admin/admin_'+reqTarget+'/update_'+reqTarget+'.php', 
					   'application/x-www-form-urlencoded');
					
					if(tinymce.activeEditor.id === 'comments-text') {
						if(tinymce.editors.length > 1 && tinymce.editors[1].id !== 'comments-text') {
							removeActiveTinymceEditors();
						}
					}
					else tinymce.activeEditor.destroy();
					
					document.body.removeChild(this);	// удаляем div редактирования
				} 
				
			}, false);
		}
		else DEBUG(addHandlerOnEditBtns.name, response);
	});
};

// функция создает DIV элемент, в котором можно редактировать элементы новости или статьи
Admin.prototype._createEditDiv = function createEditDiv(className) {
	'use strict';
	var doc = document;
	var div = doc.createElement('div');
	var form = doc.createElement('form');
	var textarea = doc.createElement('textarea');
	var saveBtn = doc.createElement('a');
	var closeBtn = doc.createElement('a');
	saveBtn.textContent = 'Сохранить';
	saveBtn.setAttribute('href', '#');
	closeBtn.textContent = 'Отменить';
	closeBtn.setAttribute('href', '#');
	div.className = 'admin-edit-elem'; 
	
	var pattern = (className.indexOf('news') > -1) ? 'news' : 'publs';
	form.innerHTML = 'ID: ' + this._responseObject[pattern+'_id'] + ' | ' + 
					 'Загловок: ' + this._responseObject[pattern+'_header'];
	textarea.innerHTML = this._responseObject[pattern+'_text'];
	textarea.setAttribute('id', 'edit-textarea');

	form.appendChild(textarea);
	form.appendChild(saveBtn);
	form.appendChild(closeBtn);
	div.appendChild(form);
	doc.body.appendChild(div);
	
	this._editDiv = div;
};

// функция применяет изменения новости или статьи
/*
	argArr -> параметры (id, текст...) элемента
	reqType -> тип запроса (обычно POST)
	reqTarget -> какой php файл используем для сохранения 
	contentType -> Content-Type в заголовок
*/
Admin.prototype._sendSaveRequest = function sendSaveRequest(argArr, reqType, reqTarget, contentType) {
	'use strict';
	var data = '', j = 1;
	var self = this;
	
	for(var key in argArr) {
		var val = argArr[key];
		data += key + '=' + val;
		// Расставляем & перед параметрами
		if(Object.keys(argArr).length > j++) {
			data += '&';
		}
		else {
			DEBUG(sendSaveRequest.name, 'Параметр всего 1');
		}
	}
	DEBUG(sendSaveRequest.name, "data: " + data);
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function() {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout);
			if(self._XMLHttpRequest.status != 200) {
				DEBUG(sendSaveRequest.name, 'Ошибка: ' + self._XMLHttpRequest.responseText);
			}
			else {
				DEBUG(sendSaveRequest.name, 'Запрос отправлен. Ответ сервера: ' + self._XMLHttpRequest.responseText);
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
};

// функция делает AJAX запрос на выборку новости/статьи по ID
Admin.prototype._getElemByDBId = function getElemByDBId(className, id, callback) {
	'use strict';
	// новость или статья
	var pattern = (className.indexOf('news') > -1) ? 'news' : 'publs';
	var self = this;
	
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function () {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout); 
			
			if(self._XMLHttpRequest.status != 200) {			// сервер сказал "НЕТ"
				DEBUG(getElemByDBId.name, 'wha?');
			}
			else {
				var resp = self._XMLHttpRequest.responseText;
				if(resp != null) {								// в ответ что-то пришло
					if(typeof callback  == 'function') {
						callback.call(self._XMLHttpRequest);	// отдаем это в виде параметра в callback-функцию 
					}
				}
				else {
					DEBUG(getElemByDBId.name, "Херово!");
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
}

// для админа ставим кнопки редактирования
Admin.prototype.setPrivilege = function setPrivilege() {
	'use strict';
	var self = this;
	setTimeout(function() { 
		DEBUG(setPrivilege.name, 'admin: ' + self.getIsAdmin());
		
		if(self.getIsAdmin()) {
			//appendScript('scripts/tinymce/tinymce.min.js');
			self.checkForEditableContent();			// расставляем кнопки редактирования
			self.checkForComments();
		}
		else {
			DEBUG(setPrivilege.name, 'Вы не админ. Хватит хулиганить!');
		}
	}, 700);										// даем время на выполнение запроса в checkIfAdmin
};

addEventListenerWithOptions(document, 'DOMContentLoaded', createAdminClass, {passive: true});