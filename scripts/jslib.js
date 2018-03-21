'use strict';
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
	if(!target) return;
	
	var optionsOrCapture = options;
	
	if (!supportsPassive) {
		optionsOrCapture = options.capture;
	}
	
	if(target.length !== undefined) {
		for(var i=0, len=target.length; i<len; i++) {
			target[i].addEventListener(type, handler, optionsOrCapture);
		}
	}
	else target.addEventListener(type, handler, optionsOrCapture);
}

// ищем родителя элемента
function findParent(child, parentClass) {
	var parent = child.parentNode;

	while(parent && parent.parentNode) {
		if(parent.classList.contains(parentClass)) return parent;
		if(parent.tagName === 'BODY') return null;
		
		parent = parent.parentNode;
	}
	
	return null;
}

// получает все параметры адресной строки
function getUrlParam(value, obj) {
	for(var param in obj) {
		if(param == value) {
			return obj[param];
		}
	}
	
	return false;
}

// проверяем есть ли файл
function isFileExists(url) {
	var http = new XMLHttpRequest();
	http.open('HEAD', url, true);
	http.send();
	return http.status != 404;
}

// подключаем скрипт
function appendScript(src) {
	var script = createDOMElem({tagName: 'script', args: [{name: 'src', value: src}]});
	document.body.appendChild(script);
}

// получает значение параметра из адресной строки
function getParamFromLocationSearch(parName) {
	var location = window.location.search.substring(1);
	var params = location.split('&');
	
	for(var i=0, len=params.length; i<len; i++) {
		var val = params[i].split("=");
		if(val[0] == parName) {
			return val[1];
		}
	}
	
	return null;
}

// Функция создает DOM-элемент и возвращает его
/*
	elemParams {
		tagName: '',
		className: '',
		id: '',
		args: [{name: '', value: ''...}],
		innerHTML: ''
	}
*/
function createDOMElem(elemParams) {
	if(!elemParams.tagName) return;
	
	var elem = document.createElement(elemParams.tagName);
	
	if(elemParams.className) elem.className = elemParams.className;
	
	if(elemParams.id) elem.id = elemParams.id;
	
	if(elemParams.args) {
		for(var i=0, len=elemParams.args.length; i<len; i++) {
			elem.setAttribute(elemParams.args[i].name, elemParams.args[i].value);
		}
	}
	
	if(elemParams.innerHTML) {
		elem.innerHTML = elemParams.innerHTML;
	}
	
	DEBUG(createDOMElem.name, 'Элемент создан: ' + elem);
	
	return elem;
}

// Функция поиска элемента 
/*
	- принимает параметры элемента, который должен быть найден,
	- и родителя, от которого искать
	- возвращает найденный элемент
*/
function getElems(query, parent) {
	var elem;
	
	if(!parent) parent = document;
	
	// запрос без параметров - поиск по ID
	if(typeof(query) !== 'object') {
		elem = parent.getElementById(query);
	}
	else if(query.length){
		var queryObj = {'name': query[0], 'num': query[1], 'tagname': query[2]};

		if(queryObj.num >= 0) {
			if(!queryObj.tagname) {
				elem = parent.getElementsByClassName(queryObj.name)[queryObj.num];
			}
			else {
				elem = parent.getElementsByTagName(queryObj.tagname)[queryObj.num];
			}
		}
		else {
			if(!queryObj.tagname) {
				elem = parent.getElementsByClassName(queryObj.name);
			}
			else {
				elem = parent.getElementsByTagName(queryObj.tagname);
			}
			
			// объект не найден, вернулся []
			if(elem.length === 0) {
				elem = null;
			}
		}
	}
	else DEBUG(getElems.name, "Error in elems searching!");
	
	if(elem) {
		DEBUG(getElems.name, "Found elem/elem: " + elem);
		return elem;
	}
}

// Проверяем есть ли класс/классы у элемента
/*
	- принимает параметры элемента, который должен быть найден,
	- массив классов
	- параметр на строгий поиск всех классов
	- возвращает true/false
*/
function checkClass(elem, classes, strict) {
	if(classes.length > 1) {
		if(!elem.classList.contains(classes.pop())) {
			return strict ? false : checkClass(elem, classes);
		}
		else {
			return !strict ? true : checkClass(elem, classes, strict);
		}
	}
	else return elem.classList.contains(classes.pop());	// последний элемент
}

// Вывод отладочной информации
/*
	- принимает название функции и текст для вывода
*/
function DEBUG(funcName, output) {
	if(_DEBUG) {
		if(typeof output !== 'string') {
			output = output.toString();
		}
		if(funcName === '') {
			funcName = 'no name';
		}
		console.log('func: '+funcName+'; output: '+output);
	}
}