'use strict';
class Admin {
	#isAdmin = false;
	#login = null;
	
	#XMLHttpRequest = null;
	#editBtns = [];
	#responseObject = '';
	#editDiv = null;
	#commentsTables = null;
	#guestbookForms = null;
	#tempText = '';
	
	get checkAdmin() { return this.isAdmin; };
	get Login() { return this.login; };
	
	// выясняем являемся ли мы админом
	constructor () {
		var self = this;
		this.#XMLHttpRequest = new XMLHttpRequest();
		this.#XMLHttpRequest.onreadystatechange = function () {
			if(this.readyState == 4) {
				clearTimeout(timeout);
				var resp = this.getResponseHeader('IsAdmin');
				var respLogin = this.getResponseHeader('UserLogin');
				DEBUG("checkIfAdmin", "RESP: "+resp);
				
				if(respLogin !== null) {
					if(resp !== null) {
						DEBUG("checkIfAdmin", "Вы - Админ. Поздравляю!");
						self.isAdmin = true;
						self.login = respLogin;
						User = user = null;
						self.setPrivilege();
					}
					else {
						DEBUG("checkIfAdmin", "Вы - не Админ.");
						self.destructor();
						createUserClass();
					}
				}
				else {
					DEBUG("checkIfAdmin", "Вы - не авторизованы.");
					//Admin = admin = null;
					self.destructor();
					User = user = null;
				} 
			}
		};
		var timeout = setTimeout(function() {
			anotherThis.#XMLHttpRequest.abort();
		}, 60*1000);
		this.#XMLHttpRequest.open('HEAD', 'content/json.php', true);
		this.#XMLHttpRequest.send();
	}
	
	destructor() {
		admin = null;
	}
	
	// проверяем есть ли на странице редактируемые элементы
	#checkForEditableContent() {
		var elems = getElems(['editable']);
		
		if(!elems) return;
		
		// есть хотя бы один элемент
		if(elems.length > 0) {
			this.#addEditBtn(elems);
			this.#initAdminEdit();
		}
	}
	
	// проверяем есть ли на странице сообщения гостевой книги
	#checkForGuestbook() {
		var guestbookForms = getElems(['guestbook-message']);
		
		if(!guestbookForms) return;
		
		this.#guestbookForms = guestbookForms;
		this.#addGuestbookEditBtn();
	}

	// добавляем кнопку редактирования сообщений гостевой книги
	#addGuestbookEditBtn() {
		// если сообщения есть
		if(this.#guestbookForms === null) return;
		
		// если кнопок пока нет
		if(getElems(['gb-edit-button'])) return;
			
		for(var formElem of this.#guestbookForms) {
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
		
		this.#initGuestbookEditBtns();
	}

	// вешаем события на кнопки редактирования гостевой книги
	#initGuestbookEditBtns() {
		var self = this;
		
		for(var formElem of this.#guestbookForms) {
			var btns = getElems(['gb-edit-button'], formElem);
			
			for(var btn of btns) {
				btn.addEventListener('click', self.addHandlerOnGuestbookEditBtns.bind(self), false);
			}
		}
	}

	// описываем события для кнопок редактирования гостевой книги
	#addHandlerOnGuestbookEditBtns(e) {
		var target = e.target;
		var self = this;
		var targetId = target.getAttribute('data-id');
		var targetText = target.textContent;
		
		if(checkClass(target, ['gb-edit-button'])) {
			e.preventDefault();
			e.stopPropagation();
			
			switch(targetText) {
				case 'Редактировать': this.#editGuestbookMessage.call(self, target, targetId); break;
				case 'Сохранить': this.#saveGuestbookMessage.call(self, targetId); break;
				case 'Удалить': this.#deleteGuestbookMessage.call(self, targetId); break;
				default: break;
			}
		}
	}
	
	#editGuestbookMessage(btn, gbId) {
		// если есть форма сообщения гостевой, то пропускаем ее
		var totalEditors = tinymce.get().length;
		var gbEditors = (tinymce.activeEditor.getElement().name === 'guestbook-text') ? totalEditors - 1 : totalEditors;
		
		if(totalEditors > 1) {
			if(tinymce.get()[1].getElement().name === 'edit-textarea') {
				alert("Закончите текущее редактирование!");
				return;
			}
			
			if(tinymce.activeEditor.getElement().name === 'guestbook-text') {
				tinymce.get()[1].focus();
			}
		}
		
		if(gbEditors > 1) {
			var currentEditId = tinymce.activeEditor.targetElm.parentElement.id;

			if(confirm('Уже начато редактирование сообщения №'+currentEditId+
					   '. Отменить изменения и редактировать сообщение №'+gbId+' ?')) {
				this.#disablePrevEditors();
				this.#initEditorForGuestbook(btn);
			}
			else return; 			
		} 
		else {		
			this.#initEditorForGuestbook(btn);
		}
	}

	#saveGuestbookMessage(gbId) {
		// выбрана форма добавления сообщения
		if(tinymce.get().length > 1 && tinymce.activeEditor.getElement().name === 'guestbook-text') {
			tinymce.get()[1].focus();
		}
		
		var updatedText = tinymce.activeEditor.getContent({ format: 'text' });
		removeActiveTinymceEditors();

		DEBUG(this.#saveGuestbookMessage.name, gbId + "|" + updatedText);
		// запрос на сохранение элемента
		this.#sendSaveRequest({
			'gb-id': gbId,
			'gb-text': updatedText,
		   },
		   'POST', 
		   'admin/admin_guestbook/update_guestbook.php', 
		   'application/x-www-form-urlencoded');
		updateGuestbookDiv();
	}

	#deleteGuestbookMessage(gbId) {
		if(confirm('Точно удалить сообщение №'+gbId+'?')) { 
			DEBUG(this.#deleteGuestbookMessage.name, 'Удаление: '+gbId);			
			removeActiveTinymceEditors();
			this.#sendSaveRequest({
				'gb-id': gbId
			   },
			   'POST', 
			   'admin/admin_guestbook/delete_guestbook.php', 
			   'application/x-www-form-urlencoded');
			updateGuestbookDiv();
		}
	}

	// проверяем есть ли на странице редактируемые комментарии
	#checkForComments() {
		var commentsTables = getElems(['comments-table']);
		
		if(!commentsTables) return;
			
		this.#commentsTables = commentsTables;
		this.#addCommentsEditBtn();
		
	}

	// добавляем еще один TR к каждому комментарию
	#addCommentsEditBtn() {
		if(typeof tinymce === 'undefined') {
			appendScript('scripts/tinymce/tinymce.min.js');							
		}
		
		// если комментарии есть
		if(this.#commentsTables === null) return;
		
		// если кнопок пока нет
		if(getElems(['comments-edit'])) return;
			
		for(var tableElem of this.#commentsTables) {
			if(tableElem.className.includes('deleted')) continue;

			var commId = getElems(['comment-id', 0], tableElem);
			var editTr = this.#createEditCommentsTr(commId.textContent);
			getElems(['', 0, 'TBODY'], tableElem).appendChild(editTr);
		}
		
		this.#initCommentsEditBtns();
	}

	// вешаем события на кнопки редактировать/удалить/сохранить
	#initCommentsEditBtns() {
		var self = this;
		
		for(var tableElem of this.#commentsTables) {
			var btns = getElems(['admin-edit'], tableElem);
			
			if(!(btns instanceof HTMLCollection)) continue;	// удаленные комментарии пропускаем
			
			for(var btn of btns) {
				btn.addEventListener('click', self.#addHandlerOnCommentsEditBtns.bind(self), false);
			}
		}
	}

	// описываем события для кнопок (редактировать/удалить/сохранить)
	#addHandlerOnCommentsEditBtns(e) {
		var target = e.target;
		var self = this;
		var targetId = target.getAttribute('data-id');
		var targetText = target.textContent;
		
		if(checkClass(target, ['edit-comm'])) {
			e.preventDefault();
			e.stopPropagation();
			
			if(targetText === 'Редактировать') {
				this.#editComments.call(self, target, targetId);
			}
			else if(targetText === 'Сохранить') {
				this.#saveComments.call(self, targetId);
			}
		}
		else if(checkClass(target, ['del-comm'])) {
			e.preventDefault();
			e.stopPropagation();
			this.#deleteComments.call(self, targetId);
		}
	}

	/*
		Вспомогательные функции редактирования/удаления/сохранения комментариев
	*/
	#editComments(td, tdId) {	
		// если есть форма комментирования, то пропускаем ее
		var totalEditors = tinymce.get().length;
			
		if(tinymce.activeEditor) {
			if(tinymce.activeEditor.getElement().name === 'comments-text') {
				totalEditors = 1;
			}
			else if(tinymce.get()[0].getElement().name === 'comments-text') {
				totalEditors -= 1;
			}
		}
		
		if(tinymce.get().length > totalEditors) {						// уже есть редактируемый комментарий
			if(tinymce.get()[1].getElement().name === 'edit-textarea') {
				alert("Закончите текущее редактирование!");
				return;
			}
			
			if(tinymce.activeEditor.getElement().name === 'comments-text') {			// была выбрана форма комментирования
				tinymce.get()[1].focus();
			}
			
			var prevEditor = tinymce.activeEditor.getBody().parentElement;
			var prevEditId = getElems(['comment-id', 0], prevEditor).textContent;

			if(confirm('Уже начато редактирование комментария №'+prevEditId+
					   '. Отменить изменения и редактировать комментарий №'+tdId+' ?')) {
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

	// удаляем комментарии
	#deleteComments(tdId) {
		if(confirm('Точно удалить комментарий №'+tdId+'?')) { 
			DEBUG(this.#deleteComments.name, 'Удаление: '+tdId);			
			// запрос на удаление элемента
			removeActiveTinymceEditors();
			this.#tempText = '';
			this.#sendSaveRequest({
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
	#saveComments(tdId) {
		// была выбрана форма комментирования
		if(tinymce.get().length > 1 && tinymce.activeEditor.getElement().name === 'comments-text') {
			tinymce.get()[1].focus();
		}
		
		var updatedText = tinymce.activeEditor.getContent();
		var activeEditorId = tinymce.activeEditor.getParam('id');	// Unused
		var editPos = updatedText.indexOf('<br><em class=');
		removeActiveTinymceEditors();
		this.#tempText = '';
		
		if(editPos !== -1) {
			updatedText = updatedText.substr(0, editPos);
		}
		
		DEBUG(this.#saveComments.name, tdId + "|" + updatedText);
		// запрос на сохранение элемента
		this.#sendSaveRequest({
			'comment-id': tdId,
			'comment-text': updatedText,
			'comment-author': this.Login
		   },
		   'POST', 
		   'admin/admin_comments/update_comment.php', 
		   'application/x-www-form-urlencoded');
		updateCommentsWrapper();
	}

	// инициализируем объекты tinymce
	#initEditorForComment(td) {
		var tdParent = findParent(td, 'comments-table');
		
		if(tdParent === null) return;
		
		var commentsTextTd = getElems(['comment-text', 0], tdParent); // нашли текст комментария
		var commentsText = DOMPurify.sanitize(commentsTextTd.innerHTML);
		var editPos = commentsText.indexOf('<br><em class=');
		
		if(editPos !== -1) {
			this.#tempText = commentsText;
			commentsTextTd.innerHTML = DOMPurify.sanitize(commentsText.substr(0, editPos));
		}
		
		DEBUG(this.#initEditorForComment.name, 'commentsTextTd: '+commentsTextTd);
		DEBUG(this.#initEditorForComment.name, 'Редактирование: '+td.getAttribute('data-id'));

		commentsTextTd.firstChild.classList.add('edit-this');
		td.textContent = 'Сохранить';
		initTinyMCE('.edit-this', true, 'auto', 'auto');
	}

	// инициализируем объект tinymce
	#initEditorForGuestbook(btn) {
		var gbForm = findParent(btn, 'guestbook-message');
		
		if(gbForm === null) return;
		
		var gbTextarea = getElems(['', 0, 'TEXTAREA'], gbForm);
		gbTextarea.removeAttribute('disabled');
		
		DEBUG(this.#initEditorForGuestbook.name, 'gbTextarea: '+gbTextarea);
		DEBUG(this.#initEditorForGuestbook.name, 'Редактирование: '+btn.getAttribute('data-id'));

		gbTextarea.classList.add('edit-this');
		btn.textContent = 'Сохранить';
		initTinyMCE('.edit-this', false, 'auto', 'auto');
		// TODO: добавить возможность отключения панелей в initTinyMCE
		//getElems(['mce-menubar', 0]).style.display = 'none';
		//getElems(['mce-toolbar-grp', 0]).style.display = 'none';;
	}

	// убираем предыдущий объект tinymce и меняем назначение кнопок
	#disablePrevEditors() {
		var prevTinymceElems = getElems(['edit-this']);
		var saveLinks = getElems(['edit-comm']) || getElems(['gb-edit-button']);
		var activeEditorId = tinymce.activeEditor.getParam('id');	// Unused
		
		if(!prevTinymceElems || !saveLinks) return;

		for(var tinymceEditor of tinymce.get()) {
			if(tinymceEditor.getElement().name === 'comments-text' ||
			   tinymceEditor.getElement().name === 'guestbook-text') continue;
			
			tinymce.remove('#'+tinymceEditor.id);
			console.log('11111');
		}
		
		for(var prevTinymceElem of prevTinymceElems) {
			if(this.#tempText !== '') {
				DEBUG(this.#disablePrevEditors.name, 'this.#tempText: '+this.#tempText);
				DEBUG(this.#disablePrevEditors.name, 'prevTinymceElem: '+prevTinymceElem);
				prevTinymceElem.innerHTML = DOMPurify.sanitize(this.#tempText);
				this.#tempText = '';
			}
			
			prevTinymceElem.classList.toggle('edit-this', false);
			console.log('22222');
		}
		
		for(var linkElem of saveLinks) {
			if(linkElem.textContent !== 'Сохранить') continue;
			
			linkElem.textContent = 'Редактировать';
		}
	}

	// создаем TR с кнопками редактировать/удалить 
	#createEditCommentsTr(commId) {
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
	}

	// функция для добавления кнопки редактирования (новости/статьи)
	#addEditBtn(elems) {
		if(typeof tinymce === 'undefined') {
			appendScript('scripts/tinymce/tinymce.min.js');
		}
		
		var divElem = createDOMElem({tagName: 'DIV', className: 'admin-edit-button'});
		
		// создаем для каждого редактируемого элемента кнопку
		for(var i=0, len=elems.length, firstChild; i<len; i++) {
			this.#editBtns[i] = divElem.cloneNode();
			firstChild = elems[i].children[0];
			elems[i].insertBefore(this.#editBtns[i], firstChild);
		}
	}

	// вешаем на каждую кнопку событие на нажатие
	#initAdminEdit() {
		var self = this;
		
		for(var editBtn of this.#editBtns) {
			editBtn.addEventListener('mouseup', function(e) {
				var id = e.target.parentNode.getAttribute('id');
				var className = e.target.parentNode.className;
				var pattern = (className.indexOf('news') > -1) ? 'news' : 'publs';
				
				// ищем элементы для редактирования по их ID
				self.#getElemByDBId(pattern, id, function() {
					var response = this.responseText;
					
					// вернулась строка
					if(typeof response === 'string') {
						try{
							self.#responseObject = JSON.parse(response);
						}
						catch(e) {
							DEBUG(addHandlerOnEditBtns.name, 'Пришла не JSON строка: ' + e.toString());
						}
					}
					
					// строка оказалась формата JSON
					if(typeof self.#responseObject === 'object') {
						var editElem = getElems(['admin-edit-elem', 0]);
						
						if(editElem) {
							self.#editDiv = null;
							document.body.removeChild(editElem);
						}

						// создаем все необходимые элементы
						self.#createEditDiv(pattern, self.#createEditDivCallback.bind(self, pattern, id));
					}
					else DEBUG(addHandlerOnEditBtns.name, response);
				});
			}, false);
		}
	}

	// функция делает AJAX запрос на выборку новости/статьи по ID
	#getElemByDBId(pattern, id, callback) {
		var self = this;
		this.#XMLHttpRequest = new XMLHttpRequest();
		this.#XMLHttpRequest.onreadystatechange = function () {
			if(this.readyState == 4) {
				clearTimeout(timeout); 
				
				if(this.status != 200) {							// сервер сказал "НЕТ"
					DEBUG(self.#getElemByDBId.name, 'wha?');
				}
				else {
					if(this.responseText != null) {					// в ответ что-то пришло
						if(typeof callback  == 'function') {
							callback.call(this);					// отдаем это в виде параметра в callback-функцию 
						}
					}
					else {
						DEBUG(self.#getElemByDBId.name, "Херово!");
					}	
				}
			}
		};
		
		var timeout = setTimeout(function() {
			self.#XMLHttpRequest.abort();
		}, 60*1000);
		this.#XMLHttpRequest.open('GET', 'admin/admin_'+pattern+'/get_'+pattern+'_by_id.php?id=' + id, true);
		this.#XMLHttpRequest.send();
	}

	// функция создает DIV элемент для редактирования новости или статьи
	#createEditDiv(pattern, callback) {
		var self = this;
		this.#XMLHttpRequest = new XMLHttpRequest();
		this.#XMLHttpRequest.onreadystatechange = function () {
			if(this.readyState == 4) {
				clearTimeout(timeout); 
				
				if(this.status != 200) {			// сервер сказал "НЕТ"
					DEBUG(self.#getElemByDBId.name, 'wha?');
				}
				else {
					var resp = this.responseText;
					if(resp != null) {								// в ответ что-то пришло
						resp = resp.replace('$id$', self.#responseObject[pattern+'_id']);
						resp = resp.replace('$header$', self.#responseObject[pattern+'_header']);
						resp = resp.replace('$inner$', self.#responseObject[pattern+'_text']);
						var div = createDOMElem({tagName: 'DIV', className: 'admin-edit-elem'});
						div.innerHTML = DOMPurify.sanitize(resp);
						document.body.appendChild(div);
						self.#editDiv = div;
						
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
			self.#XMLHttpRequest.abort();
		}, 60*1000);
		this.#XMLHttpRequest.open('GET', 'content/templates/editform_template.php', true);
		this.#XMLHttpRequest.send();
	}

	// создаем элемент для редактирования
	#createEditDivCallback(pattern, id) {
		var self = this;
		// удаляем другие объекты tinymce
		if(tinymce.get().length > 0) {
			this.#disablePrevEditors();
		}
		
		// делаем из textarea объект tinymce
		initTinyMCE('.admin-edit-elem textarea', false);
		
		// вешаем на кнопки события
		this.#editDiv.addEventListener('mouseup', function(e) {
			var target = e.target;
			var targetText = target.textContent;
			e.preventDefault();
			
			if(targetText === 'Отменить') {
				e.stopPropagation();
				
				if(tinymce.activeEditor.getElement().name === 'comments-text') {
					if(tinymce.get().length > 1 && tinymce.get()[1].getElement().name !== 'comments-text') {
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
				self.#sendSaveRequest({
					[pattern+'-id']: id,
					[pattern+'-text']: updatedText
				   },
				   'POST', 
				   'admin/admin_'+pattern+'/update_'+pattern+'.php', 
				   'application/x-www-form-urlencoded');
				
				if(tinymce.activeEditor.getElement().name === 'comments-text') {
					if(tinymce.get().length > 1 && tinymce.get()[1].getElement().name !== 'comments-text') {
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
	#sendSaveRequest(argArr, reqType, reqTarget, contentType) {
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
				DEBUG(this.#sendSaveRequest.name, 'Параметр всего 1');
			}
		}
		DEBUG(this.#sendSaveRequest.name, "data: " + data);
		this.#XMLHttpRequest = new XMLHttpRequest();
		this.#XMLHttpRequest.onreadystatechange = function() {
			if(this.readyState == 4) {
				clearTimeout(timeout);
				if(this.status != 200) {
					DEBUG(self.#sendSaveRequest.name, 'Ошибка: ' + this.responseText);
				}
				else {
					DEBUG(self.#sendSaveRequest.name, 'Запрос отправлен. Ответ сервера: ' + this.responseText);
					setTimeout(function() {
						//location.reload();
					}, 3*1000);
					//updateCommentsWrapper();
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

	// создаем панель администратора
	// TODO: проверять на наличии ссылки перед добавлением (AJAX)
	#addAdminPanelLink() {
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
	}
	
	// для админа ставим кнопки редактирования
	setPrivilege() {
		DEBUG(this.setPrivilege.name, 'admin: ' + this.checkAdmin);
		
		if(this.checkAdmin) {
			this.#checkForEditableContent();			// расставляем кнопки редактирования
			this.#checkForComments();
			this.#checkForGuestbook();
			this.#addAdminPanelLink();				// добавляем ссылку на панель администратора
		}
		else {
			DEBUG(this.setPrivilege.name, 'Вы не админ. Хватит хулиганить!');
		}
	}
}

// создаем объект класса Admin
var admin = null;
function createAdminClass() {
	admin = new Admin();					
}

addEventListenerWithOptions(document, 'DOMContentLoaded', createAdminClass, {passive: true});