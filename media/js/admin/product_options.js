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
		if (isNaN(i)) {
			i = 0;
		}
		
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