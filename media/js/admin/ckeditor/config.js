/*
Copyright (c) 2003-2010, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	// Define changes to default configuration here. For example:
	// config.language = 'fr';
	// config.uiColor = '#AADC6E';
	config.removePlugins = 'scayt';
	config.enterMode = CKEDITOR.ENTER_BR;
	config.autoParagraph = false;
	CKEDITOR.on('instanceReady', function( ev ) {
	  var blockTags = ['div','h1','h2','h3','h4','h5','h6','p','pre','li','blockquote','ul','ol',
	  'table','thead','tbody','tfoot','td','th','p'];
	
	  for (var i = 0; i < blockTags.length; i++)
	  {
	     ev.editor.dataProcessor.writer.setRules( blockTags[i], {
	        indent : false,
	        breakBeforeOpen : false,
	        breakAfterOpen : false,
	        breakBeforeClose : false,
	        breakAfterClose : false
	     });
	  }
  });
};
