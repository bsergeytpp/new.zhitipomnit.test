/*Passive event listeners for Blink*/
var supportsPassive = false;

try {
	var opts = Object.defineProperty({}, 'passive', {
		get: function() {
			supportsPassive = true;
		}
	});
	window.addEventListener("test", null, opts);
} catch (e) {}

function addEventListenerWithOptions(target, type, handler, options) {
	var optionsOrCapture = options;
	
	if (!supportsPassive) {
		optionsOrCapture = options.capture;
	}
	target.addEventListener(type, handler, optionsOrCapture);
}

addEventListenerWithOptions(document, "touchstart", function(e) {}, {passive: true} );
addEventListenerWithOptions(document, "touchmove", function(e) {}, {passive: true} );
addEventListenerWithOptions(document, "touchend", function(e) {}, {passive: true} );

addEventListenerWithOptions(document, "wheel", function(e) {
	//var respTime = performance.now() - e.timeStamp;
	//console.log(respTime);
	
}, {passive: true} );

/***********************/

function Admin() {
	this._isAdmin = false;
	this._XMLHttpRequest = null;
	this._editBtns = [];
	this._responseObject;
	this._editDiv = null;
};

Admin.prototype.getIsAdmin = function() {
	return this._isAdmin;
};

Admin.prototype.checkIfAdmin = function() {
	var self = this;
	var request = new XMLHttpRequest();
	request.onreadystatechange = function () {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			var resp = request.getResponseHeader('IsAdmin');
			
			if(resp !== null) {
				console.log("Вы - Админ. Поздравляю!");
				self._isAdmin = true;
				appendScript('scripts/tinymce/tinymce.min.js');
				self.addEditBtn();
			}
			else {
				console.log("Вы - не Админ. Херово!");
			}
		}
	};
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	request.open('HEAD', 'content/json.php', true);
	request.send();
};

Admin.prototype.addEditBtn = function() {
	var pattern = '';
	if(document.getElementsByClassName('article-news').length > 0 ||
	   document.getElementsByClassName('news-full-container').length > 0) {
		pattern = 'news';
	}
	else if(document.getElementsByClassName('article-publs').length > 0 ||
	        document.getElementsByClassName('publs-full-container').length > 0) {
		pattern = 'publs';
	}
	else return;
	
	var elem = document.getElementsByClassName('article-'+pattern);
	
	// если элемент открыт
	if(elem.length === 0) {
		elem = document.getElementsByClassName(pattern+'-full-container');
	}

	for(var i=0, len=elem.length; i<len; i++) {
		this._editBtns[i] = document.createElement('div');
		this._editBtns[i].className = 'admin-edit-button';
		var firstChild = elem[i].children[0];
		elem[i].insertBefore(this._editBtns[i], firstChild);
	}

	this.initAdminEdit();
};

Admin.prototype.initAdminEdit = function() {
	var self = this;
	for(var i=0, len=this._editBtns.length; i<len; i++) {
		(function() {
			self._editBtns[i].addEventListener('click', self.addHandlerOnEditBtns.bind(self), false);
		})();
	}
};

Admin.prototype.addHandlerOnEditBtns = function(e) {
	var id = e.target.parentNode.getAttribute('id');
	var className = e.target.parentNode.className;
	var self = this;
	
	this._getElemByDBId(className, id, function() {
		var response = this.responseText;
		
		if(typeof response === 'string') {
			self._responseObject = JSON.parse(response);
		}
		
		if(typeof self._responseObject === 'object') {
			if(document.getElementsByClassName('admin-edit-elem')[0] !== undefined) {
				self._editDiv = null;
				document.body.removeChild(document.getElementsByClassName('admin-edit-elem')[0]);
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
					var reqTarget = (this.className === 'article-news') ? 'news' : 'publs';
					this._sendSaveRequest({
						'id': id,
						'text': updatedText
					   },
					   'POST', 
					   'admin/update_'+reqTarget+'.php', 
					   'application/x-www-form-urlencoded');
					document.body.removeChild(this);	// удаляем div редактирования
				} 
				
			}, false);
		}
		else console.log(response);
	});
};

Admin.prototype._createEditDiv = function(className) {
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
	
	if(className === 'article-news') {
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

Admin.prototype._sendSaveRequest = function(argArr, reqType, reqTarget, contentType) {
	var data = '', j = 1;
	var self = this;
	
	for(var key in argArr) {
		var val = argArr[key];
		data += key + '=' + val;
		(j++ < Object.keys(argArr).length) ? data += '&' : console.log('I dunno');
	}
	
	var request = new XMLHttpRequest();
	request.onreadystatechange = function() {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			if(request.status != 200) {
				console.log('Ошибка: ' + request.responseText);
			}
			else console.log('Запрос отправлен. Все - хорошо.');
		}
	};
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	request.open(reqType, reqTarget, true);
	request.setRequestHeader("Content-Type", contentType);
	request.send(data);
}

Admin.prototype._getElemByDBId = function(className, id, callback) {
	var request = new XMLHttpRequest();
	var pattern = (className === 'article-news') ? 'news' : 'publs' ;
	var self = this;
	
	request.onreadystatechange = function () {
		if(request.readyState == 4) {
			clearTimeout(timeout); 
			
			if(request.status != 200) {
				console.log('wha?');
			}
			else {
				var resp = request.responseText;
				if(resp != null) {
					if(typeof callback  == 'function') {
						callback.call(request);
					}
				}
				else {
					console.log("Херово!");
				}	
			}
		}
	};
	
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	request.open('GET', 'admin/get_'+pattern+'_by_id.php?id=' + id, true);
	request.send();
};

addEventListenerWithOptions(document, 'scroll', function(e) {
    var article = document.getElementsByClassName('article')[0];
    var scrollBtn = document.getElementsByClassName('scroll-button')[0];
    var header = document.getElementsByClassName('header')[0];
        if((window.pageYOffset || document.documentElement.scrollTop) > 550) {
			if(article.style.width !== '100%') {
			   //article.style.marginLeft = 5 + 'px';    
			   //article.style.width = 100 + '%';
			   scrollBtn.classList.add("scroll-button-active");
			}
        }
        else {
			//article.style.marginLeft = "";
			//article.style.width = "";
			scrollBtn.classList.remove("scroll-button-active");
        }
}, {passive: true});

addEventListenerWithOptions(document, 'DOMContentLoaded', function() {
	var ul = document.getElementsByClassName('news-list');
	
	if(!ul) return;
	
	for(var i=0; i<ul.length; i++) {
		ul[i].addEventListener('click', navigateUlList, false);
	}
}, {passive: true});

addEventListenerWithOptions(document, 'DOMContentLoaded', function(e) { 
	var admin = new Admin();
	admin.checkIfAdmin();
	//admin.checkIfAdmin(appendScript.bind(null, 'scripts/tinymce/tinymce.min.js'));
	//admin.checkIfAdmin(admin.addEditBtn.bind(admin,'news'));
	//admin.checkIfAdmin(admin.addEditBtn.bind(admin,'publs'));
}, {passive: true});

function initTinyMCE(className, isInline) {
	tinymce.init({
		inline: isInline,
		selector: className,
		language: 'ru_RU',
		plugins: 'code',
		paste_data_images: true,
		width: 400,
		height: 170
	});
}

function navigateUlList(e) {
	var target = e.target;
	
	if(target === e.currentTarget) {
		e.stopPropagation();
		return;
	}
	
	var urlArr = decodeURIComponent(location.search.substr(1)).split('&');
	var pair, urlParams = new Object;
	
	for(var i=0, len=urlArr.length; i<len; i++) {
		pair = urlArr[i].split("=");
		urlParams[pair[0]] = pair[1];
	}
	
	var pageNum = getUrlParam('page', urlParams);
	
	if(!pageNum) pageNum = 1;
				
	if(target == this.firstChild) {
		if(target.innerHTML.indexOf("«")) {
			if(pageNum != 1) {
				urlParams['page'] = --pageNum;
				urlArr = [];
				
				for(var elem in urlParams) {
					urlArr.push(elem + "=" + urlParams[elem]); 
				}
				location.search = urlArr.join('&');
				
			}
		}
	}
	else if(target == this.lastChild) {
		if(target.innerHTML.indexOf("«")) {
			if(pageNum != this.children.length-2) {
				urlParams['page'] = ++pageNum;
				urlArr = [];
				
				for(var elem in urlParams) {
					urlArr.push(elem + "=" + urlParams[elem]); 
				}
				location.search = urlArr.join('&');
			}
		}
	}		
}

function getUrlParam(value, obj) {
	for(var param in obj) {
		if(param == value) {
			return obj[param];
		}
	}
	
	return false;
}

function replaceNewsLinks() {
	var container = document.body.getElementsByClassName('article')[0];
	var parents = container.getElementsByTagName('P');
	
	for(var i=0, len=parents.length; i<len; i++) {
		var link = parents[i].getElementsByTagName('A')[0];
		link.setAttribute('href', 'index.php?pages=news&custom-news-date=' + link.getAttribute('href').substring(0, link.getAttribute('href').length - 5));
		//console.log(link.getAttribute('href'));
	}
}

function replacePressLinks() {
	var press = document.body.getElementsByClassName('article-press');
	
	for(var i=0, len=press.length; i<len; i++) {
		var str = press[i].getElementsByTagName('A')[0];
		str.setAttribute('href', 'index.php?pages=press&custom-press=' + str.getAttribute('href').substring(0, 5));
		//console.log(press[i].getAttribute('href'));
	}
}

function changeStyle() {
	document.getElementById('news-container').style.display = 'block';
}

function displayNewsImage() {
	var imgs = document.body.getElementsByClassName('article-news-image');
	
	for(var i=0, len=imgs.length; i<len; i++) {
		if(isFileExists(imgs[i].getAttribute('src'))) {
			imgs[i].style.display = 'block';
		}
		else imgs[i].style.display = 'none';
	}
}

function isFileExists(url) {
	var http = new XMLHttpRequest();
	http.open('HEAD', url, true);
	http.send();
	return http.status != 404;
}

function checkIfAdmin(callback) {
	var request = new XMLHttpRequest();
	request.onreadystatechange = function () {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			var resp = request.getResponseHeader('IsAdmin');
			
			if(resp !== null) {
				console.log("Вы - Админ. Поздравляю!");
				if(typeof callback == 'function') {
					callback.call(request);
				}
			}
			else {
				console.log("Вы - не Админ. Херово!");
			}
		}
	};
	var timeout = setTimeout(function() {
		request.abort();
	}, 60*1000);
	request.open('HEAD', 'content/json.php', true);
	request.send();
}

function appendScript(src) {
	var script = document.createElement('script');
	script.src = src;
	document.body.appendChild(script);
}