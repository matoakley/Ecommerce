$(function(){

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
		$('<a>').attr('href', '#').addClass('remove-product-option-container')
															.attr('data-product-option', option)
															.attr('data-product-id', $(this).attr('data-product-id')).html('remove').appendTo(labelContainer);
		labelContainer.appendTo(container);
		
		var optionsContainer = $('<div>').addClass('grid_6 omega');
		$('<div>').addClass('option-container').appendTo(optionsContainer);
		$('<input>').attr('type', 'radio').attr('name', '').val('').attr('disabled', 'disabled').appendTo(optionsContainer);
		$('<span>').html(' ').appendTo(optionsContainer);
		$('<input>').attr('type', 'text').attr('id', 'new-option-input-'+option).addClass('inputtext short').attr('placeholder', 'New value...').appendTo(optionsContainer);
		$('<span>').html(' ').appendTo(optionsContainer);
		$('<img>').attr('src', '/media/images/admin/ajax-loader.gif').attr('alt', 'Loading…').attr('id', 'add-option-value-spinner-'+option).addClass('hidden left-pad inline-icon').appendTo(optionsContainer);
		var addOption = $('<a>').attr('href', '#').addClass('add-product-option').attr('data-option', option)
														.attr('data-product-id', $(this).attr('data-product-id'))
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
		
		var removeButton = $(this);
		var valueId = removeButton.attr('data-value-id');
		
		// Replace the Add… button with a spinner as feedback to the user
		removeButton.hide();
		$('#remove-option-value-spinner-'+valueId).show();
		
		// Make an AJAX call to delete the option
		$.ajax({
			url: '/admin/products/remove_option',
			type: 'POST',
			dataType: 'json',
			data: { option_id: valueId },
			success: function(response){
				removeButton.parent('div').slideUp('slow', function(){
					$(this).remove();
				});
			},
			complete: function(){
				// Replace the spinner with the Add… button
				$('#remove-option-value-spinner-'+valueId).hide();
				removeButton.show();
			}
		});		
	});
	
	$('.add-product-option').live('click', function(e){
		
		e.preventDefault();
		
		var addButton = $(this);
		var newOptionKey = addButton.attr('data-option');
		var newOptionValue = $('#new-option-input-'+newOptionKey).val();
		var newOptionProductId = addButton.attr('data-product-id');
		
		// Hide any existing errors.
		$('#product-option-error-'+newOptionKey).slideUp();
			
		if (newOptionValue == ''){
			$('#product-option-error-'+newOptionKey).html('Option value cannot be empty.');
			$('#product-option-error-'+newOptionKey).slideDown();
			return true;
		}
			
		// Replace the Add… button with a spinner as feedback to the user
		addButton.hide();
		$('#add-option-value-spinner-'+newOptionKey).show();
	
		// Perform an AJAX call to create the new product option
		$.ajax({
			url: '/admin/products/add_option',
			type: 'POST',
			dataType: 'json',
			data: { product_id: newOptionProductId, key: newOptionKey, value: newOptionValue},
			success: function(response){
			
				if (response.option != null){
				
					// Add the new product option to the list of options
					var newOption = $('<div>').addClass('hidden product-option-row');
					$('<input>').attr('type', 'radio').attr('name', 'new_sku_options_'+newOptionKey)
											.addClass('new-sku-options').val(response.option.id).appendTo(newOption);
					$('<span>').html(' ').appendTo(newOption);
					$('<input>').attr('type', 'text')
											.attr('name', 'product_options\['+response.option.id+'\]\[value\]')
											.addClass('inputtext short')
											.val(response.option.value)
											.appendTo(newOption);
					$('<span>').html(' ').appendTo(newOption);
					$('<a href="#" class="product-option-remove" data-value-id="'+response.option.id+'">remove</a>').appendTo(newOption);
					newOption.appendTo(addButton.siblings('.option-container')).show('slide');
					$('#new-option-input-'+newOptionKey).val('');
					$('#add-new-product-sku').attr('disabled', false);
				} else {
					$('#product-option-error-'+newOptionKey).html(response.error);
					$('#product-option-error-'+newOptionKey).slideDown();
				}
			},
			complete: function(){
				// Replace the spinner with the Add… button
				$('#add-option-value-spinner-'+newOptionKey).hide();
				addButton.show();
			}
		});
	});

	$('a.remove-product-option-container').live('click', function(e){
	
		e.preventDefault();
		
		var removeButton = $(this);
		
		removeButton.hide();
		$('#remove-option-spinner-'+removeButton.attr('data-product-option')).show();
		
		// Make an AJAX call to remove all product options with this key
		$.ajax({
			url: '/admin/products/remove_options',
			type: 'POST',
			dataType: 'json',
			data: { product_id: removeButton.attr('data-product-id') , option_key: removeButton.attr('data-product-option') },
			success: function(){
				$('div[rel="' + removeButton.attr('data-product-option') + '"]').slideUp('fast', function(){
					$(this).remove();
				});
			}
		});
	});
	
	$('#add-new-product-sku').live('click', function(e){
		
		e.preventDefault();
		
		// Get all of the selected radio boxes
		var options = $('.new-sku-options:checked');
		var optionIds = [];
		
		options.each(function(){
			optionIds.push($(this).val());
		});
		
		var addButton = $(this);
		
		addButton.hide();
		$('#product-sku-add-spinner').show();
		
		$.ajax({
			url: '/admin/products/add_sku',
			type: 'POST',
			dataType: 'json',
			data: { product_options: optionIds, product_id: $(this).attr('data-product-id') },
			success: function(response){

				if (response.sku != null){
				
					// TODO: Add sku
					window.location.reload(true);
					
				} else {
					$('#sku-add-error').html(response.error);
					$('#sku-add-error').slideDown();
					$('#product-sku-add-spinner').hide();
					addButton.show();
				}
			}
		});
	});
	
	$('.sku-delete-button').live('click', function(e){
		
		e.preventDefault();
		
		var removeButton = $(this);
		var skuId = removeButton.attr('data-sku-id');
		
		removeButton.hide();
		$('#product-sku-remove-spinner-'+skuId).show();
		
		$.ajax({
			url: '/admin/products/remove_sku',
			type: 'POST',
			dataType: 'json',
			data: { sku_id: skuId },
			success: function(response){
				$('#product-sku-row-'+skuId).slideUp();
			},
			complete: function(){
				$('#product-sku-remove-spinner-'+skuId).hide();
				removeButton.show();
			}
		});
	});
});