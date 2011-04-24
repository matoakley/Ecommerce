$(function(){

	$('#bulk-actions').change(function(){
		
		if ($(this).val() == 'update_price'){
			
			$.fancybox({
				'href': '/admin/products/bulk_update_price',
		        'autoDimensions': false,
				'width': 525,
				'height': 'auto',
				'transitionIn': 'none',
				'transitionOut': 'none',
				'showCloseButton': false
			});
		}
		
		$(this).val('');
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
	
	$('#add-new-option-container').click(function(e){
	
		e.preventDefault();
		
		$('#no-options').hide();
		
		var option = $('#new-product-option').val().toLowerCase();
		var alphaOmega = 'alpha';
		
		if ($('.product-option-container').last().hasClass('alpha')){
			alphaOmega = 'omega';
		}
		
		var container = $('<div>').addClass('grid_8 field product-option-container ' + alphaOmega).attr('rel', option);
		
		var labelContainer = $('<div>').addClass('grid_2 alpha');
		$('<label>').html(ucwords(option)).appendTo(labelContainer);
		$('<a>').attr('href', '#').addClass('remove-product-option-container').attr('rel', option).html('remove').appendTo(labelContainer);		
		labelContainer.appendTo(container);
		
		var optionsContainer = $('<div>').addClass('grid_6 omega');
		$('<div>').addClass('option-container').appendTo(optionsContainer);
		$('<input>').attr('type', 'text').addClass('inputtext short').attr('placeholder', 'New value...').appendTo(optionsContainer);
		var addOption = $('<a>').attr('href', '#').addClass('add-product-option').attr('rel', option)
														.html('<img src="/images/icons/add.png" alt="" class="inline-icon" /> Add').appendTo(optionsContainer);

		$('<div>').addClass('clear').appendTo(optionsContainer);
		optionsContainer.appendTo(container);
		container.appendTo('#product-options');
		
		if ($('.product-option-container').last().hasClass('omega')){
			$('<div>').addClass('clear').appendTo('#product-options');
		}
		
		$('#new-product-option').val('');
	});
	
	$('.product-option-remove').live('click' , function(e){
	
		e.preventDefault();
		$(this).parent('div').slideUp('slow', function(){
			$(this).remove();
		});
	});
	
	$('.add-product-option').live('click', function(e){
		
		e.preventDefault();
		
		var addButton = $(this);
		var newOptionValue = $(this).prev('input');
		var i = parseInt($(this).siblings('.option-container').children('.product-option-row').last().attr('id')) + 1;
		
		if (newOptionValue.val() != ''){
		
			var newOption = $('<div>').addClass('hidden product-option-row').attr('id', i);
			$('<input>').attr('type', 'text')
									.attr('name', 'product\[product_options\]\[' + $(this).attr('rel') + '\]\[' + i + '\]\[value\]')
									.addClass('inputtext short')
									.val(newOptionValue.val())
									.appendTo(newOption);
			
			$('<span>').html(' ').appendTo(newOption);
			
			// Get the currently available statuses
			$.ajax({
				url: '/admin/products/option_statuses',
				type: 'GET',
				dataType: 'json',
				success: function(response){
					var optionStatus = $('<select>').attr('name', 'product\[product_options\]\[' + addButton.attr('rel') + '\]\[' + i + '\]\[status\]');
					
					for (var j = 0; j < response.length; j++)
					{
						$('<option>').val(response[j]).html(ucwords(response[j])).appendTo(optionStatus);
					}
																					
					optionStatus.appendTo(newOption);
					
					$('<a href="#" class="product-option-remove">remove</a>').appendTo(newOption);
				}
			});
																																		
			newOption.appendTo($(this).siblings('.option-container')).show('slide');
			newOptionValue.val('');
		}
	});

	$('a.remove-product-option-container').live('click', function(e){
	
		e.preventDefault();
		$('div[rel="' + $(this).attr('rel') + '"]').slideUp('fast', function(){
			$(this).remove();
		});
	});
});

function ucwords (str) {
	return (str + '').replace(/^([a-z])|\s+([a-z])/g, function ($1) {
	    return $1.toUpperCase();
	});
};