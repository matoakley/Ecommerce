$(function(){
	
	//quick search auto predict thing.
    
	$(".search").keyup(function(){
      var searchbox = $(this).val();
      var dataString = 'searchword='+ searchbox;
      
      if(searchbox!=''){
        $.ajax({
          type: "POST",
          url: "/admin/products/quick_search",
          data: dataString,
          cache: false,
          success: function(html){
          if (html != ''){
            $("#display").html(html).show();
          }
          	}
        });
      }
      return false;    
    });

    jQuery(function($){
       $("#searchbox").Watermark("Search");
       });

	
	$('#bulk-update-prices').live('click', function(){
	
		var products = {};
		var i = 1;
		
		$('.row-selector:checked').each(function(){
			
			products[i] = $(this).val();
			i++;
		});
	
		var data = {
		
			price: $('#bulk-update-prices-value').val(),
			products: products
		};
	
		$.ajax({
		
			url: '/admin/products/bulk_update_price',
			type: 'POST',
			data: data,
			success: function(){
			
				window.location.reload();
			}
		});
	});
	
		$('#product-meta-description').counter({
		count: 'up',
		goal: 160
	});
	$('#product-meta-keywords').counter({
		count: 'up',
		goal: 255
	});
	
	$('.product-image-row').live('mouseenter', function(){
		$(this).addClass('alternate');
	});
	$('.product-image-row').live('mouseleave', function(){
		$(this).removeClass('alternate');
	});

	$('#image-upload').change(function(){
	
		// Start the spinner
		$('#image-upload-spinner').show();
	
		// If file upload has changed then do an async upload
		$('#upload-image-form').submit();
	});
	
	// Check the iframe for the response
	$('#upload-image').load(function(){
	
		// Reset the upload field
		$('#image-upload-spinner').hide();
		$('#image-upload').val('');
		
		var imageCount = $('#product-images').children().length;
		
		// Load the new image into the page
		var response = $.parseJSON($('#upload-image').contents().children().children('body').children().html());
		
		// Build the divs etc
		var newRow = $('<div>').addClass('product-image-row hidden'); // Hidden to start so we can slide in :)
		var imageContainer = $('<div>').addClass('grid_3 alpha tc').appendTo(newRow);
		var defaultContainer = $('<div>').addClass('grid_2 tc').css('line-height', '120px').appendTo(newRow);
		var thumbContainer = $('<div>').addClass('grid_2 tc').css('line-height', '120px').appendTo(newRow);
		var altContainer = $('<div>').addClass('grid_6').appendTo(newRow);
		var toolContainer = $('<div>').addClass('grid_3 omega').appendTo(newRow);
		var clear = $('<div>').addClass('clear').appendTo(newRow);
		
		// Build the elements and place them in the divs
		$('<img>').attr('src', response.thumb_path).attr('alt', '').appendTo(imageContainer);
		
		var defaultRadio = $('<input>').attr('type', 'radio').attr('name', 'product[default_image]').val(response.id);
		var thumbRadio = $('<input>').attr('type', 'radio').attr('name', 'product[thumbnail]').val(response.id);
		
		// If this is the first image then we want to select it as default and thumb.
		if (imageCount == 0){
			defaultRadio.attr('checked', 'checked');
			thumbRadio.attr('checked', 'checked');
		}
		
		defaultRadio.appendTo(defaultContainer);
		thumbRadio.appendTo(thumbContainer);
		$('<textarea>').addClass('wide').attr('name', 'product_images['+response.id+'][alt_text]').appendTo(altContainer);
		
		var deleteAnchor = $('<a>').attr('href', '/admin/products/delete_image/'+response.id).addClass('delete-product-image').html('Delete');
		$('<img>').attr('src', '/images/icons/delete.png').attr('alt', '').addClass('inline-icon').prependTo(deleteAnchor);
		deleteAnchor.appendTo(toolContainer);
		
		// Add row to the page and show it
		$('#product-images-header').show();
		
		newRow.appendTo($('#product-images')).show('slide');
	});

	
	$('.delete-product-image').live('click', function(e){
		
		e.preventDefault();
		
		if (confirm('Are you sure that you want to permanently delete this image?'))
		{
			var container = $(this).parents('.product-image-row');
		
			$.ajax({
			
				url: $(this).attr('href'),
				type: 'GET',
				dataType: 'json',
				success: function(response){
					
					container.hide('slide', function(){
						$(this).remove();
					});
					
					if ($('#product-images').children().length == 0){
						$('#product-images-header').hide();
					}
					else{
						// Set the default and thumbnail images after delete
						$(':radio[name="product\[default_image\]"][value="'+response.default_image+'"]').attr('checked', 'checked');
						$(':radio[name="product\[thumbnail\]"][value="'+response.thumbnail+'"]').attr('checked', 'checked');
					}
				}
			});
		}
	});
	
});

function ucwords (str) {
	return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
	    return $1.toUpperCase();
	});
};

// Are you sure you want to edit the SEO slug?

     $('#edit-slug').live('click', function(e){
        e.preventDefault();
          if (confirm('Are you sure you want to edit the SEO slug? This is an important field of the product and editing could cause issues with the display of the webpage.')) {
                $('#product-slug').removeAttr('readonly')
              }
      });
