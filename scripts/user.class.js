/*
	1) сделать User "подклассом" Admin или наоборот
	2) сделать из некоторых переменных свойства объекта 
	   (userLogin -> this._userLogin и т.д.)
*/

function User() {
	'use strict';
	this._XMLHttpRequest = null;
	this._commentsTables = null;
	this._userComments = null;
	
	var userLogin = false;
	var isAdmin = false;
	var self = this;
	
	// выясняем текущий логин
	var checkIfUser = function() {
		'use strict';
		self._XMLHttpRequest = new XMLHttpRequest();
		self._XMLHttpRequest.onreadystatechange = function () {
			if(self._XMLHttpRequest.readyState == 4) {
				clearTimeout(timeout);
				var resp = self._XMLHttpRequest.getResponseHeader('UserLogin');
				isAdmin = self._XMLHttpRequest.getResponseHeader('IsAdmin');
				
				if(isAdmin) {
					return;
				}

				if(resp !== null) {
					DEBUG("func: checkIfUser; output: Ваш логин: "+resp);
					userLogin = resp;
					
					// хак-проверка на админа, чтобы не было лишних TR'ов
					if(document.getElementsByClassName('comments-edit').length === 0) {
						self.checkForUserComments();
					}
				}
				else {
					DEBUG("func: checkIfUser; output: Вы не авторизованы!");
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
User.prototype.checkForUserComments = function() {
	'use strict';
	var commentsTables = null;
	var self = this;
	
	if(document.getElementsByClassName('comments-table').length > 0) {
		commentsTables = document.getElementsByClassName('comments-table');
		var location_id = getParamFromLocationSearch('id');
		
		this.getUserCommentsFromId(location_id, function() {
			var response = this.responseText;
			var responseObject = null;
			DEBUG('func: checkForUserComments; output: ' + response + ' это объект');
		
			if(typeof response === 'string') {
				try {
					responseObject = JSON.parse(response);
				}
				catch(e) {
					DEBUG('func: checkForUserComments; Пришла не JSON строка: ' + e.toString());
				}
			}
			
			if(responseObject !== null) {
				DEBUG('func: checkForUserComments; output: ' + response + ' это объект');				
				self.addCommentsEditBtn(responseObject);
			}
			else {
				DEBUG('func: checkForUserComments; output: ' + response);
			}
		});
		//this.addCommentsEditBtn();
	}
};

User.prototype.addCommentsEditBtn = function(commentsIds) {
	'use strict';
	var commTables = document.getElementsByClassName('comments-table');

	// если есть комментарии
	if(!commTables) return;
	
	// если пришли ID
	if(commentsIds === null) return;
	
	/*if(typeof tinymce === 'undefined') {
		appendScript('scripts/tinymce/tinymce.min.js');							
	}*/

	this._userComments = this.getUserComments(commTables, commentsIds);
	
	
	for(var i=0, len=this._userComments.length; i<len; i++) {
		var commId = this._userComments[i].getElementsByClassName('comment-id')[0];
		var editTr = this.createEditCommentsTr(commId.getElementsByTagName('A')[0].innerHTML);
		this._userComments[i].getElementsByTagName('TBODY')[0].appendChild(editTr);
	}
	
	this.initCommentsEditBtns();
};

User.prototype.checkId = function(id, object) {
	for(var i=0, len=object.length; i<len; i++) {
		if(id === object[i]['comments_id']) {
			return true;
		}
	}
	
	return false;
};
	
User.prototype.getUserComments = function(commTables, commentsIds) {
	var temp = [];
	
	for(var i=0, len=commTables.length; i<len; i++) {
		var contentTr = commTables[i].getElementsByClassName('comments-content')[0];
		var idTd = contentTr.getElementsByClassName('comment-id')[0];
		var id = idTd.getElementsByTagName('A')[0].innerHTML;
		DEBUG('func: getUserComments; output: '+id);
		if(this.checkId(id, commentsIds)) {
			DEBUG('func: getUserComments; output: cut '+i);
			temp.push(commTables[i]);
		}
	}
	
	return temp;
};

// вешаем события на кнопки редактировать/удалить/сохранить
User.prototype.initCommentsEditBtns = function() {
	'use strict';
	var self = this;
	
	for(var i=0, len=this._userComments.length; i<len; i++) {
		var btns = this._userComments[i].getElementsByClassName('user-edit');
		
		for(var j=0, btnsLen=btns.length; j<btnsLen; j++) {
			(function() {
				btns[j].addEventListener('click', self.addHandlerOnCommentsEditBtns.bind(self), false);
			})();
		}
	}
};

// описываем события для кнопок (редактировать/удалить/сохранить)
User.prototype.addHandlerOnCommentsEditBtns = function(e) {
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
			updatedText += "<em>Отредактировано " + new Date().toLocaleString() + '</em>';
			DEBUG('func: addHandlerOnCommentsEditBtns; output: '+id + "|" + updatedText);
			// запрос на сохранение элемента
			this._sendSaveRequest({
				'comment-id': id,
				'comment-text': updatedText
			   },
			   'POST', 
			   'users/user_update_comment.php', 
			   'application/x-www-form-urlencoded');
		}
	}
};

// инициализируем объект tinymce
User.prototype.initEditorForComment = function(elem) {
	'use strict';
	var elemParent, commentsTextTd, commId;
	elemParent = findParent(elem, 'comments-table');
	DEBUG("func: initEditorForComment; elem: " + elem);
	
	if(elemParent === null) return;
	
	commentsTextTd = elemParent.getElementsByClassName('comment-text')[0]; // нашли текст комментария
	
	DEBUG('func: initEditorForComment; output: commentsTextTd: '+commentsTextTd);
	DEBUG('func: initEditorForComment; output: Редактирование: '+elem.getAttribute('data-id'));
	
	commId = elem.getAttribute('data-id');
	commentsTextTd.classList.add('edit-this');
	elem.innerHTML = 'Сохранить';
	initTinyMCE('.edit-this', true, 'auto', 'auto');
};

// убираем предыдущий объект tinymce и меняем назначение кнопок
User.prototype.disablePrevEditors = function() {
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
User.prototype.createEditCommentsTr = function(commId) {
	'use strict';
	var tr = document.createElement('TR');
	tr.classList.add('comments-edit');
	var editTd = document.createElement('TD');
	//var removeTd = document.createElement('TD');
	var infoTd = document.createElement('TD');
	infoTd.setAttribute('colspan', 4);
	infoTd.innerHTML = '<strong>Управление</strong>';
	editTd.innerHTML = '<a href="#" class="user-edit edit-comm " data-id="'+commId+'">Редактировать</a>';
	//removeTd.innerHTML = '<a href="#" class="admin-edit del-comm" data-id="'+commId+'">Удалить</a>';
	tr.appendChild(infoTd);
	tr.appendChild(editTd);
	//tr.appendChild(removeTd);
	
	return tr;
};

User.prototype.getUserCommentsFromId = function(location_id, callback) {
	'use strict';
	var self = this;
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function () {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout); 
			
			if(self._XMLHttpRequest.status != 200) {			
				DEBUG('func: getUserCommentsFromId; output: wha?');
			}
			else {
				var resp = self._XMLHttpRequest.responseText;
				DEBUG('func: getUserCommentsFromId; output: Пришло: '+resp);
				if(resp !== null) {								// в ответ что-то пришло
					if(typeof callback == 'function') {			
						callback.call(self._XMLHttpRequest);	// отдаем это в виде параметра в callback-функцию 
					}											
				}
				else {
					DEBUG("func: getUserCommentsFromId; output: Херово!");
				}	
			}
		}
	};
	
	var timeout = setTimeout(function() {
		self._XMLHttpRequest.abort();
	}, 60*1000);
	this._XMLHttpRequest.open('GET', 'users/user_comments.php?login=' + self.getUserLogin() 
							+ '&comments-location-id=' + encodeURIComponent(location_id), true);
	this._XMLHttpRequest.send();
}

User.prototype._sendSaveRequest = function(argArr, reqType, reqTarget, contentType) {
	'use strict';
	var data = '', j = 1;
	var self = this;
	
	for(var key in argArr) {
		var val = argArr[key];
		data += key + '=' + val;
		(Object.keys(argArr).length > j++) ? data += '&' : DEBUG('func: _sendSaveRequest; Параметр всего 1');	// TODO: тут лажа какая-то
	}
	DEBUG("func: _sendSaveRequest; data: "+data);
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function() {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout);
			if(self._XMLHttpRequest.status != 200) {
				DEBUG('func: _sendSaveRequest; output: Ошибка: ' + self._XMLHttpRequest.responseText);
			}
			else {
				DEBUG('func: _sendSaveRequest; output: Запрос отправлен. Все - хорошо. Ответ сервера: ' + self._XMLHttpRequest.responseText);
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

addEventListenerWithOptions(document, 'DOMContentLoaded', createUserClass, {passive: true});