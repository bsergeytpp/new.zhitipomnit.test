function Admin() {
	'use strict';
	this._XMLHttpRequest = null;
	this._editBtns = [];
	this._responseObject = '';
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
	'use strict';
	return this.getAdmin();
};

// получаем логин администратора
Admin.prototype.getAdminLogin = function() {
	'use strict';
	return this.getLogin();
};

// проверяем есть ли на странице редактируемые элементы
Admin.prototype.checkForEditableContent = function checkForEditableContent() {
	'use strict';
	var elems = getElems(['editable']);
	
	if(!elems) return;
	
	// есть хотя бы один элемент
	if(elems.length > 0) {
		this.addEditBtn(elems);
		this.initAdminEdit();
	}
};

// проверяем есть ли на странице редактируемые комментарии
Admin.prototype.checkForComments = function checkForComments() {
	'use strict';
	var commentsTables = getElems(['comments-table']);
	
	if(!commentsTables) return;
	
	this._commentsTables = commentsTables;
	this.addCommentsEditBtn();
	
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
	if(getElems(['comments-edit'])) return;
		
	for(var i=0, len=this._commentsTables.length; i<len; i++) {
		var commId = getElems(['comment-id', 0], this._commentsTables[i]);
		var editTr = this.createEditCommentsTr(getElems(['', 0, 'A'], commId).textContent);
		getElems(['', 0, 'TBODY'], this._commentsTables[i]).appendChild(editTr);
	}
	
	this.initCommentsEditBtns();
};

// вешаем события на кнопки редактировать/удалить/сохранить
Admin.prototype.initCommentsEditBtns = function initCommentsEditBtns() {
	'use strict';
	var self = this;
	
	for(var i=0, len=this._commentsTables.length; i<len; i++) {
		var btns = getElems(['admin-edit'], this._commentsTables[i]);
		
		// ES6 realization
		if(Array.from) {
			Array.from(btns).forEach(function(elem, index, array) {
				elem.addEventListener('click', this.addHandlerOnCommentsEditBtns.bind(this), false);
			}, self);
		}
		// ES6 realization 2
		/*for(var btn of btns) {
			btn.addEventListener('click', self.addHandlerOnCommentsEditBtns.bind(self), false);
		}*/
		// Old realization ES5
		else {
			for(var j=0, btnsLen=btns.length; j<btnsLen; j++) {
				btns[j].addEventListener('click', self.addHandlerOnCommentsEditBtns.bind(self), false);
			}
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

// инициализируем объект tinymce
Admin.prototype.initEditorForComment = function initEditorForComment(td) {
	'use strict';
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

// убираем предыдущий объект tinymce и меняем назначение кнопок
Admin.prototype.disablePrevEditors = function disablePrevEditors() {
	'use strict';	
	var prevTinymceElems = getElems(['edit-this']);
	var saveLinks = getElems(['edit-comm']);
	var activeEditorId = tinymce.activeEditor.getParam('id');
	
	if(!prevTinymceElems || !saveLinks) return;

	for(var i=0, len=tinymce.editors.length; i<len; i++) {
		if(tinymce.editors[i].id !== 'comments-text') {
			tinymce.remove('#'+tinymce.editors[i].id);
		}
	}
	
	for(i=0, len=prevTinymceElems.length; i<len; i++) {
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
	var tr = createDOMElem({tagName: 'TR', className: 'comments-edit'});
	var editTd = createDOMElem({tagName: 'TD', 
							    innerHTML: '<a href="#" class="admin-edit edit-comm " data-id="'+commId+'">Редактировать</a>'});
	var removeTd = createDOMElem({tagName: 'TD', 
								  innerHTML: '<a href="#" class="admin-edit del-comm" data-id="'+commId+'">Удалить</a>'});
	var infoTd = createDOMElem({tagName: 'TD', args: [{name: 'colspan', value: 3}], innerHTML: '<strong>Управление</strong>'});
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
	'use strict';
	var self = this;
	
	for(var i=0, len=this._editBtns.length; i<len; i++) {
		this._editBtns[i].addEventListener('mouseup', function(e) {
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
	'use strict';
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
	'use strict';
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
					var div = createDOMElem({tagName: 'DIV', className: 'admin-edit-elem', innerHTML: resp});
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
Admin.prototype.addAdminPanelLink = function addAdminPanelLink() {
	'use strict';
	var headerLinks = getElems(['header-links', 0]);
	var headerUl = getElems(['', 0, 'UL'], headerLinks);
	var liElem = createDOMElem({tagName: 'LI'});
	var linkElem = createDOMElem({
									tagName: 'A', 
									args: [{name: 'href', value: 'admin/index.php'}], 
									innerHTML: 'Админ Панель'
							    });
	liElem.appendChild(linkElem);
	headerUl.appendChild(liElem);
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
	DEBUG(setPrivilege.name, 'admin: ' + this.getIsAdmin());
	
	if(this.getIsAdmin()) {
		this.checkForEditableContent();			// расставляем кнопки редактирования
		this.checkForComments();
		this.addAdminPanelLink();				// добавляем ссылку на панель администратора
	}
	else {
		DEBUG(setPrivilege.name, 'Вы не админ. Хватит хулиганить!');
	}
};

addEventListenerWithOptions(document, 'DOMContentLoaded', createAdminClass, {passive: true});