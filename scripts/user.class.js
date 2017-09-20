function User() {
	'use strict';
	this._XMLHttpRequest = null;
	this._commentsTables = null;
	this._userComments = null;
	this._tempText = '';
	
	var userLogin = false;
	var self = this;
	
	// выясняем текущий логин
	var checkIfUser = function() {
		self._XMLHttpRequest = new XMLHttpRequest();
		self._XMLHttpRequest.onreadystatechange = function () {
			if(self._XMLHttpRequest.readyState == 4) {
				clearTimeout(timeout);
				var resp = self._XMLHttpRequest.getResponseHeader('UserLogin');
				var isAdmin = self._XMLHttpRequest.getResponseHeader('IsAdmin');
				
				if(isAdmin) {
					return;
				}

				if(resp !== null) {
					DEBUG(checkIfUser.name, "Ваш логин: "+resp);
					userLogin = resp;
					
					// хак-проверка на админа, чтобы не было лишних TR'ов
					if(!getElems(['comments-edit'])) {
						self.checkForUserComments();
					}
				}
				else {
					DEBUG(checkIfUser.name, "Вы - не авторизованы!");
					User = null;
					user = null;
				}
			}
		};
		var timeout = setTimeout(function() {
			self._XMLHttpRequest.abort();
		}, 60*1000);
		self._XMLHttpRequest.open('HEAD', 'content/json.php', true);
		self._XMLHttpRequest.send();
	};
	
	checkIfUser();
	
	this.getUserLogin = function() {
		return userLogin;
	};
}

// ищем комментарии пользователя
User.prototype.checkForUserComments = function checkForUserComments() {
	'use strict';
	var commentsTables = getElems(['comments-table']);
	var self = this;
	
	if(!commentsTables) return;
	
	var location_id = getParamFromLocationSearch('id');
	
	this.getUserCommentsFromId(location_id, function() {
		var response = this.responseText;
		var responseObject = null;
		DEBUG(checkForUserComments.name, response + ' это объект');
	
		if(typeof response === 'string') {
			try {
				responseObject = JSON.parse(response);
			}
			catch(e) {
				DEBUG(checkForUserComments.name, 'Пришла не JSON строка: ' + e.toString());
			}
		}
		
		if(responseObject !== null) {
			DEBUG(checkForUserComments.name, response + ' это объект');				
			self.addCommentsEditBtn(responseObject);
		}
		else {
			DEBUG(checkForUserComments.name, response);
		}
	});
};

// расставляем элементы редактирования комментария
User.prototype.addCommentsEditBtn = function addCommentsEditBtn(commentsIds) {
	'use strict';
	var commTables = getElems(['comments-table']);

	// если есть комментарии
	if(!commTables) return;
	
	// если пришли ID
	if(commentsIds === null) return;

	this._userComments = this.getUserComments(commTables, commentsIds);
	
	for(var i=0, len=this._userComments.length; i<len; i++) {
		var commId = getElems(['comment-id', 0], this._userComments[i]).textContent;
		var editTr = this.createEditCommentsTr(commId);
		getElems(['', 0, 'TBODY'], this._userComments[i]).appendChild(editTr);
	}
	
	this.initCommentsEditBtns();
};

// проверяем ID комментария
User.prototype.checkId = function checkId(id, object) {
	for(var i=0, len=object.length; i<len; i++) {
		if(parseInt(id) === object[i]['comments_id']) {
			return true;
		}
	}
	
	return false;
};

// получаем комментарии пользователя
User.prototype.getUserComments = function getUserComments(commTables, commentsIds) {
	var temp = [];
	
	for(var i=0, len=commTables.length; i<len; i++) {
		// commTables -> .comments-content -> .comment-id
		var commId = getElems(['comment-id', 0], commTables[i]).textContent;	
		DEBUG(getUserComments.name, commId);
		if(this.checkId(commId, commentsIds)) {
			DEBUG(getUserComments.name, 'cut '+i);
			temp.push(commTables[i]);
		}
	}
	
	return temp;
};

// вешаем события на кнопки редактировать/удалить/сохранить
User.prototype.initCommentsEditBtns = function initCommentsEditBtns() {
	'use strict';
	var self = this;
	
	for(var i=0, len=this._userComments.length; i<len; i++) {
		var btns = getElems(['user-edit'], this._userComments[i]);
		
		for(var j=0, btnsLen=btns.length; j<btnsLen; j++) {
			btns[j].addEventListener('mouseup', self.addHandlerOnCommentsEditBtns.bind(self), false);
		}
	}
};

// описываем события для кнопок (редактировать/удалить/сохранить)
User.prototype.addHandlerOnCommentsEditBtns = function addHandlerOnCommentsEditBtns(e) {
	'use strict';
	var target = e.target;
	var self = this;
	var targetId = target.getAttribute('data-id');
	
	if(target.classList.contains('edit-comm')) {
		e.preventDefault();
		var targetText = target.textContent;
		
		if(targetText === 'Редактировать') {
			userEditComment.call(self, target, targetId);
		}
		else if(targetText === 'Сохранить') {
			e.stopPropagation();
			userSaveComments.call(self, targetId);
		}
	}
};

/*
	Вспомогательные функции редактирования/сохранения комментариев
*/
function userEditComment(td, tdId) {
	var totalEditors = 1;
	
	if(tinymce.activeEditor.getElement.id === 'comments-text') {	// если есть форма комментирования, то пропускаем ее
		totalEditors = 2;
	} 
	
	if(tinymce.editors.length > 1) {
		if(tinymce.activeEditor.id === 'comments-text') {			// была выбрана форма комментирования
			tinymce.editors[1].focus();
		}
	}
	
	if(tinymce.editors.length > totalEditors) {						// уже есть редактируемый комментарий
		var prevEditor = tinyMCE.activeEditor.bodyElement.parentElement;
		prevEditId = getElems(['comment-id', 0], prevEditor).textContent;
		
		if(confirm('Уже начато редактирование комментария №'+prevEditId+'. Отменить изменения и редактировать комментарий №'+tdId+' ?')) {
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

// сохраняем измененный текст комментария
function userSaveComments(tdId) {
	var updatedText = tinymce.activeEditor.getContent();
	var activeEditorId = tinymce.activeEditor.getParam('id');
	removeActiveTinymceEditors();
	this._tempText = '';
	// запрос на сохранение элемента
	this._sendSaveRequest({
		'comment-id': tdId,
		'comment-text': updatedText
	   },
	   'POST', 
	   'users/user_update_comment.php', 
	   'application/x-www-form-urlencoded');
}

// инициализируем объект tinymce
User.prototype.initEditorForComment = function initEditorForComment(td) {
	'use strict';
	var tdParent = findParent(td, 'comments-table');
	DEBUG(initEditorForComment.name, "tdElem: " + td);
	
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
User.prototype.disablePrevEditors = function disablePrevEditors() {
	'use strict';
	var prevTinymceElems = getElems(['edit-this']);
	var saveLinks = getElems(['edit-comm']);
	var activeEditorId = tinymce.activeEditor.getParam('id');
	
	// убираем редактор комментариев
	for(var i=0, len=tinymce.editors.length; i<len; i++) {
		if(tinymce.editors[i].id !== 'comments-text') {
			tinymce.remove('#'+tinymce.editors[i].id);
		}
	}
	
	// убираем все внесенные изменения
	for(i=0, len=prevTinymceElems.length; i<len; i++) {
		if(this._tempText !== '') {
			DEBUG(disablePrevEditors.name, 'this._tempText: '+this._tempText);
			DEBUG(disablePrevEditors.name, 'prevTinymceElems[i]: '+prevTinymceElems[i]);
			prevTinymceElems[i].innerHTML = this._tempText;
			this._tempText = '';
		}
		
		prevTinymceElems[i].classList.toggle('edit-this', false);
	}
	
	// меняем текст кнопки
	for(i=0, len=saveLinks.length; i<len; i++) {
		if(saveLinks[i].textContent === 'Сохранить') {
			saveLinks[i].textContent = 'Редактировать';
		}
	}
};

// создаем TR с кнопками редактировать/удалить 
User.prototype.createEditCommentsTr = function createEditCommentsTr(commId) {
	'use strict';
	var tr = createDOMElem({tagName: 'TR', className: 'comments-edit'});
	var editTd = createDOMElem({tagName: 'TD', 
								innerHTML: '<a href="#" class="user-edit edit-comm" data-id="'+commId+'">Редактировать</a>'});
	var infoTd = createDOMElem({tagName: 'TD', args: [{name: 'colspan', value: 4}], innerHTML: '<strong>Управление</strong>'});
	tr.appendChild(infoTd);
	tr.appendChild(editTd);
	
	return tr;
};

// ищем комментарии пользователя по его ID
User.prototype.getUserCommentsFromId = function getUserCommentsFromId(location_id, callback) {
	'use strict';
	var self = this;
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function () {
		if(this.readyState == 4) {
			clearTimeout(timeout); 
			
			if(this.status != 200) {			
				DEBUG(getUserCommentsFromId.name, 'wha?');
			}
			else {
				var resp = this.responseText;
				DEBUG(getUserCommentsFromId.name, 'Пришло: '+resp);
				if(resp !== null) {								// в ответ что-то пришло
					if(typeof callback == 'function') {			
						callback.call(this);					// отдаем это в виде параметра в callback-функцию 
					}											
				}
				else {
					DEBUG(getUserCommentsFromId.name, "Херово!");
				}	
			}
		}
	};
	
	var timeout = setTimeout(function() {
		self._XMLHttpRequest.abort();
	}, 60*1000);
	this._XMLHttpRequest.open('GET', 'users/user_comments.php?login=' + this.getUserLogin() 
							+ '&comments-location-id=' + encodeURIComponent(location_id), true);
	this._XMLHttpRequest.send();
};

// отправляем запрос на сохранение
User.prototype._sendSaveRequest = function sendSaveRequest(argArr, reqType, reqTarget, contentType) {
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
	DEBUG(sendSaveRequest.name, "data: "+data);
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function() {
		if(this.readyState == 4) {
			clearTimeout(timeout);
			if(this.status != 200) {
				DEBUG(sendSaveRequest.name, 'Ошибка: ' + this.responseText);
			}
			else {
				DEBUG(sendSaveRequest.name, 'Запрос отправлен. Ответ сервера: ' + this.responseText);
				updateCommentsWrapper();
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

// создаем объект класса User
var user;
function createUserClass() {
	'use strict';
	user = new User();
}

//addEventListenerWithOptions(document, 'DOMContentLoaded', createUserClass, {passive: true});