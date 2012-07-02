$(function(){

	$('#nav ul').superfish();
	
	$('.datepicker').datepicker({
		constrainInput: true,
		dateFormat: 'dd/mm/yy',
		firstDay: 1,
		numberOfMonths: 2,
		selectOtherMonths: true
	});
	
	$('textarea.description').ckeditor({

		// CKFinder integration		
		filebrowserBrowseUrl : '/ckfinder/ckfinder.html',
    filebrowserImageBrowseUrl : '/ckfinder/ckfinder.html?Type=Images',
    filebrowserUploadUrl : '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Files',
    filebrowserImageUploadUrl : '/ckfinder/core/connector/php/connector.php?command=QuickUpload&type=Images'
	});

	$('.slugify').keyup(function(){
		$(this).slugify($('.slug'));
	});
	
	$('#check-all').click(function(){
		
		var checked = $(this).attr('checked');
		
		$('.row-selector').each(function(){
			
			$(this).attr('checked', checked).trigger('change');
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
		var data = {
			contact: {
				firstname: firstname.val(),
				lastname: lastname.val(),
				email: email.val(),
				telephone: telephone.val(),
				position: position.val()
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
		/* var sendToCustomer = $('input#communication-send-to-customer'); */
		var data = {
			communication: {
				type: type.val(),
				title: title.val(),
				text: text.val(),
				date: Math.round(date.datetimepicker('getDate').getTime() /1000),
/* 				send_to_customer: sendToCustomer.is(':checked') */
			}
		};
		$.ajax({
			url: button.attr('data-url'),
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
			},
			complete: function(){
				$('#add-communication-spinner').hide();
				button.removeAttr('disabled');
			}
		});
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
						$('#sales-order-status').val('invoice_sent');
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