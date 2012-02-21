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
	
	$('#promotion-code-discount-on').change(function(){
		
		if ($(this).val() == 'sales_order_item'){
			$('#promotion-code-products').show();
		}
		else {
			$('#promotion-code-products').hide();
		}
		
	});
	
	$('.remove-promotion-code-product').live('click' , function(e){
	
		e.preventDefault();
		
		var productId = $(this).attr('rel');
		$('.promotion-code-product-container[rel="'+productId+'"]').slideUp('slow', function(){
			$(this).remove();
		});
	});

	$('#add-product').live('keyup', function(){
	
		var searchTerm = $(this).val();
		var autoList = $('#promotion-code-products-live-search');
		
		// Only perform a lookup if we have 3 or more chars to work with
		if (searchTerm.length > 2){
		
			$.ajax({
				url: '/admin/products/live_search',
				type: 'GET',
				data: { q: searchTerm },
				dataType: 'json',
				success: function(response){
					
					autoList.children().each(function(){
						$(this).remove();
					});
					
					for (var i = 0; i < response.length; i++){
					
						$('<li>').html(response[i].name+' <a href="#" data-id="'+response[i].id+'" data-name="'+response[i].name+'" class="promotion-code-product-live-search-add">add</a>').appendTo(autoList);
					
					}
					
				}
			});
		
		}
		else {
			autoList.children().each(function(){
				$(this).remove();
			});
		}
		
	});
	
	$('.promotion-code-product-live-search-add').live('click', function(e){
		
		e.preventDefault();
		
		var productId = $(this).attr('data-id');
		var productName = $(this).attr('data-name');
		
		$('#promotion-code-products-live-search').children().each(function(){
			$(this).remove();
		});
		
		$('#add-product').val('');
		
		// Add the product to the list
		var productContainer = $('<div>').addClass('field promotion-code-product-container').attr('rel', productId);
		$('<div class="grid_2 alpha">&nbsp;</div>').appendTo(productContainer);
		var businessEnd = $('<div>').addClass('grid_14 omega');
		$('<a>').attr('href', '/admin/products/view/'+productId).html(productName).appendTo(businessEnd);
		$('<span>').html(' ').appendTo(businessEnd);
		$('<a>').attr('href', '#').attr('rel', productId).addClass('remove-promotion-code-product').html('remove').appendTo(businessEnd);
		businessEnd.appendTo(productContainer);
		$('<input>').attr('type', 'hidden').attr('name', 'promotion_code\[products\]\[\]').val(productId).appendTo(productContainer);
		$('<div>').addClass('clear').appendTo(productContainer);
		productContainer.appendTo($('#promotion-code-products-container'));
		
	});
	
	$('a.edit_reward').fancybox({
		modal: true
	});
	
	$('a.delete-reward').live('click', function(e){
		
		e.preventDefault();
		
		var button = $(this);
		
		if (confirm('Are you sure that you want to permanently delete this reward?')){
			$.ajax({
				url: button.attr('href'),
				beforeSend: function(){},
				error: function(){},
				success: function(){
					var row = button.parents('div.promotion-code-reward-row');
					row.slideUp('slow', function(){
						row.remove();
					});
				},
			});
		}
	});
	
	$('#reward_reward_type').live('change', function(){
		
		if ($(this).val() == 'item'){
			$('#reward-discount-fields').slideUp('slow', function(){
				$('#reward-item-fields').slideDown('slow', function(){
					$.fancybox.resize();
				});
			});
		}
		else if ($(this).val() == 'discount'){
			$('#reward-item-fields').slideUp('slow', function(){
				$('#reward-discount-fields').slideDown('slow', function(){
					$.fancybox.resize();
				});
			});
		}
	});
	
	$('#add-promotion-code-reward').live('click', function(){
		
		var button = $(this);
		
		var data = {
			reward: {
				reward_type: $('#reward_reward_type').val(),
				basket_minimum_value: $('#reward_basket_minimum_value').val(),
				discount_amount: $('#reward_discount_amount').val(),
				discount_unit: $('#reward_discount_unit').val(),
				sku: $('#reward_sku').val(),
				sku_reward_price: $('#reward_sku_reward_price').val()
			}
		};
		
		$.ajax({
			url: button.attr('data-submit-url'),
			type: 'POST',
			data: data,
			dataType: 'json',
			beforeSend: function(){},
			error: function(response){
			},
			success: function(response){
				//close the modal
				$.fancybox.close();
				//add the new reward to the page
				$('#promotion-code-rewards').html(response.view);
				// Re-add the fancybox functionality to include new elements
				$('a.edit_reward').fancybox();
			}
		});
	});
	
	$('#promotion-code-run-indefinitely').change(function(){
		if ($(this).is(':checked')){
			$('#promotion-code-valid-from').attr('disabled', 'disabled');
			$('#promotion-code-valid-from-hour').attr('disabled', 'disabled');
			$('#promotion-code-valid-from-minute').attr('disabled', 'disabled');
			$('#promotion-code-valid-until').attr('disabled', 'disabled');
			$('#promotion-code-valid-until-hour').attr('disabled', 'disabled');
			$('#promotion-code-valid-until-minute').attr('disabled', 'disabled');
		} else {
			$('#promotion-code-valid-from').removeAttr('disabled');
			$('#promotion-code-valid-from-hour').removeAttr('disabled');
			$('#promotion-code-valid-from-minute').removeAttr('disabled');
			$('#promotion-code-valid-until').removeAttr('disabled');
			$('#promotion-code-valid-until-hour').removeAttr('disabled');
			$('#promotion-code-valid-until-minute').removeAttr('disabled');		
		}
	});

});