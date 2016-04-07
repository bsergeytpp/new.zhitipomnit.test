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