/**
 * @license Copyright (c) 2003-2012, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For the complete reference:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	config.toolbar_chan = 
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
	config.toolbar = 'chan';
	config.filebrowserImageUploadUrl = 'ckeditor_upload.php';
};
