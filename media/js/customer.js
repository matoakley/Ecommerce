define([], function(){
  
  /**
  *  Handle asynchronous user sign in.
  */
  var authenticate = function(email, password, successCallback, errorCallback){
    $.post('/customers/login', { login: { email: email, password: password }}, function(response){
      if (response.user){
        successCallback(response.user);
      } else {
        errorCallback(response.errors);
      }
    }, 'json');
  }
  
  return {
    authenticate: authenticate
  };
  
});