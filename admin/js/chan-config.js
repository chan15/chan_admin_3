CKEDITOR.editorConfig = function( config ) {
	config.toolbar =
		[
			{name:'document', items:['Source','-','Preview','Print']},
			{name:'clipboard', items:['Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo']},
			{name:'basicstyles', items:['Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat']},
			'/',
			{name:'paragraph', items:['NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl']},
			{name:'links', items:['Link','Unlink']},
			{name:'insert', items:['Image','Table']},
			{name:'tools', items:['Maximize']}
		];
    config.enterMode = CKEDITOR.ENTER_BR;
    config.height = 400;
	config.filebrowserImageUploadUrl = 'ckeditor_upload.php';
};
