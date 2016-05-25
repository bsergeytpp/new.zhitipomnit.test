document.onscroll = function() {
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
};

document.addEventListener('DOMContentLoaded', function() {
	var ul = document.getElementsByClassName('news-list');
	
	if(!ul) return;
	
	for(var i=0; i<ul.length; i++) {
		ul[i].addEventListener('click', navigateUlList, false);
	}
}, false);

document.addEventListener('DOMContentLoaded', isAdmin(addEditBtn('news')), false);
document.addEventListener('DOMContentLoaded', isAdmin(addEditBtn('publs')), false);

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

function addEditBtn(pattern) {
	var elem = document.getElementsByClassName('article-'+pattern);
	
	// если элемент открыт
	if(elem.length === 0) {
		elem = document.getElementsByClassName(pattern+'-full-container');
	}
	
	for(var i=0, len=elem.length; i<len; i++) {
		var div = document.createElement('div');
		div.className = 'admin-edit-button';
		var firstChild = elem[i].children[0];
		elem[i].insertBefore(div, firstChild);
	}
	
	initAdminEdit();
}

function initAdminEdit() {
	var editBtns = document.getElementsByClassName('admin-edit-button');
	
	for(var i=0, len=editBtns.length; i<len; i++) {
		(function() {
			editBtns[i].addEventListener('click', addHandlerOnEditBtns, false);
		})();
	}
}

function addHandlerOnEditBtns(e) {
	var id = this.parentNode.getAttribute('id');
	var className = this.parentNode.className;
	getElemById(className, id, function() {
		var response = this.responseText;
		
		if(typeof response === 'string') {
			var obj = JSON.parse(response);
		}
		
		if(typeof obj === 'object') {
			if(checkPrevEditDivs()) {
				document.body.removeChild(document.getElementsByClassName('admin-edit-elem')[0]);
			}
			
			// создаем все необходимые элементы
			var editDiv = createEditDiv(obj, className);
			
			// делаем из textarea объект tinymce
			initTinyMCE('.admin-edit-elem textarea', false);
			
			// вешаем на кнопки события
			editDiv.addEventListener('click', function(e) {
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
					sendSaveRequest({
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
}

function createEditDiv(obj, className) {
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
		form.innerHTML = 'ID: ' + obj['news_id'] + ' | ' + 
						 'Загловок: ' + obj['news_header'];
		textarea.innerHTML = obj['news_text'];
	}
	else {
		form.innerHTML = 'ID: ' + obj['publs_id'] + ' | ' + 
						 'Загловок: ' + obj['publs_header'];
		textarea.innerHTML = obj['publs_text'];
	} 
		
	form.appendChild(textarea);
	form.appendChild(saveBtn);
	form.appendChild(closeBtn);
	div.appendChild(form);
	document.body.appendChild(div);
	
	return div;
}

function checkPrevEditDivs() {
	var div = document.getElementsByClassName('admin-edit-elem');
	
	return (div.length > 0) ? true : false;
}

function sendSaveRequest(argArr, reqType, reqTarget, contentType) {
	var data = '', j = 1;
	
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

function getElemById(className, id, callback) {
	var request = new XMLHttpRequest();
	var pattern = (className === 'article-news') ? 'news' : 'publs' ;
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
	
	var pageNum = getParam('page', urlParams);
	
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

function getParam(value, obj) {
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

function isAdmin(callback) {
	var request = new XMLHttpRequest();
	request.onreadystatechange = function () {
		if(request.readyState == 4) {
			clearTimeout(timeout);
			var resp = request.getResponseHeader('IsAdmin');
			
			if(resp != null) {
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