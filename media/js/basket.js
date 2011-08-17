$(function(){
	
	$('#delivery_option').change(function(){
		$.ajax({
			type: 'POST',
			url: '/basket/update_delivery_option',
			data: ({ id:$(this).val() }),
			beforeSend: function(){
				$('#delivery-option-spinner').show();
			},
			success: function(response){
				$('#delivery_price').html(response);
				update_basket_total();
			},
			complete: function(){
				$('#delivery-option-spinner').hide();
			}
		});
	});
	
	$('a.quantity-adjuster').click(function(e){
	
		e.preventDefault();

		// Use basket item not product!
		var basketItemId = $(this).attr('rel');
		var quantity;
	
		if ($(this).hasClass('remove')){
			quantity = 0;
		}
		else {
			quantity = ($(this).hasClass('add')) ? parseInt($('#'+basketItemId+'-quantity').val()) + 1 : parseInt($('#'+basketItemId+'-quantity').val()) - 1; 
		}
	
		var basketItem = {
			item_id: basketItemId,
			quantity: quantity
		};
	
		$.ajax({
 			url: '/basket/adjust_item',
			type: 'POST',
			data: basketItem,
			dataType: "json",
			success: function(response){
			
				if (response.line_items > 0){
					$('input#'+basketItemId+'-quantity').val(response.line_items);
					$('span#'+basketItemId+'-total').html(response.line_total);
					
					if (response.line_items == 1){
						$('a#remove-item-'+basketItemId).addClass('hidden');
					}
					else {
						$('a#remove-item-'+basketItemId).removeClass('hidden');
					}
				}
				else {
					$('div#basket-item-'+basketItemId).slideUp();
				}
				
				$('span#subtotal').html(response.basket_subtotal);
				update_basket_total();
				
				// Shrinks the number in the basket widget, updates it and expands it back.
				$('div#basket_left').hide('clip', function(){
					$('div#basket_left').html(response.basket_items).show('clip');
				});
				
				// Check if we need to update the delivery option.
				$.ajax({
					url: '/basket/update_delivery_option',
					type: 'GET',
					success: function(response){
						$('#delivery_price').html(response);
					}
				});
				
				// Check if we now qualify for checkout
/*
				$.ajax({
					url: '/basket/qualifies_for_checkout',
					type: 'GET',
					success: function(response){
						
						if (response == '1'){
						
							if ($('#not-qualifies-for-checkout').is(':visible')){
								$('#not-qualifies-for-checkout').hide('clip', function(){
									$('#qualifies-for-checkout').show('clip');
								});
							}
						}
						else {
						
							if ($('#qualifies-for-checkout').is(':visible')){
							
								$('#qualifies-for-checkout').hide('clip', function(){
									$('#not-qualifies-for-checkout').show('clip');
								});
							}
						}
					}
				});
*/
			}
		});
	});
	
	// Hide the manual basket update form elements if JS enabled
	// as user will be able to use the awesome + and - buttons!
	$('input[name="update"]').hide();
	$('.item-quantity').attr('readonly', 'readonly').addClass('disabled');
	
	// Add the promotion code over AJAX
	$('#add-promotion-code').live('click', function(e){
		
		e.preventDefault();
		
		var promotionCode = $('#promotion-code').val();
		
		if (promotionCode != ''){
		
			$.ajax({
			
				url: '/basket/add_promotion_code',
				type: 'POST',
				data: { code: promotionCode },
				beforeSend: function(){
				
					// Show AJAX spinner
					$('#promotion-code-spinner').show();
					$('#add-promotion-code').attr('disabled', 'disabled');
					
					// Hide any old errors
					$('#promotion-code-error').hide();
				
				},
				error: function(){
					
					// Handle error message for failure, probably an invlaid code
					$('#promotion-code-error').html('The code entered is invalid.').fadeIn();
					
				},
				success: function(response){
					
					$('#promotion-code').val('');
					$('#current-promotion-code').show();
					$('#promotion-code-form').hide();
					$('#current-promotion-code-code').html(response);
					
					update_basket_total();
				},
				complete: function(){
					
					// Hide AJAX spinner
					$('#promotion-code-spinner').hide();
					$('#add-promotion-code').removeAttr('disabled');			
				}
			
			});

		} else {
		
			$('#promotion-code-error').html('Oops! No code entered.').fadeIn();
		
		}
		
	});
	
	$('#remove-promotion-code').live('click', function(e){
		
		e.preventDefault();
		
		$.ajax({
			url: '/basket/remove_promotion_code',
			type: 'GET',
			beforeSend: function(){
				$('#promotion-code-spinner').show();
			},
			success: function(){
				$('#current-promotion-code').hide();
				$('#promotion-code-form').show();
				
				update_basket_total();
			},
			complete: function(){
				$('#promotion-code-spinner').hide();
			}
		});
		
	});
	
});

function update_basket_total(){
	$.ajax({
		type: 'GET',
		url: '/basket/update_total',
		dataType: 'json',
		success: function(response){
			$('#discount').html(response.discount.toString());
			if (response.discount > 0){
				$('#basket_discount').show();
			} else {
				$('#basket_discount').hide();
				$('#current-promotion-code').hide();
				$('#promotion-code-form').show();
			}
			$('#basket_total').html(response.basket_total);
		}
	});
}