require.config({
  paths: {
    bootstrap: 'libs/bootstrap/bootstrap',
    jqueryui: 'libs/jquery-ui-1.8.15.min'
  }
});

require(['app'], function(App){
  App.initialize();
});





// This doesn't belong here
$(function(){
	// Deal with user dismissing EU Cookie Law disclaimer.
	$('a#caffeine-accept-cookies').click(function(e){
		e.preventDefault();
		var button = $(this);
		$.ajax({
			url: button.attr('href'),
			success: function(response){
				$('div#caffeine-cookies-container').slideUp();
			}
		});
	});
});