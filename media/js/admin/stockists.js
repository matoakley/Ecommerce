jQuery.fn.slugify = function(obj) {
    jQuery(this).data('obj', jQuery(obj));
    jQuery(this).keyup(function() {
        var obj = jQuery(this).data('obj');
        var slug = '';
        var i = 0;
        $('.slugify').each(function(){
        	if (i++ > 0 && $(this).val() != ''){
        		slug += '-';
        	}
        	slug += jQuery(this).val().replace(/\s+/g,'-').replace(/[^a-zA-Z0-9\-]/g,'').toLowerCase();
        });
        obj.val(slug);
    });
}