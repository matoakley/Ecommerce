$(function(){

	$('#generate-promotion-code').click(function(e){
	
		e.preventDefault();
	
		// AJAX call to generate a unique code
		$.ajax({
			url: '/admin/promotion_codes/auto_generate',
			type: 'GET',
			beforeSend: function(){
				$('#promotion-code-auto-generate-icon').attr('src', '/images/admin/ajax-loader.gif');
			},
			success: function(response){
				$('#promotion-code-code').val(response);
			},
			complete: function(){
				$('#promotion-code-auto-generate-icon').attr('src', '/images/icons/cog.png');			
			}
		});
	
	});

});