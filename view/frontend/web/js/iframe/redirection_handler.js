require(['jquery', 'jquery/ui'], function($){
  $('#c2p').load(function() { 
    try {
      window.top.location.href = document.getElementById('c2p').contentWindow.location.href;
    } catch(err) {
      return ;
    }
  });
});