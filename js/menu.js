var base_url = document.mybaseurl;
var viajeComenzado = 0;

function updateTripMenu(){
  $.get(base_url+"/getlayout/tripmenu",function (menudata){
    $("#usertrip-menu").html(menudata);
  });
}

function updateRoadtrip(){
    $.getJSON(base_url+"/getinfo/activetrip", function (dataviaje){
      // Veo si tengo un viaje nuevo
      if(viajeComenzado!=dataviaje){
          // Si, guardo el valor, actualizo el menu
          viajeComenzado = dataviaje;
          updateTripMenu();
      }
    });
}

function updateMenu (){        
      updateRoadtrip();
      setTimeout(updateMenu,5000);    
}

$(document).ready(function() {  
  updateMenu();
 
});

(jQuery);