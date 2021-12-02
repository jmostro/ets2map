function updateDriversNumbersTable(sel, user){
	var uid = sel.options[sel.selectedIndex].value;
	$.ajax({
		type: "POST",
		url: "/admin/driversnumbers/edit/",
		data: {user_id:user, number:uid},
		cache: false
	}).done(function() {
		window.location='/admin/driversnumbers';
	});
}