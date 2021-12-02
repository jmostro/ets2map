var base_url = document.mybaseurl;
var updateInterval = 5000;

function updateTrips(){
	$("#loading-btn").show();
	$.get(base_url+"/company/triptable",function(data,status){		
		$("#opentrips-table").html(data);
		$("#loading-btn").hide();
	});
	setTimeout(updateTrips,updateInterval);
}

jQuery(function(){
	updateTrips();
});