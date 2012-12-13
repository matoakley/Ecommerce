define([], function(){

  /**
  *  Handle the asynchronous posting of a review to be 
  *  saved to the database.
  */  
  var asyncSubmit = function(obj, successCallback, errorCallback){
    $.post('/reviews/add', obj, function(response){
      if (response.review){
        successCallback(response.review);
      } else {
        errorCallback(response.errors);
      }
    }, 'json');
  };
  
  /**
  *  Helper function to manage the status change of a 
  *  rating icon as the user hovers over and clicks
  *
  *  <span class="rating-selector">
  *    <img src="..." alt="..." data-index="1" />
  *    <img src="..." alt="..." data-index="2" />
  *    <img src="..." alt="..." data-index="3" />
  *    <img src="..." alt="..." data-index="4" />
  *    <img src="..." alt="..." data-index="5" />
  *  </span>
  *
  */
  var rolloverRating = function(container, activeImage, disabledImage, formField){
    container = $(container);
    formField = $(formField);
    var rating = formField.val();
    container.on('mouseenter', 'img', function(e){
      var selectedIndex = $(this).data('index');
      container.find('img').each(function(){
        if (parseInt($(this).data('index')) <= selectedIndex){
          $(this).attr('src', activeImage);
        } else {
          $(this).attr('src', disabledImage);
        }
      });
    }).on('click', 'img', function(e){
      rating = $(this).data('index');
      formField.val(rating);
    });
    container.mouseleave(function(){
      // Loop through and reset stars to currently selected rating
      container.find('img').each(function(){
        if (parseInt($(this).data('index')) > rating){
          $(this).attr('src', disabledImage);
        } else {
          $(this).attr('src', activeImage);
        }
      });
    });
  };
    
  return {
    asyncSubmit: asyncSubmit,
    rolloverRating: rolloverRating
  };
  
});