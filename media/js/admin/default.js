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
	
	var today = new Date();
	$('.datetimepicker').datetimepicker({
		hour: today.getHours(),
		minute: today.getMinutes(),
		showButtonPanel: false
	});
	
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
			beforeSend: function(){
				button.attr('disabled', 'disabled');
				$('#add-communication-spinner').show();
			},
			success: function(response){
				$('div#customer-communications-table-container').html(response);
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
		var town = $('input#address-town');
		var county = $('input#address-county');
		var postcode = $('input#address-postcode');
		var country = $('select#address-country');
		var telephone = $('input#address-telephone');
		var data = {
			address: {
				line_1: line1.val(),
				line_2: line2.val(),
				town: town.val(),
				county: county.val(),
				postcode: postcode.val(),
				country: country.val(),
				telephone: telephone.val()
			}
		};
		$.ajax({
			url: button.attr('data-url'),
			type: 'POST',
			data: data,
			beforeSend: function(){
				button.attr('disabled', 'disabled');
				$('#add-address-spinner').show();
			},
			success: function(response){
				$('div#customer-address-table-container').html(response);
				// Reset and hide form
				line1.val('');
				line2.val('');
				town.val('');
				county.val('');
				postcode.val('');
				country.val('');
				telephone.val('');
				$('div#new-address').slideUp(600);
				$('a#show-new-address').children('span').html('New Address');
			},
			complete: function(){
				$('#add-address-spinner').hide();
				button.removeAttr('disabled');
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
});

jQuery.fn.slugify = function(obj) {
    jQuery(this).data('obj', jQuery(obj));
    jQuery(this).keyup(function() {
        var obj = jQuery(this).data('obj');
        var slug = jQuery(this).val().replace(/\s+/g,'-').replace(/[^a-zA-Z0-9\-]/g,'').toLowerCase();
        obj.val(slug);
    });
}