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

document.addEventListener('DOMContentLoaded', isAdmin(addCloseBtn), false);

function addCloseBtn() {
	var articleNews = document.getElementsByClassName('article-news');
	
	for(var i=0; i<articleNews.length; i++) {
		var div = document.createElement('div');
		div.className = 'admin-close-button';
		div.innerHTML = 'X';
		var firstChild = articleNews[i].children[0];
		articleNews[i].insertBefore(div, firstChild);
	}
}

function navigateUlList(e) {
	var target = e.target;
	
	if(target === e.currentTarget) {
		e.stopPropagation();
		return;
	}
	
	var urlArr = decodeURIComponent(location.search.substr(1)).split('&');
	var pair, urlParams = new Object;
	
	for(var i=0; i<urlArr.length; i++) {
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
		if(param == value)
			return obj[param];
	}
	return false;
}

function replaceNewsLinks() {
	var container = document.body.getElementsByClassName('article')[0];
	var parents = container.getElementsByTagName('P');
	for(var i=0; i<parents.length; i++) {
		var link = parents[i].getElementsByTagName('A')[0];
		link.setAttribute('href', 'index.php?pages=news&custom-news-date=' + link.getAttribute('href').substring(0, link.getAttribute('href').length - 5));
		//console.log(link.getAttribute('href'));
	}
}

function replacePressLinks() {
	var press = document.body.getElementsByClassName('article-press');
	for(var i=0; i<press.length; i++) {
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
	for(var i = 0; i<imgs.length; i++) {
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
	request.open('HEAD', 'content/json.php', true);
	request.onreadystatechange = function () {
		if(request.readyState == 4) {
			var resp = request.getResponseHeader('IsAdmin');
			if(resp != null) {
				console.log("Вы - Админ. Поздравляю!");
				if(typeof callback == 'function') {
					callback.apply(request);
				}
			}
			else {
				console.log("Вы - не Админ. Херово!");
			}	
		}
	};
	request.send();
}