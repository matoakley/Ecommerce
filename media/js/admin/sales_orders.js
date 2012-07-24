$(function(){

	$('#bulk-actions').change(function(){
		
		if ($(this).val() == 'ship_and_email'){
			
			$.fancybox({
				'href': '/admin/sales_orders/bulk_ship_and_email',
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
	
	$('#bulk-ship-and-email').live('click', function(){
	
		var salesOrders = {};
		var i = 1;
		
		$('.row-selector:checked').each(function(){
			
			salesOrders[i] = $(this).val();
			i++;
		});
	
		var data = {
			sales_orders: salesOrders
		};
	
		$.ajax({
		
			url: '/admin/sales_orders/bulk_ship_and_email',
			type: 'POST',
			data: data,
			success: function(){
				window.location.reload();
			}
		});

	});
	
});