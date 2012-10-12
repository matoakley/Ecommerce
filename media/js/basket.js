$(function(){
	$('a.quantity-adjuster').removeClass('hidden');
	$('#delivery_option').change(function(){
	  var dropdown = $(this);
		$.ajax({
			type: 'POST',
			url: '/basket/update_delivery_option',
			data: { id:dropdown.val() },
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
			quantity = ($(this).hasClass('increment')) ? parseInt($('#'+basketItemId+'-quantity').val()) + 1 : parseInt($('#'+basketItemId+'-quantity').val()) - 1; 
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
				$('#delivery_price').html(response.shipping);
				$('span#vat').html(response.basket_vat);
				
				// Shrinks the number in the basket widget, updates it and expands it back.
				$('#basket_left').hide('clip', function(){
					$('#basket_left').html(response.basket_items);
					$('#basket_left').show('clip');
				});
				$('#basket_left_total').hide('clip', function(){
					$('#basket_left_total').html(response.basket_total);
					$('#basket_left_total').show('clip');
				});
				
				if (response.max_reward_points != null){
  				$('#max_reward_points').html(response.max_reward_points);
  		  }
  		  if (response.max_reward_points_discount != null){
				  $('#max_reward_points_discount').html(response.max_reward_points_discount);
				}
				
				// Check if we need to update the delivery option.
				$.ajax({
					url: '/basket/update_delivery_option',
					type: 'GET',
					success: function(response){
						if (response != 'false'){
							$('#delivery_price').html(response);
						}
					},
					complete: function(){
						update_basket_total();
					}
				});
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
				dataType: 'json',
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
					$('#promotion-code-form').hide('slow', function(){
						$('#current-promotion-code').removeClass('hidden').show('slow');
					});
					$('#current-promotion-code-code').html(response.code);
					$('#promotion-code-reward-item').html(response.reward_item).removeClass('hidden').slideDown();
					
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
				$('#current-promotion-code').hide('slow', function(){
					$('#promotion-code-form').removeClass('hidden').show('slow');
				});
				
				$('#promotion-code-reward-item').slideUp();
				
				update_basket_total();
			},
			complete: function(){
				$('#promotion-code-spinner').hide();
			}
		});
		
	});
	
	function update_basket_total(){
		$.ajax({
			type: 'POST',
			cache: false,
			url: '/basket/update_total',
			dataType: 'json',
			success: function(response){
				$('#discount').html(response.discount.toString());
				if (response.discount > 0){
					$('#basket_discount').removeClass('hidden').show('slow');
				} else {
					$('#basket_discount').hide();
				}
				$('#basket_total').html(response.basket_total);
				$('#subtotal').html(response.basket_subtotal);
			}
		});
	}
	
	$('#use_reward_points').live('click', function(e){
  	var use_reward_points = $(this).is(':checked');
  	$.ajax({
			url: '/basket/use_reward_points',
			type: 'POST',
			dataType: 'json',
			data: { use_reward_points: use_reward_points },
			success: function(response){
			  //maybe some discount stuff in here.
			  $('#discount').html(response.basket_discount);
			  if (parseInt(response.basket_discount) > 0){
  			  $('#basket_discount').slideDown(); 
			  } else {
  			  $('#basket_discount').slideUp();
			  }
			  $('#basket_total').html(response.basket_total);
			}
	  });
	});
	
	$('button#add-referral-code').live('click', function(e){
  	e.preventDefault();
  	var code = $('#box-add-referral-code').val();
  	var href = $(this).data('url');
  	var basket = $('#box-add-referral-code').data('id');
  	
  	$.ajax({
				url: href,
				type: 'POST',
				data: { code: code, basket: basket },
				dataType: 'html',
				beforeSend: function(){
				},
				success: function(response){
				console.log(response);
				if (response){
				  alert('The code you entered is incorrect, please try again.');
				 }
				else {
  				$('#referral-code-enter').slideUp().delay(100);
				  $('#referral-code-thank-you').removeClass('hidden').slideDown();
				 }
				},
				complete: function(){
				}
			
			});
	})
	
	
});