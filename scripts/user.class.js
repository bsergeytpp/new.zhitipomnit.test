'use strict';
class User {
	#XMLHttpRequest = null;
	#commentsTables = null;
	#userComments = null;
	#tempText = '';
	#userLogin = false;
	//var self = this;
	
	get getLogin() { return this.#userLogin; };
	
	// выясняем текущий логин
	constructor() {
		var self = this;
		this.#XMLHttpRequest = new XMLHttpRequest();
		this.#XMLHttpRequest.onreadystatechange = function () {
			if(self.#XMLHttpRequest.readyState == 4) {
				clearTimeout(timeout);
				var resp = self.#XMLHttpRequest.getResponseHeader('UserLogin');
				var isAdmin = self.#XMLHttpRequest.getResponseHeader('IsAdmin');
				
				if(isAdmin) {
					return;
				}

				if(resp !== null) {
					DEBUG("checkIfUser", "Ваш логин: "+resp);
					self.#userLogin = resp;
					
					// хак-проверка на админа, чтобы не было лишних TR'ов
					if(!getElems(['comments-edit'])) {
						self.checkForUserComments();
					}
				}
				else {
					DEBUG("checkIfUser", "Вы - не авторизованы!");
					User = user = null;
				}
			}
		};
		var timeout = setTimeout(function() {
			self.#XMLHttpRequest.abort();
		}, 60*1000);
		this.#XMLHttpRequest.open('HEAD', 'content/json.php', true);
		this.#XMLHttpRequest.send();
	}
	
	// ищем комментарии пользователя
	checkForUserComments() {
		var commentsTables = getElems(['comments-table']);
		var self = this;
		
		if(!commentsTables) return;
		
		var locationId = getParamFromLocationSearch('id');
		
		this.#getUserCommentsFromId(locationId, function() {
			var response = this.responseText;
			var responseObject = null;
			DEBUG(self.checkForUserComments.name, response + ' это объект');
		
			if(typeof response === 'string') {
				try {
					responseObject = JSON.parse(response);
				}
				catch(e) {
					DEBUG(self.checkForUserComments.name, 'Пришла не JSON строка: ' + e.toString());
				}
			}
			
			if(responseObject !== null) {
				DEBUG(self.checkForUserComments.name, response + ' это объект');				
				self.#addCommentsEditBtn(responseObject);
			}
			else {
				DEBUG(self.checkForUserComments.name, response);
			}
		});
	}

	// расставляем элементы редактирования комментария
	#addCommentsEditBtn(commentsIds) {
		if(typeof tinymce === 'undefined') {
			appendScript('scripts/tinymce/tinymce.min.js');							
		}
		
		var commTables = getElems(['comments-table']);

		// если есть комментарии
		if(!commTables) return;
		
		// если пришли ID
		if(commentsIds === null) return;

		this.#userComments = this.#getUserComments(commTables, commentsIds);
		
		for(var comment of this.#userComments) {
			if(comment.className.includes('deleted')) continue;
			
			var commId = getElems(['comment-id', 0], comment).textContent;
			var editTr = this.#createEditCommentsTr(commId);
			getElems(['', 0, 'TBODY'], comment).appendChild(editTr);
		}
		
		this.#initCommentsEditBtns();
	}

	// проверяем ID комментария
	#checkId(id, commentObject) {
		for(var comment of commentObject) {
			if(parseInt(id) === comment['comments_id']) {
				return true;
			}
		}
		
		return false;
	}

	// получаем комментарии пользователя
	#getUserComments(commTables, commentsIds) {
		var temp = [];
		
		for(var i=0, len=commTables.length; i<len; i++) {
			// commTables -> .comments-content -> .comment-id
			var commId = getElems(['comment-id', 0], commTables[i]).textContent;	
			DEBUG(this.#getUserComments.name, commId);
			if(this.#checkId(commId, commentsIds)) {
				DEBUG(this.#getUserComments.name, 'cut '+i);
				temp.push(commTables[i]);
			}
		}
		
		return temp;
	}

	// вешаем события на кнопки редактировать/удалить/сохранить
	#initCommentsEditBtns() {
		var self = this;
		
		for(var comment of this.#userComments) {
			var btns = getElems(['user-edit'], comment);
			
			for(var btn of btns) {
				btn.addEventListener('mouseup', this.#addHandlerOnCommentsEditBtns.bind(self), false);
			}
		}
	}

	// описываем события для кнопок (редактировать/удалить/сохранить)
	#addHandlerOnCommentsEditBtns(e) {
		var target = e.target;
		var self = this;
		var targetId = target.getAttribute('data-id');
		
		if(checkClass(target, ['edit-comm'])) {
			e.preventDefault();
			var targetText = target.textContent;
			
			if(targetText === 'Редактировать') {
				this.#userEditComment.call(self, target, targetId);
			}
			else if(targetText === 'Сохранить') {
				e.stopPropagation();
				this.#userSaveComments.call(self, targetId);
			}
		}
	}

	/*
		Вспомогательные функции редактирования/сохранения комментариев
	*/
	#userEditComment(td, tdId) {
		var totalEditors = 1;
		
		if(tinymce.activeEditor.getElement().name === 'comments-text') {	// если есть форма комментирования, то пропускаем ее
			totalEditors = 2;
		} 
		
		if(tinymce.get().length > 1 && 
		   tinymce.activeEditor.getElement().name === 'comments-text') {				// была выбрана форма комментирования
			tinymce.get()[1].focus();
		}
		
		if(tinymce.get().length > totalEditors) {						// уже есть редактируемый комментарий
			var prevEditor = tinyMCE.activeEditor.getBody().parentElement;
			prevEditId = getElems(['comment-id', 0], prevEditor).textContent;
			
			if(confirm('Уже начато редактирование комментария №'+prevEditId+'. Отменить изменения и редактировать комментарий №'+tdId+' ?')) {
				this.#disablePrevEditors();								// убираем предыдущие объект tinymce
				this.#initEditorForComment(td);							// делаем новый объект tinymce
			}
			else return; 												// решили закончить с предыдущим комментарием
		} 
		else {
			if(this.#tempText !== '') {
				this.#tempText = '';
			}
			
			this.#initEditorForComment(td);								// делаем новый объект tinymce
		}
	}

	// сохраняем измененный текст комментария
	#userSaveComments(tdId) {
		var updatedText = tinymce.activeEditor.getContent();
		var activeEditorId = tinymce.activeEditor.getParam('id');		// Unused
		removeActiveTinymceEditors();
		this.#tempText = '';
		// запрос на сохранение элемента
		this.#sendSaveRequest({
			'comment-id': tdId,
			'comment-text': updatedText
		   },
		   'POST', 
		   'users/user_update_comment.php', 
		   'application/x-www-form-urlencoded');
	}

	// инициализируем объект tinymce
	#initEditorForComment(td) {
		var tdParent = findParent(td, 'comments-table');
		DEBUG(this.#initEditorForComment.name, "tdElem: " + td);
		
		if(tdParent === null) return;
		
		var commentsTextTd = getElems(['comment-text', 0], tdParent); // нашли текст комментария
		var commentsText = commentsTextTd.innerHTML;
		var editPos = commentsText.indexOf('<br><em class=');
		
		if(editPos !== -1) {
			this.#tempText = commentsText;
			commentsTextTd.innerHTML = commentsText.substr(0, editPos);
		}
		
		DEBUG(this.#initEditorForComment.name, 'commentsTextTd: '+commentsTextTd);
		DEBUG(this.#initEditorForComment.name, 'Редактирование: '+td.getAttribute('data-id'));
		
		commentsTextTd.firstChild.classList.add('edit-this');
		td.textContent = 'Сохранить';
		initTinyMCE('.edit-this', true, 'auto', 'auto');
	}

	// убираем предыдущий объект tinymce и меняем назначение кнопок
	#disablePrevEditors() {
		var prevTinymceElems = getElems(['edit-this']);
		var saveLinks = getElems(['edit-comm']);
		var activeEditorId = tinymce.activeEditor.getParam('id');
		
		// убираем редактор комментариев
		for(var editor of tinymce.get()) {
			if(editor.getElement().name === 'comments-text') continue;
			
			tinymce.remove('#'+editor.id);
		}
		
		// убираем все внесенные изменения
		for(i=0, len=prevTinymceElems.length; i<len; i++) {
			prevTinymceElems[i].classList.toggle('edit-this', false);
			
			if(this.#tempText === '') continue;
			
			DEBUG(this.#disablePrevEditors.name, 'this.#tempText: '+this.#tempText);
			DEBUG(this.#disablePrevEditors.name, 'prevTinymceElems[i]: '+prevTinymceElems[i]);
			prevTinymceElems[i].innerHTML = this.#tempText;
			this.#tempText = '';
		}
		
		// меняем текст кнопки
		for(var btn of saveLinks) {
			if(btn.textContent !== 'Сохранить') continue;
			
			btn.textContent = 'Редактировать';
		}
	}

	// создаем TR с кнопками редактировать/удалить 
	#createEditCommentsTr(commId) {
		var tr = createDOMElem({tagName: 'TR', className: 'comments-edit'});
		var editTd = createDOMElem({tagName: 'TD'});
		
		var editLink = createDOMElem({
			tagName: 'A',
			args: [{name: 'href', value: '#'}, {name: 'data-id', value: commId}], 
			className: 'user-edit edit-comm ',
			innerText: 'Редактировать'
		});
		
		editTd.appendChild(editLink);
		editTd.setAttribute('colspan', 3);
		tr.appendChild(editTd);
		
		return tr;
	}

	// ищем комментарии пользователя по его ID
	#getUserCommentsFromId(locationId, callback) {
		var self = this;
		this.#XMLHttpRequest = new XMLHttpRequest();
		this.#XMLHttpRequest.onreadystatechange = function () {
			if(this.readyState == 4) {
				clearTimeout(timeout); 
				
				if(this.status != 200) {			
					DEBUG(self.#getUserCommentsFromId.name, 'wha?');
				}
				else {
					var resp = this.responseText;
					DEBUG(self.#getUserCommentsFromId.name, 'Пришло: '+resp);
					if(resp !== null &&	typeof callback == 'function') {		// в ответ что-то пришло
						callback.call(this);									// отдаем это в виде параметра в callback-функцию 										
					}
					else {
						DEBUG(self.#getUserCommentsFromId.name, "Херово!");
					}	
				}
			}
		};
		
		var timeout = setTimeout(function() {
			self.#XMLHttpRequest.abort();
		}, 60*1000);
		this.#XMLHttpRequest.open('GET', 'users/user_comments.php?login=' + this.getLogin 
								+ '&comments-location-id=' + encodeURIComponent(locationId), true);
		this.#XMLHttpRequest.send();
	}

	// отправляем запрос на сохранение
	#sendSaveRequest(argArr, reqType, reqTarget, contentType) {
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
				DEBUG(this.#sendSaveRequest.name, 'Параметр всего 1');
			}
		}
		DEBUG(this.#sendSaveRequest.name, "data: "+data);
		this.#XMLHttpRequest = new XMLHttpRequest();
		this.#XMLHttpRequest.onreadystatechange = function() {
			if(this.readyState == 4) {
				clearTimeout(timeout);
				if(this.status != 200) {
					DEBUG(self.#sendSaveRequest.name, 'Ошибка: ' + this.responseText);
				}
				else {
					DEBUG(self.#sendSaveRequest.name, 'Запрос отправлен. Ответ сервера: ' + this.responseText);
					updateCommentsWrapper();
				}
			}
		};
		var timeout = setTimeout(function() {
			self.#XMLHttpRequest.abort();
		}, 60*1000);
		this.#XMLHttpRequest.open(reqType, reqTarget, true);
		this.#XMLHttpRequest.setRequestHeader("Content-Type", contentType);
		this.#XMLHttpRequest.send(data);
	}
}

// создаем объект класса User
var user;
function createUserClass() {
	user = new User();
}

//addEventListenerWithOptions(document, 'DOMContentLoaded', createUserClass, {passive: true});