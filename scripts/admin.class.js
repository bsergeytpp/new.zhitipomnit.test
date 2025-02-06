'use strict';
function Admin() {
	this._XMLHttpRequest = null;
	this._editBtns = [];
	this._responseObject = '';
	this._editDiv = null;
	this._commentsTables = null;
	this._guestbookForms = null;
	this._tempText = '';
	
	var isAdmin = false;
	var self = this;
	var login = null;
	
	// выясняем являемся ли мы админом
	(function() {
		self._XMLHttpRequest = new XMLHttpRequest();
		self._XMLHttpRequest.onreadystatechange = function () {
			if(this.readyState == 4) {
				clearTimeout(timeout);
				var resp = this.getResponseHeader('IsAdmin');
				var respLogin = this.getResponseHeader('UserLogin');
				DEBUG("checkIfAdmin", "RESP: "+resp);
				
				if(respLogin !== null) {
					if(resp !== null) {
						DEBUG("checkIfAdmin", "Вы - Админ. Поздравляю!");
						isAdmin = true;
						login = respLogin;
						User = user = null;
						self.setPrivilege();
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
}

// получаем isAdmin
Admin.prototype.getIsAdmin = function() {
	return this.getAdmin();
};

// получаем логин администратора
Admin.prototype.getAdminLogin = function() {
	return this.getLogin();
};

// проверяем есть ли на странице редактируемые элементы
Admin.prototype.checkForEditableContent = function checkForEditableContent() {
	var elems = getElems(['editable']);
	
	if(!elems) return;
	
	// есть хотя бы один элемент
	if(elems.length > 0) {
		this.addEditBtn(elems);
		this.initAdminEdit();
	}
};

// проверяем есть ли на странице сообщения гостевой книги
Admin.prototype.checkForGuestbook = function checkForGuestbook() {
	var guestbookForms = getElems(['guestbook-message']);
	
	if(!guestbookForms) return;
	
	this._guestbookForms = guestbookForms;
	this.addGuestbookEditBtn();
	
};

// добавляем кнопку редактирования сообщений гостевой книги
Admin.prototype.addGuestbookEditBtn = function addGuestbookEditBtn() {
	// если сообщения есть
	if(this._guestbookForms === null) return;
	
	// если кнопок пока нет
	if(getElems(['gb-edit-button'])) return;
		
	for(var formElem of this._guestbookForms) {
		var gbId = formElem.id;
		var editBtn = createDOMElem({
			tagName: 'A', 
			args: [{name: 'href', value: '#'}, {name: 'data-id', value: gbId.substr(gbId.indexOf('-')+1)}], 
			className: 'gb-edit-button', 
			innerText: 'Редактировать'
		});
		var deleteBtn = createDOMElem({
			tagName: 'A', 
			args: [{name: 'href', value: '#'}, {name: 'data-id', value: gbId.substr(gbId.indexOf('-')+1)}], 
			className: 'gb-edit-button', 
			id: 'gb-'+gbId.substr(gbId.indexOf('-')+1),
			innerText: 'Удалить'
		});
		formElem.appendChild(editBtn);
		formElem.appendChild(deleteBtn);
	}
	
	this.initGuestbookEditBtns();
};

// вешаем события на кнопки редактирования гостевой книги
Admin.prototype.initGuestbookEditBtns = function initGuestbookEditBtns() {
	var self = this;
	
	for(var formElem of this._guestbookForms) {
		var btns = getElems(['gb-edit-button'], formElem);
		
		for(var btn of btns) {
			btn.addEventListener('click', self.addHandlerOnGuestbookEditBtns.bind(self), false);
		}
	}
};

// описываем события для кнопок редактирования гостевой книги
Admin.prototype.addHandlerOnGuestbookEditBtns = function addHandlerOnGuestbookEditBtns(e) {
	var target = e.target;
	var self = this;
	var targetId = target.getAttribute('data-id');
	var targetText = target.textContent;
	
	if(checkClass(target, ['gb-edit-button'])) {
		e.preventDefault();
		e.stopPropagation();
		
		switch(targetText) {
			case 'Редактировать': editGuestbookMessage.call(self, target, targetId); break;
			case 'Сохранить': saveGuestbookMessage.call(self, targetId); break;
			case 'Удалить': deleteGuestbookMessage.call(self, targetId); break;
			default: break;
		}
	}
};

function editGuestbookMessage(btn, gbId) {
	// если есть форма сообщения гостевой, то пропускаем ее
	var totalEditors = tinymce.editors.length;
	var gbEditors = (tinymce.activeEditor.getElement.id === 'guestbook-text') ? totalEditors - 1 : totalEditors;
	
	if(totalEditors > 1) {
		if(tinymce.editors[1].id === 'edit-textarea') {
			alert("Закончите текущее редактирование!");
			return;
		}
		
		if(tinymce.activeEditor.id === 'guestbook-text') {
			tinymce.editors[1].focus();
		}
	}
	
	if(gbEditors > 1) {
		var currentEditId = tinymce.activeEditor.targetElm.parentElement.id;

		if(confirm('Уже начато редактирование сообщения №'+currentEditId+
				   '. Отменить изменения и редактировать сообщение №'+gbId+' ?')) {
			this.disablePrevEditors();
			this.initEditorForGuestbook(btn);
		}
		else return; 			
	} 
	else {		
		this.initEditorForGuestbook(btn);
	}
}

function saveGuestbookMessage(gbId) {
	// выбрана форма добавления сообщения
	if(tinymce.editors.length > 1 && tinymce.activeEditor.id === 'guestbook-text') {
		tinymce.editors[1].focus();
	}
	
	var updatedText = tinymce.activeEditor.getContent({ format: 'text' });
	removeActiveTinymceEditors();

	DEBUG(saveGuestbookMessage.name, gbId + "|" + updatedText);
	// запрос на сохранение элемента
	this._sendSaveRequest({
		'gb-id': gbId,
		'gb-text': updatedText,
	   },
	   'POST', 
	   'admin/admin_guestbook/update_guestbook.php', 
	   'application/x-www-form-urlencoded');
	updateGuestbookDiv();
}

function deleteGuestbookMessage(gbId) {
	if(confirm('Точно удалить сообщение №'+gbId+'?')) { 
		DEBUG(deleteGuestbookMessage.name, 'Удаление: '+gbId);			
		removeActiveTinymceEditors();
		this._sendSaveRequest({
			'gb-id': gbId
		   },
		   'POST', 
		   'admin/admin_guestbook/delete_guestbook.php', 
		   'application/x-www-form-urlencoded');
		updateGuestbookDiv();
	}
}

// проверяем есть ли на странице редактируемые комментарии
Admin.prototype.checkForComments = function checkForComments() {
	var commentsTables = getElems(['comments-table']);
	
	if(!commentsTables) return;
		
	this._commentsTables = commentsTables;
	this.addCommentsEditBtn();
	
};

// добавляем еще один TR к каждому комментарию
Admin.prototype.addCommentsEditBtn = function addCommentsEditBtn() {
	if(typeof tinymce === 'undefined') {
		appendScript('scripts/tinymce/tinymce.min.js');							
	}
	
	// если комментарии есть
	if(this._commentsTables === null) return;
	
	// если кнопок пока нет
	if(getElems(['comments-edit'])) return;
		
	for(var tableElem of this._commentsTables) {
		if(tableElem.className.includes('deleted')) continue;

		var commId = getElems(['comment-id', 0], tableElem);
		var editTr = this.createEditCommentsTr(commId.textContent);
		getElems(['', 0, 'TBODY'], tableElem).appendChild(editTr);
	}
	
	this.initCommentsEditBtns();
};

// вешаем события на кнопки редактировать/удалить/сохранить
Admin.prototype.initCommentsEditBtns = function initCommentsEditBtns() {
	var self = this;
	
	for(var tableElem of this._commentsTables) {
		var btns = getElems(['admin-edit'], tableElem);
		
		for(var btn of btns) {
			btn.addEventListener('click', self.addHandlerOnCommentsEditBtns.bind(self), false);
		}
	}
};

// описываем события для кнопок (редактировать/удалить/сохранить)
Admin.prototype.addHandlerOnCommentsEditBtns = function addHandlerOnCommentsEditBtns(e) {
	var target = e.target;
	var self = this;
	var targetId = target.getAttribute('data-id');
	var targetText = target.textContent;
	
	if(checkClass(target, ['edit-comm'])) {
		e.preventDefault();
		e.stopPropagation();
		
		if(targetText === 'Редактировать') {
			editComments.call(self, target, targetId);
		}
		else if(targetText === 'Сохранить') {
			saveComments.call(self, targetId);
		}
	}
	else if(checkClass(target, ['del-comm'])) {
		e.preventDefault();
		e.stopPropagation();
		deleteComments.call(self, targetId);
 	}
};

/*
	Вспомогательные функции редактирования/удаления/сохранения комментариев
*/
function editComments(td, tdId) {	
	// если есть форма комментирования, то пропускаем ее
	var totalEditors = (tinymce.activeEditor.getElement.id === 'comments-text') ? 1 : 2;
	
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
		prevEditId = getElems(['comment-id', 0], prevEditor).textContent;

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

// удаляем комментарии
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
	if(tinymce.editors.length > 1 && tinymce.activeEditor.id === 'comments-text') {
		tinymce.editors[1].focus();
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

// инициализируем объекты tinymce
Admin.prototype.initEditorForComment = function initEditorForComment(td) {
	var tdParent = findParent(td, 'comments-table');
	
	if(tdParent === null) return;
	
	var commentsTextTd = getElems(['comment-text', 0], tdParent); // нашли текст комментария
	var commentsText = commentsTextTd.innerHTML;
	var editPos = commentsText.indexOf('<br><em class=');
	
	if(editPos !== -1) {
		this._tempText = commentsText;
		commentsTextTd.innerHTML = commentsText.substr(0, editPos);
	}
	
	DEBUG(initEditorForComment.name, 'commentsTextTd: '+commentsTextTd);
	DEBUG(initEditorForComment.name, 'Редактирование: '+td.getAttribute('data-id'));

	commentsTextTd.classList.add('edit-this');
	td.textContent = 'Сохранить';
	initTinyMCE('.edit-this', true, 'auto', 'auto');
};

// инициализируем объект tinymce
Admin.prototype.initEditorForGuestbook = function initEditorForGuestbook(btn) {
	var gbForm = findParent(btn, 'guestbook-message');
	
	if(gbForm === null) return;
	
	var gbTextarea = getElems(['', 0, 'TEXTAREA'], gbForm);
	gbTextarea.removeAttribute('disabled');
	
	DEBUG(initEditorForGuestbook.name, 'gbTextarea: '+gbTextarea);
	DEBUG(initEditorForGuestbook.name, 'Редактирование: '+btn.getAttribute('data-id'));

	gbTextarea.classList.add('edit-this');
	btn.textContent = 'Сохранить';
	initTinyMCE('.edit-this', false, 'auto', 'auto');
	// TODO: добавить возможность отключения панелей в initTinyMCE
	//getElems(['mce-menubar', 0]).style.display = 'none';
	//getElems(['mce-toolbar-grp', 0]).style.display = 'none';;
};

// убираем предыдущий объект tinymce и меняем назначение кнопок
Admin.prototype.disablePrevEditors = function disablePrevEditors() {
	var prevTinymceElems = getElems(['edit-this']);
	var saveLinks = getElems(['edit-comm']) || getElems(['gb-edit-button']);
	var activeEditorId = tinymce.activeEditor.getParam('id');
	
	if(!prevTinymceElems || !saveLinks) return;

	for(var tinymceEditor of tinymce.editors) {
		if(tinymceEditor.id === 'comments-text' ||
		   tinymceEditor.id === 'guestbook-text') continue;
		
		tinymce.remove('#'+tinymceEditor.id);
	}
	
	for(var prevTinymceElem of prevTinymceElems) {
		if(this._tempText !== '') {
			DEBUG(disablePrevEditors.name, 'this._tempText: '+this._tempText);
			DEBUG(disablePrevEditors.name, 'prevTinymceElem: '+prevTinymceElem);
			prevTinymceElem.innerHTML = this._tempText;		// innerHTML -> ?
			this._tempText = '';
		}
		
		prevTinymceElem.classList.toggle('edit-this', false);
	}
	
	for(var linkElem of saveLinks) {
		if(linkElem.textContent !== 'Сохранить') continue;
		
		linkElem.textContent = 'Редактировать';
	}
};

// создаем TR с кнопками редактировать/удалить 
Admin.prototype.createEditCommentsTr = function createEditCommentsTr(commId) {
	var tr = createDOMElem({tagName: 'TR', className: 'comments-edit'});
	var editTd = createDOMElem({tagName: 'TD'});
	var removeTd = editTd.cloneNode();
	var infoTd = editTd.cloneNode();
		
	var editLink = createDOMElem({
		tagName: 'A',
		args: [{name: 'href', value: '#'}, {name: 'data-id', value: commId}], 
		className: 'admin-edit edit-comm ',
		innerText: 'Редактировать'
	});
	
	editTd.appendChild(editLink);
	
	var delLink = createDOMElem({
		tagName: 'A',
		args: [{name: 'href', value: '#'}, {name: 'data-id', value: commId}], 
		className: 'admin-edit del-comm ',
		innerText: 'Удалить'
	});
	
	removeTd.appendChild(delLink);
	
	infoTd.setAttribute('colspan', 1);
	
	var infoLink = createDOMElem({
		tagName: 'STRONG',
		innerText: 'Управление'
	});
	
	infoTd.appendChild(infoLink);
	
	tr.appendChild(infoTd);
	tr.appendChild(editTd);
	tr.appendChild(removeTd);
	
	return tr;
};

// функция для добавления кнопки редактирования (новости/статьи)
Admin.prototype.addEditBtn = function addEditBtn(elems) {
	if(typeof tinymce === 'undefined') {
		appendScript('scripts/tinymce/tinymce.min.js');
	}
	
	var divElem = createDOMElem({tagName: 'DIV', className: 'admin-edit-button'});
	
	// создаем для каждого редактируемого элемента кнопку
	for(var i=0, len=elems.length, firstChild; i<len; i++) {
		this._editBtns[i] = divElem.cloneNode();
		firstChild = elems[i].children[0];
		elems[i].insertBefore(this._editBtns[i], firstChild);
	}
};

// вешаем на каждую кнопку событие на нажатие
Admin.prototype.initAdminEdit = function initAdminEdit() {
	var self = this;
	
	for(var editBtn of this._editBtns) {
		editBtn.addEventListener('mouseup', function(e) {
			var id = e.target.parentNode.getAttribute('id');
			var className = e.target.parentNode.className;
			var pattern = (className.indexOf('news') > -1) ? 'news' : 'publs';
			
			// ищем элементы для редактирования по их ID
			self._getElemByDBId(pattern, id, function() {
				var response = this.responseText;
				
				// вернулась строка
				if(typeof response === 'string') {
					try{
						self._responseObject = JSON.parse(response);
					}
					catch(e) {
						DEBUG(addHandlerOnEditBtns.name, 'Пришла не JSON строка: ' + e.toString());
					}
				}
				
				// строка оказалась формата JSON
				if(typeof self._responseObject === 'object') {
					var editElem = getElems(['admin-edit-elem', 0]);
					
					if(editElem) {
						self._editDiv = null;
						document.body.removeChild(editElem);
					}

					// создаем все необходимые элементы
					self._createEditDiv(pattern, createEditDivCallback.bind(self, pattern, id));
				}
				else DEBUG(addHandlerOnEditBtns.name, response);
			});
		}, false);
	}
};

// функция делает AJAX запрос на выборку новости/статьи по ID
Admin.prototype._getElemByDBId = function getElemByDBId(pattern, id, callback) {
	var self = this;
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function () {
		if(this.readyState == 4) {
			clearTimeout(timeout); 
			
			if(this.status != 200) {							// сервер сказал "НЕТ"
				DEBUG(getElemByDBId.name, 'wha?');
			}
			else {
				if(this.responseText != null) {					// в ответ что-то пришло
					if(typeof callback  == 'function') {
						callback.call(this);					// отдаем это в виде параметра в callback-функцию 
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

// функция создает DIV элемент для редактирования новости или статьи
Admin.prototype._createEditDiv = function createEditDiv(pattern, callback) {
	var self = this;
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function () {
		if(this.readyState == 4) {
			clearTimeout(timeout); 
			
			if(this.status != 200) {			// сервер сказал "НЕТ"
				DEBUG(getElemByDBId.name, 'wha?');
			}
			else {
				var resp = this.responseText;
				if(resp != null) {								// в ответ что-то пришло
					resp = resp.replace('$id$', self._responseObject[pattern+'_id']);
					resp = resp.replace('$header$', self._responseObject[pattern+'_header']);
					resp = resp.replace('$inner$', self._responseObject[pattern+'_text']);
					var div = createDOMElem({tagName: 'DIV', className: 'admin-edit-elem'});
					div.innerHTML = resp;
					document.body.appendChild(div);
					self._editDiv = div;
					
					if(typeof callback === 'function') {
						callback.call(self);
					}
				}
				else {
					
				}	
			}
		}
	};
	
	var timeout = setTimeout(function() {
		self._XMLHttpRequest.abort();
	}, 60*1000);
	this._XMLHttpRequest.open('GET', 'content/templates/editform_template.php', true);
	this._XMLHttpRequest.send();
};

// создаем элемент для редактирования
function createEditDivCallback(pattern, id) {
	var self = this;
	// удаляем другие объекты tinymce
	if(tinymce.editors.length > 0) {
		this.disablePrevEditors();
	}
	
	// делаем из textarea объект tinymce
	initTinyMCE('.admin-edit-elem textarea', false);
	
	// вешаем на кнопки события
	this._editDiv.addEventListener('mouseup', function(e) {
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
			self._sendSaveRequest({
				'id': id,
				'text': updatedText,
				'name': pattern+'_text'
			   },
			   'POST', 
			   'admin/admin_'+pattern+'/update_'+pattern+'.php', 
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

// функция применяет изменения новости или статьи
/*
	argArr -> параметры (id, текст...) элемента
	reqType -> тип запроса (обычно POST)
	reqTarget -> какой php файл используем для сохранения 
	contentType -> Content-Type в заголовок
*/
Admin.prototype._sendSaveRequest = function sendSaveRequest(argArr, reqType, reqTarget, contentType) {
	var data = '';
	var	j = 1;
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
		if(this.readyState == 4) {
			clearTimeout(timeout);
			if(this.status != 200) {
				DEBUG(sendSaveRequest.name, 'Ошибка: ' + this.responseText);
			}
			else {
				DEBUG(sendSaveRequest.name, 'Запрос отправлен. Ответ сервера: ' + this.responseText);
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

// создаем панель администратора
// TODO: проверять на наличии ссылки перед добавлением (AJAX)
Admin.prototype.addAdminPanelLink = function addAdminPanelLink() {
	var headerLinks = getElems(['header-links', 0]);
	var headerUl = getElems(['', 0, 'UL'], headerLinks);
	var liElem = createDOMElem({tagName: 'LI'});
	var linkElem = createDOMElem({
									tagName: 'A', 
									args: [{name: 'href', value: 'admin/index.php'}], 
									innerText: 'Админ Панель'
							    });
	liElem.appendChild(linkElem);
	headerUl.appendChild(liElem);
};

// создаем объект класса Admin
var admin = null;
function createAdminClass() {
	admin = new Admin();					
}

// для админа ставим кнопки редактирования
Admin.prototype.setPrivilege = function setPrivilege() {
	DEBUG(setPrivilege.name, 'admin: ' + this.getIsAdmin());
	
	if(this.getIsAdmin()) {
		this.checkForEditableContent();			// расставляем кнопки редактирования
		this.checkForComments();
		this.checkForGuestbook();
		this.addAdminPanelLink();				// добавляем ссылку на панель администратора
	}
	else {
		DEBUG(setPrivilege.name, 'Вы не админ. Хватит хулиганить!');
	}
};

addEventListenerWithOptions(document, 'DOMContentLoaded', createAdminClass, {passive: true});