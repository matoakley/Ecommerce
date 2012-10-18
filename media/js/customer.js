define([], function(){
  
  var authenticate = function(email, password){
    $.post('/customers/ajax_login', { email: email, password: password: password });
  }
  
  return {
    authenticate: authenticate
  };
  
});