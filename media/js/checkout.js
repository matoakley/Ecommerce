$(function(){
	
	$('#delivery_address_same').click(function(){
		var container = $('#delivery_address_container');
		
		if($(this).attr('checked')){
			container.slideUp();
		}
		else{
			container.slideDown();
		}
	});
	
	$('form#portal_form').submit();

});