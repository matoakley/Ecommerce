define([], function(){

  /**
  *  Handle the asynchronous posting of a comment to be 
  *  saved to the database.
  */  
  var asyncSubmit = function(obj, successCallback, errorCallback){
    $.post('/comments/add', obj, function(response){
      if (response.comment){
        successCallback(response.comment);
      } else {
        errorCallback(response.errors);
      }
     }, 'json');
  };
  
   var likeDislike = function(obj, successCallback, errorCallback){
    $.post('/comments/like_dislike', obj, function(response){
      if (response.comment){
        successCallback(window.location.reload(true));
      } else {
        errorCallback(response.errors);
      }
     }, 'json');
  };

   var showMore = function(obj, successCallback, errorCallback){
    $.post('/products/get_product_reviews', obj, function(response){
       if (response.reviews){
        successCallback(response.reviews);
      } else {
        errorCallback(response.errors);
      }
     }, 'json');
  };
  
  var submitABook = function(obj, successCallback, errorCallback){
    $.post('/products/submit_a_book', obj, function(response){
       if (response.id){
        successCallback(response.id);
      } else {
        errorCallback(response.error);
      }
     }, 'json');
  };
  
  return {
    asyncSubmit: asyncSubmit,
    likeDislike: likeDislike,
    showMore: showMore,
    submitABook: submitABook
  };
  
});