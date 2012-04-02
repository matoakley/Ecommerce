$(function(){

	$('#image-upload').change(function(){
	
		var uploadButton = $(this);
	
		// Remove the current image and replace with a spinner
		$("#current-image").attr('src', '/media/images/admin/ajax-loader.gif');
		
		// If file upload has changed then do an async upload
		$('#upload-image-form').submit();
		
		// Check the iframe for the response
		$('#upload-image').load(function(){
			
			// Set the new image into the page
			var date = new Date().getTime();
			$("#current-image").attr('src', '/images/blog-posts/'+uploadButton.attr('data-blog-post-id')+'.jpg?'+date);
			
			// Reset the upload field
			$('#image-upload-field').val('');
		});
		
	});
	
});


