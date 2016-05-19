function initTinyMCE(className, isInline) {
	tinymce.init({
		inline: isInline,
		selector: className,
		language: 'ru_RU',
		plugins: 'code',
		paste_data_images: true
	});
}

function editBtnOnClick(pattern) {
	var editBtns = document.getElementsByClassName("edit-btn");
		
	for(var i=0; i<editBtns.length; i++) {
		editBtns[i].addEventListener('click', function(e) {
			var target = e.target;
			
			if(target === e.currentTarget) {
				e.stopPropagation();
				return;
			}
			
			var parent = this.parentNode;
			var prevNode = parent.previousSibling;
			var textArea = prevNode.lastChild;
			
			if(this.innerHTML.indexOf('Редактировать') != -1) {
				textArea.className = pattern + '-textarea';
				initTinyMCE('.' + pattern + '-textarea', true);
				this.innerHTML = '<strong>Сохранить</strong>';
				parent.style.background = 'lightgray';
				prevNode.style.background = 'lightgray';
			}
			else if(this.innerHTML.indexOf('Сохранить') != -1) {
				var updatedText = tinymce.activeEditor.getContent();
				var id = prevNode.firstChild.innerHTML;
				var updatedData = "id=" + encodeURIComponent(id) + "&" +
								   "text=" + encodeURIComponent(updatedText);
				var request = new XMLHttpRequest();
				request.open('POST', 'update_' + pattern + '.php', true);
				request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
				request.send(updatedData);
				document.location.reload(true);
			}
		}, false);
	}
}