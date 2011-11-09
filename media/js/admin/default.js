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
		toolbar:
				[
		            ['Source', 'Format'],
		            ['Bold', 'Italic', '-', 'NumberedList', 'BulletedList', '-', 'Link', '-', 'RemoveFormat']
		        ],
		width: "810"
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
	
});

jQuery.fn.slugify = function(obj) {
    jQuery(this).data('obj', jQuery(obj));
    jQuery(this).keyup(function() {
        var obj = jQuery(this).data('obj');
        var slug = jQuery(this).val().replace(/\s+/g,'-').replace(/[^a-zA-Z0-9\-]/g,'').toLowerCase();
        obj.val(slug);
    });
}