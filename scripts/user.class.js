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
		'use strict';
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
					if(document.getElementsByClassName('comments-edit').length === 0) {
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
};

// ищем комментарии пользователя
User.prototype.checkForUserComments = function checkForUserComments() {
	'use strict';
	var commentsTables = document.getElementsByClassName('comments-table');;
	var self = this;
	
	if(commentsTables.length > 0) {
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
	}
};

User.prototype.addCommentsEditBtn = function addCommentsEditBtn(commentsIds) {
	'use strict';
	var commTables = document.getElementsByClassName('comments-table');

	// если есть комментарии
	if(!commTables) return;
	
	// если пришли ID
	if(commentsIds === null) return;

	this._userComments = this.getUserComments(commTables, commentsIds);
	
	for(var i=0, len=this._userComments.length; i<len; i++) {
		var commId = this._userComments[i].getElementsByClassName('comment-id')[0].textContent;
		var editTr = this.createEditCommentsTr(commId);
		this._userComments[i].getElementsByTagName('TBODY')[0].appendChild(editTr);
	}
	
	this.initCommentsEditBtns();
};

User.prototype.checkId = function checkId(id, object) {
	for(var i=0, len=object.length; i<len; i++) {
		if(parseInt(id) === object[i]['comments_id']) {
			return true;
		}
	}
	
	return false;
};
	
User.prototype.getUserComments = function getUserComments(commTables, commentsIds) {
	var temp = [];
	
	for(var i=0, len=commTables.length; i<len; i++) {
		// commTables -> .comments-content -> .comment-id
		var commId = commTables[i].getElementsByClassName('comment-id')[0].textContent;	
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
		var btns = this._userComments[i].getElementsByClassName('user-edit');
		
		for(var j=0, btnsLen=btns.length; j<btnsLen; j++) {
			(function() {
				btns[j].addEventListener('mouseup', self.addHandlerOnCommentsEditBtns.bind(self), false);
			})();
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
		prevEditId = prevEditor.getElementsByClassName('comment-id')[0].textContent;
		
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
	
	var commentsTextTd = tdParent.getElementsByClassName('comment-text')[0]; // нашли текст комментария
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
User.prototype.createEditCommentsTr = function createEditCommentsTr(commId) {
	'use strict';
	var doc = document;
	var tr = doc.createElement('TR');
	tr.classList.add('comments-edit');
	var editTd = doc.createElement('TD');
	var infoTd = doc.createElement('TD');
	infoTd.setAttribute('colspan', 4);
	infoTd.innerHTML = '<strong>Управление</strong>';
	editTd.innerHTML = '<a href="#" class="user-edit edit-comm" data-id="'+commId+'">Редактировать</a>';
	tr.appendChild(infoTd);
	tr.appendChild(editTd);
	
	return tr;
};

User.prototype.getUserCommentsFromId = function getUserCommentsFromId(location_id, callback) {
	'use strict';
	var self = this;
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function () {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout); 
			
			if(self._XMLHttpRequest.status != 200) {			
				DEBUG(getUserCommentsFromId.name, 'wha?');
			}
			else {
				var resp = self._XMLHttpRequest.responseText;
				DEBUG(getUserCommentsFromId.name, 'Пришло: '+resp);
				if(resp !== null) {								// в ответ что-то пришло
					if(typeof callback == 'function') {			
						callback.call(self._XMLHttpRequest);	// отдаем это в виде параметра в callback-функцию 
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
}

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
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout);
			if(self._XMLHttpRequest.status != 200) {
				DEBUG(sendSaveRequest.name, 'Ошибка: ' + self._XMLHttpRequest.responseText);
			}
			else {
				DEBUG(sendSaveRequest.name, 'Запрос отправлен. Ответ сервера: ' + self._XMLHttpRequest.responseText);
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
}

// создаем объект класса User
var user;
function createUserClass() {
	'use strict';
	user = new User();
}

//addEventListenerWithOptions(document, 'DOMContentLoaded', createUserClass, {passive: true});