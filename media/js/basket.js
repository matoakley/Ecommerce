$(function(){
	$('a.quantity-adjuster').removeClass('hidden');
	$('#delivery_option').change(function(){
	  var dropdown = $(this);
		$.ajax({
			type: 'POST',
			url: '/basket/update_delivery_option',
			data: { id:dropdown.val() },
			dataType: 'json',
			beforeSend: function(){
				$('#delivery-option-spinner').show();
			},
			success: function(response){
				$('#delivery_price').html(response.price);
				$('#delivery_option').val(response.id);
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
		   if ($('#delivery_option').length) {
  	     var id = $('#delivery_option');
  	     }
	     else {
  	     var id = $('#id');
  	     }
				$.ajax({
					url: '/basket/update_delivery_option',
					type: 'POST',
					dataType: 'json',
					data: {id:id.val()},
					success: function(response){
  						 $('#delivery_price').html(response.price);
  						 $('#delivery_option').val(response.id);
					},
					complete: function(){
						update_basket_total();
					}
				});
			}
		});
	});
	
	//a function to make the response of the hidden delivery option into a selectable option in the dropdown, only once!
	
	function add_hidden_delivery_option(id){
	
	if ( !$('#hidden_option').length) {
	
  	$.ajax({
			type: 'POST',
			url: '/delivery_options/available_options',
			data: {id:id},
			dataType: 'json',
			async: false, // to stop things being done too cleverly at the same time
			success: function(response){
				var hiddenOption = '<option id="hidden_option" value="' + response.id + '" selected="selected">' + response.name + '</option>';
				$('#delivery_option').append(hiddenOption);
				
			},
			complete: function () {

  			}
  		});
  	}
	}
	
	// Hide the manual basket update form elements if JS enabled
	// as user will be able to use the awesome + and - buttons!
	$('input[name="update"]').hide();
	$('.item-quantity').attr('readonly', 'readonly').addClass('disabled');
	
	// Add the promotion code over AJAX
	$('#add-promotion-code').on('click', function(e){
		
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
	
	$('#remove-promotion-code').on('click', function(e){
		
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
	
	$('#use_reward_points').on('click', function(e){
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
	
	$('button#add-referral-code').on('click', function(e){
  	e.preventDefault();
  	var code = $('#box-add-referral-code').val();
  	$.ajax({
			url: '/basket/add_customer_referral_code',
			type: 'POST',
			data: { code: code},
			dataType: 'json',
			success: function(response){
			  if (response.code){
  				$('#add-referral-code-form').slideUp(400, function(){
  				  $('#current-referral-code-code').html(response.code);
    				$('#current-referral-code').removeClass('hidden').slideDown();
  				});
  		  } else {
    		  $('#referral-code-error').removeClass('hidden').slideDown();
  		  }
			}
		});
	});
});