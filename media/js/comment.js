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
    });
  };

  return {
    asyncSubmit: asyncSubmit
  };
  
});