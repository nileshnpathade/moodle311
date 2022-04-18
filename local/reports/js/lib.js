function get_search(data, datatable){
	var result = [];
	$(datatable).find("tfoot").find("select").each(function() {
	   result.push([$(this).attr("name"), $(this).find("option:selected").text() ]);
	});
	return JSON.stringify(result);
}


function get_custom_search(custom_search_fields){
  var result = [];
  var searchval = $(custom_search_fields).val().split(',');
  for (const search_field of searchval) {
      result.push([search_field, $('#'+search_field).val()]);
  }
  return JSON.stringify(result);
}

function timeConverter(UNIX_timestamp){
  var a = new Date(UNIX_timestamp * 1000);
  var months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
  var year = a.getFullYear();
  var month = months[a.getMonth()];
  var date = a.getDate();
  var hour = a.getHours();
  var min = a.getMinutes();
  var sec = a.getSeconds();
  var time = date + " " + month + " " + year + " " + hour + ":" + min + ":" + sec ;
  return time;
}