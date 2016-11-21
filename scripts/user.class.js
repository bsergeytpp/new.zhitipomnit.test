function User() {
	'use strict';
	this._userLogin = false;
	this._XMLHttpRequest = null;
	this._commentsTables = null;
};

// выясняем являемся ли мы пользователем
User.prototype.checkIfUser = function() {
	'use strict';
	var self = this;
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function () {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout);
			var resp = self._XMLHttpRequest.getResponseHeader('UserLogin');

			if(resp !== null) {
				DEBUG("func: checkIfUser; output: Ваш логин: "+resp);
				self._userLogin = resp;
				self.checkForUserComments();
			}
			else {
				DEBUG("func: checkIfUser; output: Вы не авторизованы!");
			}
		}
	};
	var timeout = setTimeout(function() {
		self._XMLHttpRequest.abort();
	}, 60*1000);
	this._XMLHttpRequest.open('HEAD', 'content/json.php', true);
	this._XMLHttpRequest.send();
};

User.prototype.checkForUserComments = function() {
	'use strict';
	var commentsTables = null;
	var self = this;
	
	if(document.getElementsByClassName('comments-table').length > 0) {
		commentsTables = document.getElementsByClassName('comments-table');
		var location = window.location.origin + window.location.pathname + window.location.search;
		
		this.getUserCommentsFromUrl(location, function() {
			var response = this.responseText;
			var responseObject;
		
			if(typeof response === 'string') {
				responseObject = JSON.parse(response);
			}
			
			if(typeof responseObject === 'object') {
				DEBUG('func: checkForUserComments; output: ' + response + ' это объект');				
				self.addCommentsEditBtn(responseObject);
			}
			else DEBUG('func: checkForUserComments; output: ' + response);
		});
		//this.addCommentsEditBtn();
	}
};

User.prototype.addCommentsEditBtn = function(commentsIds) {
	'use strict';
	var commTables = document.getElementsByClassName('comments-table');

	if(!commTables) return;
	if(commentsIds === null) return;
	/*if(typeof tinymce === 'undefined') {
		appendScript('scripts/tinymce/tinymce.min.js');							
	}*/
	var userComments = [];
	userComments = getUserComments(commTables, commentsIds);
		
	for(var i=0, len=userComments.length; i<len; i++) {
		// TODO...
		var commId = userComments[i].getElementsByTagName('A')[0].innerHTML;
		var editTr = this.createEditCommentsTr(commId);
		userComments[i].getElementsByTagName('TBODY')[0].appendChild(editTr);
	}
	
	this.initCommentsEditBtns(userComments);
	
	function checkId(id, object) {
		for(var i=0, len=object.length; i<len; i++) {
			if(id === object[i]['comments_id'])
				return true;
		}
		
		return false;
	}
	function getUserComments(commTables, commentsIds) {
		var temp = [];
		
		for(var i=0, len=commTables.length; i<len; i++) {
			var contentTd = commTables[i].getElementsByClassName('comments-content')[0];
			var id = contentTd.getElementsByTagName('A')[0].innerHTML;			// TODO: надо TD с ID дать класс
			DEBUG('func: getUserComments; output: '+id);
			if(checkId(id, commentsIds)) {
				DEBUG('func: getUserComments; output: cut '+i);
				temp.push(commTables[i]);
			}
		}
		
		return temp;
	}
};

// вешаем события на кнопки редактировать/удалить/сохранить
User.prototype.initCommentsEditBtns = function(userComments) {
	'use strict';
	var self = this;
	
	for(var i=0, len=userComments.length; i<len; i++) {
		var btns = userComments[i].getElementsByClassName('user-edit');
		
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
	var elemParent = findParent(elem, 'comments-table');
	console.log(elem);
	
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

User.prototype.getUserCommentsFromUrl = function(location, callback) {
	'use strict';
	var self = this;
	this._XMLHttpRequest = new XMLHttpRequest();
	this._XMLHttpRequest.onreadystatechange = function () {
		if(self._XMLHttpRequest.readyState == 4) {
			clearTimeout(timeout); 
			
			if(self._XMLHttpRequest.status != 200) {			
				DEBUG('func: getUserCommentsFromUrl; output: wha?');
			}
			else {
				var resp = self._XMLHttpRequest.responseText;
				DEBUG('func: getUserCommentsFromUrl; output: Пришло: '+resp);
				if(resp != null) {								// в ответ что-то пришло
					if(typeof callback  == 'function') {
						callback.call(self._XMLHttpRequest);	// в ответ приходит json строка, 
					}											// которая отдается в виде параметра в функцию callback
				}
				else {
					DEBUG("func: getUserCommentsFromUrl; output: Херово!");
				}	
			}
		}
	};
	
	var timeout = setTimeout(function() {
		self._XMLHttpRequest.abort();
	}, 60*1000);
	this._XMLHttpRequest.open('GET', 'users/user_comments.php?login=' + self._userLogin + '&location=' + encodeURIComponent(location), true);
	this._XMLHttpRequest.send();
}

User.prototype._sendSaveRequest = function(argArr, reqType, reqTarget, contentType) {
	'use strict';
	var data = '', j = 1;
	var self = this;
	
	for(var key in argArr) {
		var val = argArr[key];
		data += key + '=' + val;
		(Object.keys(argArr).length > j++) ? data += '&' : console.log('Параметр всего 1');	// TODO: тут лажа какая-то
	}
	console.log("data: "+data);
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
	user.checkIfUser();
}

addEventListenerWithOptions(document, 'DOMContentLoaded', createUserClass, {passive: true});