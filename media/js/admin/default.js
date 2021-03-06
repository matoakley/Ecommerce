$(function(){
    
  //Page image upload 
  $(function(){

  	$('#page-image-upload').change(function(){
  	
  		var uploadButton = $(this);
  	
  		// Remove the current image and replace with a spinner
  		$("#current-image").attr('src', '/media/images/admin/ajax-loader.gif');
  		
  		// If file upload has changed then do an async upload
  		$('#upload-image-form').submit();
  		
  		// Check the iframe for the response
  		$('#upload-image').load(function(){
  			
  			// Set the new image into the page
  			var date = new Date().getTime();
  			$("#current-image").attr('src', '/images/pages/'+uploadButton.attr('data-page-id')+'.jpg?'+date);
  			
  			// Reset the upload field
  			$('#image-upload-field').val('');
  		});
  		
  	});
  	
  });
  
  //Brand image upload
  $(function(){

  	$('#brand-image-upload').change(function(){
  	
  		var uploadButton = $(this);
  	
  		// Remove the current image and replace with a spinner
  		$("#current-image").attr('src', '/media/images/admin/ajax-loader.gif');
  		
  		// If file upload has changed then do an async upload
  		$('#upload-image-form').submit();
  		
  		// Check the iframe for the response
  		$('#upload-image').load(function(){
  			
  			// Set the new image into the page
  			var date = new Date().getTime();
  			$("#current-image").attr('src', '/images/brands/'+uploadButton.attr('data-brand-id')+'.jpg?'+date);
  			
  			// Reset the upload field
  			$('#image-upload-field').val('');
  		});
  		
  	});
  	
  });
 
    $('#defaultDelivery').live('click', function(e){
      e.preventDefault();
      var defaultDelivery = $('#default-delivery').val();
	
		var data = {
			default: defaultDelivery};
	console.log(data);
		$.ajax({
		
			url: '/admin/delivery_options/default_delivery_option',
			type: 'POST',
			data: data,
			beforeSend: function(){
			$('#tick-delivery').hide();
  		$('#waiting-delivery').show();
  		$('#error-delivery').show();
			},
			success: function(){
			$('#waiting-delivery').hide();
			$('#tick-delivery').show();
  		//window.location.reload();
			},
			error: function(){
  		$('#waiting-delivery').hide();
			$('#error-delivery').show();
			},
		});
	});
    
	$('#bulk-actions').change(function(e){
		
		if ($(this).val() == 'delete'){
		e.preventDefault();
		if (confirm('Are you sure that you want to delete the selected item(s)?')) {
		
		var items = [];
  						var i = 0;
		
						$(".row-selector").filter(':checked').each(function(){
				
  						items[i] = $(this).val();
  						i++;    				
    				})
    				
    var data = {
    				items: items,
    				type: $('#type').text(),
    		}

		$.ajax({
		
			url: '/admin/tools/bulk_delete',
			type: 'POST',
			data: data,
			beforeSend: function(){
  			console.log(items);
			},
			success: function(response){
			  window.location.reload();
    				  }
    				});
    		  };
    		}
    else {
      if ($(this).val() == 'print_invoices'){
    		e.preventDefault();    		
    		var items = [];
      						var i = 0;
    		
    						$(".row-selector").filter(':checked').each(function(){
    				
      						items = items + '/'+$(this).val();
      						i++;    				
        				})

    		var url = "/admin/sales_orders/bulk_print"+items;
    		var print = $('a#bulk-print');
    		
    		$(function(){
      		print.attr('href', url);
          window.location.href = url;
    		})    		
          
        		}
    }
    
    });
	     
	 $('#bulk-actions').change(function(e){
		var status = $(this).val();
		var statusUgly = $('#bulk-actions option:selected').text();
		var statusPretty = statusUgly.replace('Mark ', '');
		
		if (status == 'awaiting_payment' || status == 'problem_occurred' || status == 'payment_received' || status == "order_cancelled" || status == 'invoice_generated' || status == 'invoice_sent' || status == 'complete'){
		e.preventDefault();
		
		if (confirm('Are you sure that you want to change the status of the selected item(s) to ' + statusPretty +  '?')) {
		
		var items = [];
  						var i = 0;
		
						$(".row-selector").filter(':checked').each(function(){
				
  						items[i] = $(this).val();
  						i++;
  				})
  				
	
  				var data = {
    				items: items,
    				status: status,
    				
    				}

		$.ajax({
		
			url: '/admin/tools/bulk_change_status',
			type: 'POST',
			data: data,
			success: function(response){
			  window.location.reload();
    				    }
    				 })
    				}
      		}
      });
    				
  
  $('#add-to-related').click(function(e){
    e.preventDefault();
    
    var product = $('#product-unrelated').val();
    var productOption = $('#product-unrelated option:selected');
    var originalProduct = $('#product-id').data('id');
    var data = {product_id: originalProduct, related_id: product};
    console.log(originalProduct);
    $.ajax({   
				url: '/admin/products/add_to_related_products',
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(response){
  				console.log(response);
  				}
		  })
        
    $('#product-related').append("<option value='" + product + "'>" + productOption.text() + "</option>");
    productOption.remove();
  
    console.log(product, productOption);
    
  })
  
   $('#remove-from-related').click(function(e){
    e.preventDefault();
    
    var product = $('#product-related').val();
    var productOption = $('#product-related option:selected');
    var originalProduct = $('#product-id').data('id');
    var data = {product_id: originalProduct, related_id: product};
    
    $.ajax({   
				url: '/admin/products/remove_from_related_products',
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(response){
  				console.log(response);
  				}
		  })
        
    $('#product-unrelated').append("<option value='" + product + "'>" + productOption.text() + "</option>");
    productOption.remove();
    
    console.log(product, productOption);
    
  })

  
   $('#add-to-bundle').click(function(e){
    e.preventDefault();
    
    var product = $('#product-sku-id').val();
    var productOption = $('#product-sku-id option:selected');
    var originalProduct = $('#product-id').data('id');
    var data = {product_id: originalProduct, sku_id: product};
    
    
    $.ajax({   
				url: '/admin/bundles/add_to_bundle',
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(response){
  				console.log(response);
  				}
		  })
        
    $('#product-bundle').append("<option value='" + product + "'>" + productOption.text() + "</option>");
    productOption.remove();
  
    console.log(product, productOption);
    
  })
  
   $('#remove-from-bundle').click(function(e){
    e.preventDefault();
    
    var product = $('#product-bundle').val();
    var productOption = $('#product-bundle option:selected');
    var originalProduct = $('#product-id').data('id');
    var data = {product_id: originalProduct, sku_id: product};
    
    $.ajax({   
				url: '/admin/bundles/remove_from_bundle',
				type: 'POST',
				data: data,
				dataType: 'json',
				success: function(response){
  				console.log(response);
  				}
		  })
        
    $('#product-sku-id').append("<option value='" + product + "'>" + productOption.text() + "</option>");
    productOption.remove();
    
    console.log(product, productOption);
    
  })


	$('#nav ul').superfish();
	
	$('.datepicker').datepicker({
		constrainInput: true,
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		numberOfMonths: 2,
		selectOtherMonths: true
	});
	
	$('.datepicker_one_month').datepicker({
		constrainInput: true,
		dateFormat: 'yy/mm/dd',
		firstDay: 1,
		numberOfMonths: 1,
		selectOtherMonths: true
	});
	
	// Manage datepicker for customer callbacks and show/hide "assigned to" selector as required 
	$('input#communication-callback-on').datepicker({
		constrainInput: true,
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		numberOfMonths: 2,
		selectOtherMonths: true,
		onSelect: function(dateText, inst){
  		$('select#communication-callback-assigned-to').removeAttr('disabled');
		}
   });
   $('input#communication-callback-on').change(function(){
     if ($(this).val() != ""){
      $('select#communication-callback-assigned-to').removeAttr('disabled');
     } else {
      $('select#communication-callback-assigned-to').attr('disabled', 'disabled');
     }
   });
	
	$('textarea.description').ckeditor({

		// CKFinder integration		
		filebrowserBrowseUrl : '/ckfinder/ckfinder.html',
    filebrowserImageBrowseUrl : '/ckfinder/ckfinder.html?Type=Images',
    filebrowserUploadUrl : '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
    filebrowserImageUploadUrl : '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images'
	});
	
	$('.delete-custom-field-document').live('click', function(e){
		e.preventDefault();
		
		if (confirm('Are you sure that you want to permanently delete this file?'))
		{
			$.ajax({   
				url: $(this).attr('href'),
				type: 'GET',
				dataType: 'json',
				success: function(response){
  				console.log(response);
  		    $('div.custom-field-upload-form[data-custom-field-id="'+response.custom_field.id+'"]').fadeOut(200, function(){
  			    $('div.custom-field-upload-form[data-custom-field-id="'+response.custom_field.id+'"]').html(response.html);
  		    })
  		    $('div.custom-field-upload-form[data-custom-field-id="'+response.custom_field.id+'"]').fadeIn(200);
				}
		  })
		}
  });

	$('.slugify').keyup(function(){
		$(this).slugify($('.slug'));
	});
	
	$('#check-all').click(function(){
    
      var checked = $(this).attr('checked');
      $('.row-selector').each(function(){
          
      if (checked){
              
      $(this).attr('checked', 'checked');
          }
      else {
              
      $(this).removeAttr('checked');
      }
    });
  });
	
	$('#bulk-actions').change(function(){
		
		if ($('.row-selector:checked').length == 0){
			
			$('#no-rows-selected').fadeIn();
			$(this).val('');
			return false;
		}
	});
	
	$('.row-selector').change(function(){
	
		if ($('#no-rows-selected').is(':visible')){
		
			$('#no-rows-selected').fadeOut();
		}
	});
	
	$('a.delete-button').click(function(e){
		if ( ! confirm('Are you sure that you want to permanently delete this item?'))
		{
			e.preventDefault();
		}
	});
	
	$('a.archive-button').click(function(e){
		if ( ! confirm('Are you sure that you want to archive this item?'))
		{
			e.preventDefault();
		}
	});
	
	$('#admin-items-per-page').change(function(){
		
		var noItems = $(this).val();
		
		$.ajax({
			url: '/admin/tools/items_per_page/'+noItems,
			type: 'GET',
			success: function(response){
				window.location.reload();
			}
		});
	});
	
	$('a.close-modal').live('click', function(e){
		e.preventDefault();
		$.fancybox.close();
	});
	
	// Show/hide body of customer communications
	$('img.show-communication').live('mouseenter', function(){
		if ($('div.communication-body[data-communication-id="'+$(this).attr('data-communication-id')+'"]').is(':visible')){
			$(this).attr('src', '/media/images/icons/magifier_zoom_out.png');
		} else {
			$(this).attr('src', '/media/images/icons/magnifier_zoom_in.png');	
		}
	}).live('mouseleave', function(){
		$(this).attr('src', '/media/images/icons/magnifier.png');
	}).live('click', function(){
		$('div.communication-body[data-communication-id="'+$(this).attr('data-communication-id')+'"]').slideToggle('slow');
		$('td.communication-body-container[data-communication-id="'+$(this).attr('data-communication-id')+'"]').toggleClass('active-communication-body-container');
	});
	
	$('a#show-new-contact').click(function(e){
		e.preventDefault();
		var button = $(this);
		$('div#new-contact').slideToggle(600, function(){
			if ($('div#new-contact').is(':visible')) {
				button.children('span').html('Hide New Contact');
			} else {
				button.children('span').html('New Contact');
			}
		});
	});
	$('input#add-contact').click(function(e){
		e.preventDefault();
		var button = $(this);
		var firstname = $('input#contact-firstname');
		var lastname = $('input#contact-lastname');
		var email = $('input#contact-email');
		var telephone = $('input#contact-telephone');
		var position = $('input#contact-position');
		var notes = $('input#contact-notes');
		var data = {
			contact: {
				firstname: firstname.val(),
				lastname: lastname.val(),
				email: email.val(),
				telephone: telephone.val(),
				position: position.val(),
				notes: notes.val()
			}
		};
		$.ajax({
			url: button.attr('data-url'),
			type: 'POST',
			dataType: 'json',
			data: data,
			beforeSend: function(){
				button.attr('disabled', 'disabled');
				$('#add-contact-spinner').show();
			},
			success: function(response){
				$('div#customer-contact-table-container').html(response.html);
				// Reset and hide form
				firstname.val('');
				lastname.val('');
				email.val('');
				telephone.val('');
				position.val('');
				$('div#new-contact').slideUp(600);
				$('a#show-new-contact').children('span').html('New Contact');
			},
			complete: function(){
				$('#add-contact-spinner').hide();
				button.removeAttr('disabled');
			}
		});
	});
	//show/hide notes of customer contact
	$('img.show-contact-notes').live('mouseenter', function(){
		if ($('div.contact-notes[data-contact-id="'+$(this).attr('data-contact-id')+'"]').is(':visible')){
			$(this).attr('src', '/media/images/icons/note_delete.png');
		} else {
			$(this).attr('src', '/media/images/icons/note_add.png');	
		}
	}).live('mouseleave', function(){
		$(this).attr('src', '/media/images/icons/note.png');
	}).live('click', function(){
	  $('#edit-pencil-text[data-contact-id="'+ $('.inline_editor_textarea_contacts').data('contact-id')+'"]').addClass('hidden');//hide pencil in case of glitch

		$('div.contact-notes[data-contact-id="'+$(this).attr('data-contact-id')+'"]').slideToggle('slow');
		$('td.contact-notes-container[data-contact-id="'+$(this).attr('data-contact-id')+'"]');
	});


	// Show/hide notes of customer addresses	
	$('img.show-address-notes').live('mouseenter', function(){
		if ($('div.address-notes[data-address-id="'+$(this).attr('data-address-id')+'"]').is(':visible')){
			$(this).attr('src', '/media/images/icons/note_delete.png');
		} else {
			$(this).attr('src', '/media/images/icons/note_add.png');	
		}
	}).live('mouseleave', function(){
		$(this).attr('src', '/media/images/icons/note.png');
	}).live('click', function(){
	  $('#edit-pencil-text[data-contact-id="'+ $('.inline_editor_textarea_contacts').data('contact-id')+'"]').addClass('hidden');//hide pencil in case of glitch
		$('div.address-notes[data-address-id="'+$(this).attr('data-address-id')+'"]').slideToggle('slow');
		$('td.address-notes-container[data-address-id="'+$(this).attr('data-address-id')+'"]');
	});
	
	$('div#customer-contact-table-container').on('click', 'a.customer-contact-delete', function(e){
		e.preventDefault();
		var button = $(this);
		$.ajax({
			url: button.data('url'),
			dataType: 'json',
			beforeSend: function(){
				button.hide();
				$('img.custom-contact-delete-spinner[data-contact-id="'+button.data('contact-id')+'"]').show();
			},
			success: function(response){
				$('div#customer-contact-table-container').html(response.html);
			},
			complete: function(){
				$('img.custom-contact-delete-spinner[data-contact-id="'+button.data('contact-id')+'"]').hide();
				button.show();
			}
		});
	});
	
	
	$('div#communication-date').datetimepicker({
		showButtonPanel: false
	}).datetimepicker('setDate', (new Date()));
	
	
	// CRM Customer Communications
	$('a#show-new-communication').click(function(e){
		e.preventDefault();
		var button = $(this);
		$('div#new-communication').slideToggle(600, function(){
			if ($('div#new-communication').is(':visible')) {
				button.children('span').html('Hide New Communication');
			} else {
				button.children('span').html('New Communication');
			}
		});
	});
	$('input#add-communication').click(function(e){
		e.preventDefault();
		var button = $(this);
		var type = $('select#communication-type');
		var title = $('input#communication-title');
		var text = $('textarea#communication-text');
		var date = $('div#communication-date');
		var callbackOn = $('input#communication-callback-on');
		var callbackAssignedTo = $('select#communication-callback-assigned-to');
		if ( !callbackOn.val() ) {
		 var data = {
			communication: {
				type: type.val(),
				title: title.val(),
				text: text.val(),
				date: Math.round(date.datetimepicker('getDate').getTime() /1000)
			}
		};
		}
		else 
		{
	
    var data = {
			communication: {
				type: type.val(),
				title: title.val(),
				text: text.val(),
				date: Math.round(date.datetimepicker('getDate').getTime() /1000),
				callback_on: Math.round(callbackOn.datepicker('getDate').getTime() /1000),
				callback_assigned_to: callbackAssignedTo.val()
			}
		};
		 }
		$.ajax({
			url: button.data('add-url'),
			type: 'POST',
			data: data,
			dataType: 'json',
			beforeSend: function(){
				button.attr('disabled', 'disabled');
				$('#add-communication-spinner').show();
			},
			success: function(response){
				$('div#customer-communications-table-container').html(response.html);
				// Reset and hide form
				type.val('');
				title.val('');
				text.val('');
				title.val('');
				date.datetimepicker('setDate', (new Date()));
				$('div#new-communication').slideUp(600);
				$('a#show-new-communication').children('span').html('New Communication');
				callbackOn.val('');
				callbackAssignedTo.val($('input#default-callback-user').val());
			},
			complete: function(){
				$('#add-communication-spinner').hide();
				button.removeAttr('disabled');
			}
		});
	});
	$('div#customer-communications-table-container').on('click', 'a.customer-communication-delete', function(e){
		e.preventDefault();
		var button = $(this);
		$.ajax({
			url: button.data('url'),
			dataType: 'json',
			beforeSend: function(){
				button.hide();
				$('img#delete-communication-spinner[data-communication-id="'+button.data('communication-id')+'"]').show();
			},
			success: function(response){
				$('div#customer-communications-table-container').html(response.html);
			},
			complete: function(){
				$('img#delete-communication-spinner[data-communication-id="'+button.data('communication-id')+'"]').hide();
				button.show();
			}
		});
	});
	$('a.callback-complete').live('click', function(e){
  	e.preventDefault();
  	if (confirm('Are you sure you wich to mark this callback as completed?')){
    	var link = $(this);
    	var communicationId = $(this).data('communication-id');
    	var spinner = $('img.callback_completed_spinner[data-communication-id="'+communicationId+'"]');
    	var icon = $('img.callback_completed_icon[data-communication-id="'+communicationId+'"]');
    	var details = $('span.callback_details[data-communication-id="'+communicationId+'"]');
    	$.ajax({
      	url: link.attr('href'),
      	type: 'get',
      	beforeSend: function(){
        	icon.hide();
        	spinner.show();
      	},
      	error: function(){
        	spinner.hide();
        	icon.show();
      	},
      	success: function(){
        	spinner.hide();
        	details.css('text-decoration', 'line-through');
      	}
    	});
    }
	});
	// CRM Customer Addresses
	$('a#show-new-address').click(function(e){
		e.preventDefault();
		var button = $(this);
		$('div#new-address').slideToggle(600, function(){
			if ($('div#new-address').is(':visible')) {
				button.children('span').html('Hide New Address');
			} else {
				button.children('span').html('New Address');
			}
		});
	});
	$('input#add-address').click(function(e){
		e.preventDefault();
		var button = $(this);
		var line1 = $('input#address-line-1');
		var line2 = $('input#address-line-2');
		var line3 = $('input#address-line-3');
		var town = $('input#address-town');
		var county = $('input#address-county');
		var postcode = $('input#address-postcode');
		var country = $('select#address-country');
		var telephone = $('input#address-telephone');
		var name = $('input#address-name');
		var notes = $('input#address-notes');
		var module = button.attr('data-module');
		var data = {
			address: {
				line_1: line1.val(),
				line_2: line2.val(),
				line_3: line3.val(),
				town: town.val(),
				county: county.val(),
				postcode: postcode.val(),
				country: country.val(),
				telephone: telephone.val(),
				name: name.val(),
				notes: notes.val()
			},
			template: module,
		};
		$.ajax({
			url: button.attr('data-url'),
			type: 'POST',
			dataType: 'json',
			data: data,
			beforeSend: function(){
				button.attr('disabled', 'disabled');
				$('#add-address-spinner').show();
			},
			success: function(response){
				$('div#customer-address-table-container').html(response.html);
				$('div#sales-order-address-table-container').html(response.html);
				$('div#sales-order-address-table-container').find('input[type="radio"]:first').attr('checked', 'checked');
				// Reset and hide form
				line1.val('');
				line2.val('');
				line3.val('');
				town.val('');
				county.val('');
				postcode.val('');
				country.val('');
				telephone.val('');
				name.val('');
				notes.val('');
				$('div#new-address').slideUp(600);
				$('a#show-new-address').children('span').html('New Address');
			},
			complete: function(){
				$('#add-address-spinner').hide();
				button.removeAttr('disabled');
			}
		});
	});
	$('div#customer-address-table-container').on('click', 'a.customer-address-delete', function(e){
		e.preventDefault();
		var button = $(this);
		$.ajax({
			url: button.data('url'),
			dataType: 'json',
			beforeSend: function(){
				button.hide();
				$('img.custom-address-delete-spinner[data-address-id="'+button.data('address-id')+'"]').show();
			},
			success: function(response){
				$('div#customer-address-table-container').html(response.html);
			},
			complete: function(){
				$('img.custom-address-delete-spinner[data-address-id="'+button.data('address-id')+'"]').hide();
				button.show();
			}
		});
	});
	
	// Deal with hiding/showing sku tiered prices
	$('a.show-sku-tiered-prices').live('mouseenter', function(){
		if ($('div.sku-tiered-price-container[data-sku-id="'+$(this).attr('data-sku-id')+'"]').is(':visible')){
			$(this).children('img').attr('src', '/media/images/icons/magifier_zoom_out.png');
		} else {
			$(this).children('img').attr('src', '/media/images/icons/magnifier_zoom_in.png');	
		}
	}).live('mouseleave', function(){
		$(this).children('img').attr('src', '/media/images/icons/magnifier.png');
	}).live('click', function(e){
		e.preventDefault();
		$('div.sku-tiered-price-container[data-sku-id="'+$(this).attr('data-sku-id')+'"]').slideToggle('slow');
	});
	
	// Commercial Sales Orders
	$('select#new-sales-order-item').change(function(){
		var select = $(this);
		$.ajax({
			url: select.val(),
			dataType: 'json',
			beforeSend: function(){
				$('img#new-sales-order-item-spinner').show();
			},
			success: function(response){
				$('table#sales-order-items tbody').append(response.html);
				restripeRows('sales-order-items');
				$('span#sales-order-subtotal').html(calculateSalesOrderSubtotal());
				$('span#sales-order-total').html(calculateSalesOrderTotal());
				$('span#sales-order-vat').html(calculateSalesOrderVat());
				// Disable the sku in the select to avoid duplicate rows
				$('select#new-sales-order-item').find('option[data-sku-id="'+response.sku.id+'"]').attr('disabled', 'disabled');
				$('.datepicker').datepicker({
      		constrainInput: true,
      		dateFormat: 'dd/mm/yy',
      		firstDay: 1,
      		numberOfMonths: 1,
      		selectOtherMonths: true
      	})
			},
			complete: function(){
				select.val('');
				$('img#new-sales-order-item-spinner').hide();
			}
		});
	});
	// Recalculates a sales order row total when quantity or unit price changes
	$('table#sales-order-items').on('keyup', 'input.sales-order-item-unit-price, input.sales-order-item-quantity', function(e){
		var row = $('tr[data-sku-id="'+$(this).parents('tr').data('sku-id')+'"]');
		var unitPrice = row.find('input.sales-order-item-unit-price').val();
		var quantity = row.find('input.sales-order-item-quantity').val();
		var total = number_format(unitPrice*quantity, 2);
		if (total !== NaN){
			row.find('span.sales-order-item-total').html(total);
			$('span#sales-order-subtotal').html(calculateSalesOrderSubtotal());
			$('span#sales-order-total').html(calculateSalesOrderTotal());
			$('span#sales-order-vat').html(calculateSalesOrderVat());
		}
	}).on('click', 'a.sales-order-line-delete', function(e){
		var skuId = $(this).parents('tr').data('sku-id');
		$('tr[data-sku-id="'+skuId+'"]').remove();
		restripeRows('sales-order-items');
		$('span#sales-order-subtotal').html(calculateSalesOrderSubtotal());
		$('span#sales-order-total').html(calculateSalesOrderTotal());
		$('span#sales-order-vat').html(calculateSalesOrderVat());
		$('select#new-sales-order-item').find('option[data-sku-id="'+skuId+'"]').removeAttr('disabled');
	});
	$('input#sales-order-delivery-charge').keyup(function(){
		$('span#sales-order-subtotal').html(calculateSalesOrderSubtotal());
		$('span#sales-order-total').html(calculateSalesOrderTotal());
		$('span#sales-order-vat').html(calculateSalesOrderVat());
	});
	function restripeRows(tableId){
		$('table#'+tableId+' tbody tr:even').removeClass('alternate');
		$('table#'+tableId+' tbody tr:odd').addClass('alternate');
	}
	function calculateSalesOrderSubtotal(){
		var total = 0.00;
		$('span.sales-order-item-total').each(function(){
			total += parseFloat($(this).html().replace(',', ''));
		});
		if ($('input#sales-order-delivery-charge').val() != ''){
			total += parseFloat($('input#sales-order-delivery-charge').val());	
		}
		return number_format(total, 2);
	}
	function calculateSalesOrderTotal(){
		var total = 0.00;
		$('span.sales-order-item-total').each(function(){
			var netTotal = $(this).html();
			var vatRate = $(this).parents('tr').find('input.sales-order-item-vat-rate').val(); 
			var grossTotal = parseFloat(netTotal) * ((parseFloat(vatRate) + 100) / 100);
			total += parseFloat(grossTotal.toString().replace(',', ''));
		});
		if ($('input#sales-order-delivery-charge').val() != ''){
			total += parseFloat($('input#sales-order-delivery-charge').val()) * ((parseFloat($('input#default-vat').val()) + 100) / 100);
		}
		return number_format(total, 2);
	}
	function calculateSalesOrderVat(){
		var total = parseFloat($('span#sales-order-total').html()) - parseFloat($('span#sales-order-subtotal').html());
		return number_format(total, 2);
	}
	// Asynchronous pagination for sales order addresses
	$('div#sales-order-address-table-container').on('click', '.pagination.asynchronous a', function(e){
		e.preventDefault();
		var pageNumber = $(this).data('page');
		var customerId = $('input#sales-order-customer-id').val();
		$.ajax({
			url: '/admin/sales_orders/new_sales_order_addresses',
			data: {addresses_page: pageNumber, customer: customerId},
			dataType: 'json',
			success: function(response){
				$('div#sales-order-address-table-container').html(response.html);
			}
		});
	});
	
	// View sales order page
	$('#complete-and-email').click(function(e){
		e.preventDefault();
		if (confirm('Are you sure that you want to mark this order as complete and send confirmation email to customer?')) {
			$.ajax({
				url: $(this).data('url'),
				type: 'GET',
				beforeSend: function(){
					$('#complete-and-email img').hide();
					$('#ajax-spinner').show();
				},
				success: function(response){
					if (response == 'ok')
					{
						$('#sales-order-status').val('complete');
						$('#complete-and-email').hide();
					}
				}
			});
		}
	});
	now = $.datepicker.formatDate('dd/mm/yy', new Date());
	$('#email-invoice').click(function(e){
		e.preventDefault();
		if (confirm('Are you sure that you want to email the invoice to the customer?')) {
			$.ajax({
				url: $(this).data('url'),
				type: 'GET',
				beforeSend: function(){
					$('#email-invoice img').hide();
					$('#ajax-spinner').show();
				},
				success: function(response){
					if (response == 'ok')
					{
					   var invoicedOn = ($('#sales-order-invoiced-on').val() + "empty");
					   console.log(invoicedOn);
						$('#sales-order-status').val('invoice_sent');
						if (invoicedOn === "empty"){
						$('#sales-order-invoiced-on').val(now);
						}
						$('#email-invoice').hide();
					}
				}
			});
		}
	});
	$('#add-sales-order-note').click(function(){
		var button = $(this);
		$.ajax({
			url: '/admin/sales_orders/add_note',
			type: 'POST',
			data: { sales_order:button.data('sales-order-id'), note: $('#new-note').val() },
			dataType: 'json',
			beforeSend: function(){
				$('#add-note-spinner').show();
				$('#add-note').attr('disabled', 'disabled');
			},
			success: function(response){
				var rowClass;
				if ($('.sales_order_note').first().hasClass('alternate')){
					 rowClass = '';
				}
				else {
					rowClass = 'alternate';
				}
				// Add note into list
				var newRow = $('<div>').addClass('sales_order_note hidden ' + rowClass); // Hidden to start so we can slide in :)
				var noteHeader = $('<p>').html('<img src="/images/icons/user_suit.png" alt="" class="inline-icon" /><strong>On ' + response.created + ' ' + response.user + ' said:</strong>').appendTo(newRow);
				var noteBody = $('<div>').html(response.text).appendTo(newRow);
				newRow.prependTo($('#sales-order-notes')).show('slide');
				$('#new-note').val('');
			},
			complete: function(){
				$('#add-note-spinner').hide();
				$('#add-note').removeAttr('disabled');
			}
		});
	});
	
	$('input#sales-order-invoiced-on').datepicker({
		dateFormat: 'dd/mm/yy'
	});
	
	//inline editor for communications

	$('.inline_editor_textarea').live('mouseenter', function(){
	var area = $(this);
	$('#edit-pencil-text[data-communication-id="'+area.data('communication-id')+'"]').removeClass('hidden');
		$('.inline_editor_textarea').live('mouseleave', function(){
	$('#edit-pencil-text[data-communication-id="'+area.data('communication-id')+'"]').addClass('hidden');
	});
	});

	$('.inline_editor_textarea').live('click', function(){
	 var original = $(this);
	 var container = $(this).parent();
	 
	 
	 var field = $('<textarea class="inplace_field">');
	 field.val($(this).html()); 
	 $(this).replaceWith(field).text();
	 
	 
	 var saveButton = $('<button>');
	 saveButton.html('Save');
	 container.append(saveButton);
	 //Perform Ajax on click
	 saveButton.click(function(e){
	 e.preventDefault();
	  var new_value = $('.inplace_field').val();
	 var updatedValues = { text: new_value }
	 $.ajax({
			url: original.data('communication-url'),
			type: 'POST',
			data: updatedValues,
			dataType: 'json',
			beforeSend: function(){
				original.text(new_value);
				field.replaceWith(original);
				original.hide();
				$('img#edit-communication-spinner[data-communication-id="'+original.data('communication-id')+'"]').show();
				saveButton.remove();
				cancelButton.remove();
			},
			success: function(response){
				
		  },
		  complete: function(){
  		  $('img#edit-communication-spinner[data-communication-id="'+original.data('communication-id')+'"]').hide();
  		  original.show();
			}
		});
	 });         	 
	
	 var cancelButton =  $('<button>');
	 cancelButton.html('Cancel');
	 container.append(cancelButton);
	 cancelButton.click(function(e){
	   e.preventDefault();
    field.replaceWith(original);
    saveButton.remove();
    cancelButton.remove();
   });
  });
  
	$('.inline_editor_input').live('mouseenter', function(){
	var area = $(this);
	$('#edit-pencil-title[data-communication-id="'+area.data('communication-id')+'"]').show();
		$('.inline_editor_input').live('mouseleave', function(){
	$('#edit-pencil-title[data-communication-id="'+area.data('communication-id')+'"]').hide();
	});
	});
	$('.inline_editor_input').live('click', function(){
	 var original = $(this);
	 var container = $(this).parent();
	 
	 
	 var field = $('<input class="inplace_form">');
	 field.val($(this).html()); 
	 $(this).replaceWith(field).text(); 
	 
	 
	 var saveButton = $('<button>');
	 saveButton.html('Save');
	 container.append(saveButton);
	
	 saveButton.click(function(e){
	 e.preventDefault();
	 var new_value = $('.inplace_form').val();
	 var updatedValues = { title: new_value }
	 $.ajax({
			url: original.data('communication-url'),
			type: 'POST',
			data: updatedValues,
			dataType: 'json',
			beforeSend: function(){
				original.text(new_value);
				field.replaceWith(original);
				original.hide();
				$('img#edit-communication-title-spinner[data-communication-id="'+original.data('communication-id')+'"]').show();
				saveButton.remove();
				cancelButton.remove();
			},
			success: function(response){
				
		  },
		  complete: function(){
  		  $('img#edit-communication-title-spinner[data-communication-id="'+original.data('communication-id')+'"]').hide();
  		  original.show();
			}		});
	 });
	 
            	 
	
	 var cancelButton =  $('<button>');
	 cancelButton.html('Cancel');
	 container.append(cancelButton);
	 cancelButton.click(function(e){
	   e.preventDefault();
    field.replaceWith(original);
    saveButton.remove();
    cancelButton.remove();
   });
  });

// inline editor for contacts

$('.inline_editor_textarea_contacts').live('mouseenter', function(){
	var area = $(this);
	$('#edit-pencil-text[data-contact-id="'+area.data('contact-id')+'"]').removeClass('hidden');
		$('.inline_editor_textarea').live('mouseleave', function(){
	$('#edit-pencil-text[data-contact-id="'+area.data('contact-id')+'"]').addClass('hidden');
	});
	});

	$('.inline_editor_textarea_contacts').live('click', function(){
	 var original = $(this);
	 var container = $(this).parent();
	 
	 
	 var field = $('<textarea class="inplace_field">');
	 field.val($(this).html()); 
	 $(this).replaceWith(field).text();
	 
	 
	 var saveButton = $('<button>');
	 saveButton.html('Save');
	 container.append(saveButton);
	 //Perform Ajax on click
	 saveButton.click(function(e){
	 e.preventDefault();
	  var new_value = $('.inplace_field').val();
	 var updatedValues = { notes: new_value }
	 $.ajax({
			url: original.data('url'),
			type: 'POST',
			data: updatedValues,
			dataType: 'json',
			beforeSend: function(){
				original.text(new_value);
				field.replaceWith(original);
				original.hide();
				$('img#edit-contact-spinner[data-contact-id="'+original.data('contact-id')+'"]').show();
				saveButton.remove();
				cancelButton.remove();
				$('#edit-pencil-text[data-contact-id="'+ $('.inline_editor_textarea_contacts').data('contact-id')+'"]').addClass('hidden');
			},
			success: function(response){
				
		  },
		  complete: function(){
  		  $('img#edit-contact-spinner[data-contact-id="'+original.data('contact-id')+'"]').hide();
  		  original.show();
			}
		});
	 });         	 
	
	 var cancelButton =  $('<button>');
	 cancelButton.html('Cancel');
	 container.append(cancelButton);
	 cancelButton.click(function(e){
	   e.preventDefault();
    field.replaceWith(original);
    saveButton.remove();
    cancelButton.remove();
   });
  });
  //firstname contacts
	$('.inline_editor_input_contacts_name').live('mouseenter', function(){
	var area = $(this);
	$('#edit-pencil-name[data-contact-id="'+area.data('contact-id')+'"]').show();
		$('.inline_editor_input_contacts_name').live('mouseleave', function(){
	$('#edit-pencil-name[data-contact-id="'+area.data('contact-id')+'"]').hide();
	});
	});
	$('.inline_editor_input_contacts_name').live('click', function(){
	 var original = $(this);
	 var container = $(this).parent();
	 
	 
	 var field = $('Firstname:<input class="inplace_form"></br>');
	 field.val($(this).html()); 
	 $(this).replaceWith(field).text(); 
	 
	 
	 var saveButton = $('<button>');
	 saveButton.html('Save');
	 container.append(saveButton);
	
	 saveButton.click(function(e){
	 e.preventDefault();
	 var new_value = $('.inplace_form').val();
	 var updatedValues = { firstname: new_value }
	 $.ajax({
			url: original.data('url'),
			type: 'POST',
			data: updatedValues,
			dataType: 'json',
			beforeSend: function(){
				original.text(new_value);
				field.replaceWith(original);
				original.hide();
				$('img#edit-contact-name-spinner[data-contact-id="'+original.data('contact-id')+'"]').show();
				saveButton.remove();
				cancelButton.remove();
			},
			success: function(response){
				
		  },
		  complete: function(){
  		  $('img#edit-contact-name-spinner[data-contact-id="'+original.data('contact-id')+'"]').hide();
  		  original.show();
			}		});
	 });
	 
            	 
	
	 var cancelButton =  $('<button>');
	 cancelButton.html('Cancel');
	 container.append(cancelButton);
	 cancelButton.click(function(e){
	   e.preventDefault();
    field.replaceWith(original);
    saveButton.remove();
    cancelButton.remove();
   });
  });

  
  //email contacts
  $('.inline_editor_input_contacts_email').live('mouseenter', function(){
	var area = $(this);
	$('#edit-pencil-email[data-contact-id="'+area.data('contact-id')+'"]').show();
		$('.inline_editor_input_contacts_email').live('mouseleave', function(){
	$('#edit-pencil-email[data-contact-id="'+area.data('contact-id')+'"]').hide();
	});
	});
	$('.inline_editor_input_contacts_email').live('click', function(){
	 var original = $(this);
	 var container = $(this).parent();
	 
	 
	 var field = $('<input class="inplace_form">');
	 field.val($(this).html()); 
	 $(this).replaceWith(field).text(); 
	 
	 
	 var saveButton = $('<button>');
	 saveButton.html('Save');
	 container.append(saveButton);
	
	 saveButton.click(function(e){
	 e.preventDefault();
	 var new_value = $('.inplace_form').val();
	 var updatedValues = { email: new_value }
	 $.ajax({
			url: original.data('url'),
			type: 'POST',
			data: updatedValues,
			dataType: 'json',
			beforeSend: function(){
				original.text(new_value);
				field.replaceWith(original);
				original.hide();
				$('img#edit-contact-email-spinner[data-contact-id="'+original.data('contact-id')+'"]').show();
				saveButton.remove();
				cancelButton.remove();
			},
			success: function(response){
				
		  },
		  complete: function(){
  		  $('img#edit-contact-email-spinner[data-contact-id="'+original.data('contact-id')+'"]').hide();
  		  original.show();
			}		});
	 });
	 
            	 
	
	 var cancelButton =  $('<button>');
	 cancelButton.html('Cancel');
	 container.append(cancelButton);
	 cancelButton.click(function(e){
	   e.preventDefault();
    field.replaceWith(original);
    saveButton.remove();
    cancelButton.remove();
   });
  });

//telephone contacts
  $('.inline_editor_input_contacts_tel').live('mouseenter', function(){
	var area = $(this);
	$('#edit-pencil-tel[data-contact-id="'+area.data('contact-id')+'"]').show();
		$('.inline_editor_input_contacts_tel').live('mouseleave', function(){
	$('#edit-pencil-tel[data-contact-id="'+area.data('contact-id')+'"]').hide();
	});
	});
	$('.inline_editor_input_contacts_tel').live('click', function(){
	 var original = $(this);
	 var container = $(this).parent();
	 
	 
	 var field = $('<input class="inplace_form">');
	 field.val($(this).html()); 
	 $(this).replaceWith(field).text(); 
	 
	 
	 var saveButton = $('<button>');
	 saveButton.html('Save');
	 container.append(saveButton);
	
	 saveButton.click(function(e){
	 e.preventDefault();
	 var new_value = $('.inplace_form').val();
	 var updatedValues = { telephone: new_value }
	 $.ajax({
			url: original.data('url'),
			type: 'POST',
			data: updatedValues,
			dataType: 'json',
			beforeSend: function(){
				original.text(new_value);
				field.replaceWith(original);
				original.hide();
				$('img#edit-contact-tel-spinner[data-contact-id="'+original.data('contact-id')+'"]').show();
				saveButton.remove();
				cancelButton.remove();
			},
			success: function(response){
				
		  },
		  complete: function(){
  		  $('img#edit-contact-tel-spinner[data-contact-id="'+original.data('contact-id')+'"]').hide();
  		  original.show();
			}		});
	 });
	 
            	 
	
	 var cancelButton =  $('<button>');
	 cancelButton.html('Cancel');
	 container.append(cancelButton);
	 cancelButton.click(function(e){
	   e.preventDefault();
    field.replaceWith(original);
    saveButton.remove();
    cancelButton.remove();
   });
  });

//position contacts
  $('.inline_editor_input_contacts_position').live('mouseenter', function(){
	var area = $(this);
	$('#edit-pencil-position[data-contact-id="'+area.data('contact-id')+'"]').show();
		$('.inline_editor_input_contacts_position').live('mouseleave', function(){
	$('#edit-pencil-position[data-contact-id="'+area.data('contact-id')+'"]').hide();
	});
	});
	$('.inline_editor_input_contacts_position').live('click', function(){
	 var original = $(this);
	 var container = $(this).parent();
	 
	 
	 var field = $('<input class="inplace_form">');
	 field.val($(this).html()); 
	 $(this).replaceWith(field).text(); 
	 
	 
	 var saveButton = $('<button>');
	 saveButton.html('Save');
	 container.append(saveButton);
	
	 saveButton.click(function(e){
	 e.preventDefault();
	 var new_value = $('.inplace_form').val();
	 var updatedValues = { position: new_value }
	 $.ajax({
			url: original.data('url'),
			type: 'POST',
			data: updatedValues,
			dataType: 'json',
			beforeSend: function(){
				original.text(new_value);
				field.replaceWith(original);
				original.hide();
				$('img#edit-contact-position-spinner[data-contact-id="'+original.data('contact-id')+'"]').show();
				saveButton.remove();
				cancelButton.remove();
			},
			success: function(response){
				
		  },
		  complete: function(){
  		  $('img#edit-contact-position-spinner[data-contact-id="'+original.data('contact-id')+'"]').hide();
  		  original.show();
			}		});
	 });
	 
            	 
	
	 var cancelButton =  $('<button>');
	 cancelButton.html('Cancel');
	 container.append(cancelButton);
	 cancelButton.click(function(e){
	   e.preventDefault();
    field.replaceWith(original);
    saveButton.remove();
    cancelButton.remove();
   });
  });

// inline editor for address

$('.inline_editor_textarea_address').live('mouseenter', function(){
	var area = $(this);
	$('#edit-pencil-text[data-address-id="'+area.data('address-id')+'"]').removeClass('hidden');
		$('.inline_editor_textarea_address').live('mouseleave', function(){
	$('#edit-pencil-text[data-address-id="'+area.data('address-id')+'"]').addClass('hidden');
	});
	});

	$('.inline_editor_textarea_address').live('click', function(){
	 var original = $(this);
	 var container = $(this).parent();
	 
	 
	 var field = $('<textarea class="inplace_field">');
	 field.val($(this).html()); 
	 $(this).replaceWith(field).text();
	 
	 
	 var saveButton = $('<button>');
	 saveButton.html('Save');
	 container.append(saveButton);
	 //Perform Ajax on click
	 saveButton.click(function(e){
	 e.preventDefault();
	  var new_value = $('.inplace_field').val();
	 var updatedValues = { notes: new_value }
	 $.ajax({
			url: original.data('address-url'),
			type: 'POST',
			data: updatedValues,
			dataType: 'json',
			beforeSend: function(){
				original.text(new_value);
				field.replaceWith(original);
				original.hide();
				$('img#edit-address-spinner[data-address-id="'+original.data('address-id')+'"]').show();
				saveButton.remove();
				cancelButton.remove();
				$('#edit-pencil-text[data-contact-id="'+ $('.inline_editor_textarea_contacts').data('contact-id')+'"]').addClass('hidden');
			},
			success: function(response){
				
		  },
		  complete: function(){
  		  $('img#edit-address-spinner[data-address-id="'+original.data('address-id')+'"]').hide();
  		  original.show();
			}
		});
	 });         	 
	
	 var cancelButton =  $('<button>');
	 cancelButton.html('Cancel');
	 container.append(cancelButton);
	 cancelButton.click(function(e){
	   e.preventDefault();
    field.replaceWith(original);
    saveButton.remove();
    cancelButton.remove();
   });
  });
  //name address
	$('.inline_editor_input_address_name').live('mouseenter', function(){
	var area = $(this);
	$('#edit-pencil-name[data-address-id="'+area.data('address-id')+'"]').show();
		$('.inline_editor_input_address_name').live('mouseleave', function(){
	$('#edit-pencil-name[data-address-id="'+area.data('address-id')+'"]').hide();
	});
	});
	$('.inline_editor_input_address_name').live('click', function(){
	 var original = $(this);
	 var container = $(this).parent();
	 
	 
	 var field = $('<input class="inplace_form">');
	 field.val($(this).html()); 
	 $(this).replaceWith(field).text(); 
	 
	 
	 var saveButton = $('<button>');
	 saveButton.html('Save');
	 container.append(saveButton);
	
	 saveButton.click(function(e){
	 e.preventDefault();
	 var new_value = $('.inplace_form').val();
	 var updatedValues = { name: new_value }
	 $.ajax({
			url: original.data('url'),
			type: 'POST',
			data: updatedValues,
			dataType: 'json',
			beforeSend: function(){
				original.text(new_value);
				field.replaceWith(original);
				original.hide();
				$('img#edit-address-name-spinner[data-address-id="'+original.data('address-id')+'"]').show();
				saveButton.remove();
				cancelButton.remove();
			},
			success: function(response){
				
		  },
		  complete: function(){
  		  $('img#edit-address-name-spinner[data-address-id="'+original.data('address-id')+'"]').hide();
  		  original.show();
			}		});
	 });
	 
            	 
	
	 var cancelButton =  $('<button>');
	 cancelButton.html('Cancel');
	 container.append(cancelButton);
	 cancelButton.click(function(e){
	   e.preventDefault();
    field.replaceWith(original);
    saveButton.remove();
    cancelButton.remove();
   });
  });
  
  //address address
  $('.inline_editor_input_address_address').live('mouseenter', function(){
	var area = $(this);
	$('#edit-pencil-address[data-address-id="'+area.data('address-id')+'"]').show();
		$('.inline_editor_input_address_address').live('mouseleave', function(){
	$('#edit-pencil-address[data-address-id="'+area.data('address-id')+'"]').hide();
	});
	});
	$('.inline_editor_input_address_address').live('click', function(){
	 var original = $(this);
	 var container = $(this).parent();
	 
	 
	 var field = $('<input class="inplace_form">');
	 field.val($(this).html()); 
	 $(this).replaceWith(field).text(); 
	 
	 
	 var saveButton = $('<button>');
	 saveButton.html('Save');
	 container.append(saveButton);
	
	 saveButton.click(function(e){
	 e.preventDefault();
	 var new_value = $('.inplace_form').val();
	 var updatedValues = { address: new_value }
	 $.ajax({
			url: original.data('url'),
			type: 'POST',
			data: updatedValues,
			dataType: 'json',
			beforeSend: function(){
				original.text(new_value);
				field.replaceWith(original);
				original.hide();
				$('img#edit-address-address-spinner[data-address-id="'+original.data('address-id')+'"]').show();
				saveButton.remove();
				cancelButton.remove();
			},
			success: function(response){
				
		  },
		  complete: function(){
  		  $('img#edit-address-address-spinner[data-address-id="'+original.data('address-id')+'"]').hide();
  		  original.show();
			}		});
	 });
	 
            	 
	
	 var cancelButton =  $('<button>');
	 cancelButton.html('Cancel');
	 container.append(cancelButton);
	 cancelButton.click(function(e){
	   e.preventDefault();
    field.replaceWith(original);
    saveButton.remove();
    cancelButton.remove();
   });
  });

//telephone address
  $('.inline_editor_input_address_tel').live('mouseenter', function(){
	var area = $(this);
	$('#edit-pencil-tel[data-address-id="'+area.data('address-id')+'"]').show();
		$('.inline_editor_input_address_tel').live('mouseleave', function(){
	$('#edit-pencil-tel[data-address-id="'+area.data('address-id')+'"]').hide();
	});
	});
	$('.inline_editor_input_address_tel').live('click', function(){
	 var original = $(this);
	 var container = $(this).parent();
	 
	 
	 var field = $('<input class="inplace_form">');
	 field.val($(this).html()); 
	 $(this).replaceWith(field).text(); 
	 
	 
	 var saveButton = $('<button>');
	 saveButton.html('Save');
	 container.append(saveButton);
	
	 saveButton.click(function(e){
	 e.preventDefault();
	 var new_value = $('.inplace_form').val();
	 var updatedValues = { telephone: new_value }
	 $.ajax({
			url: original.data('url'),
			type: 'POST',
			data: updatedValues,
			dataType: 'json',
			beforeSend: function(){
				original.text(new_value);
				field.replaceWith(original);
				original.hide();
				$('img#edit-address-tel-spinner[data-address-id="'+original.data('address-id')+'"]').show();
				saveButton.remove();
				cancelButton.remove();
			},
			success: function(response){
				
		  },
		  complete: function(){
  		  $('img#edit-address-tel-spinner[data-address-id="'+original.data('address-id')+'"]').hide();
  		  original.show();
			}		});
	 });
	 
            	 
	
	 var cancelButton =  $('<button>');
	 cancelButton.html('Cancel');
	 container.append(cancelButton);
	 cancelButton.click(function(e){
	   e.preventDefault();
    field.replaceWith(original);
    saveButton.remove();
    cancelButton.remove();
   });
   
});

//IS immediate payment required?

$('#immediate-payment').live('click', function(){

  if ($('#immediate-payment').prop('checked')) {
    $("#terms").attr('disabled', 'disabled');
   }
    
  else {
    $("#terms").removeAttr('disabled', 'disabled');
   }
});

function number_format (number, decimals, dec_point, thousands_sep) {
    // Formats a number with grouped thousands  
    // 
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/number_format
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +     bugfix by: Michael White (http://getsprink.com)
    // +     bugfix by: Benjamin Lupton
    // +     bugfix by: Allan Jensen (http://www.winternet.no)
    // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +     bugfix by: Howard Yeend
    // +    revised by: Luke Smith (http://lucassmith.name)
    // +     bugfix by: Diogo Resende
    // +     bugfix by: Rival
    // +      input by: Kheang Hok Chin (http://www.distantia.ca/)
    // +   improved by: davook
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Jay Klehr
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Amir Habibi (http://www.residence-mixte.com/)
    // +     bugfix by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // +      input by: Amirouche
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // *     example 1: number_format(1234.56);
    // *     returns 1: '1,235'
    // *     example 2: number_format(1234.56, 2, ',', ' ');
    // *     returns 2: '1 234,56'
    // *     example 3: number_format(1234.5678, 2, '.', '');
    // *     returns 3: '1234.57'
    // *     example 4: number_format(67, 2, ',', '.');
    // *     returns 4: '67,00'
    // *     example 5: number_format(1000);
    // *     returns 5: '1,000'
    // *     example 6: number_format(67.311, 2);
    // *     returns 6: '67.31'
    // *     example 7: number_format(1000.55, 1);
    // *     returns 7: '1,000.6'
    // *     example 8: number_format(67000, 5, ',', '.');
    // *     returns 8: '67.000,00000'
    // *     example 9: number_format(0.9, 0);
    // *     returns 9: '1'
    // *    example 10: number_format('1.20', 2);
    // *    returns 10: '1.20'
    // *    example 11: number_format('1.20', 4);
    // *    returns 11: '1.2000'
    // *    example 12: number_format('1.2000', 3);
    // *    returns 12: '1.200'
    // *    example 13: number_format('1 000,50', 2, '.', ' ');
    // *    returns 13: '100 050.00'
    // Strip all characters but numerical ones.
    number = (number + '').replace(/[^0-9+\-Ee.]/g, '');
    var n = !isFinite(+number) ? 0 : +number,
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

jQuery.fn.slugify = function(obj) {
  jQuery(this).data('obj', jQuery(obj));
  jQuery(this).keyup(function() {
      var obj = jQuery(this).data('obj');
      var slug = jQuery(this).val().replace(/\s+/g,'-').replace(/[^a-zA-Z0-9\-]/g,'').toLowerCase();
      obj.val(slug);
  });
}

});