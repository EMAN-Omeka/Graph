window.jQuery = window.$ = jQuery;

jQuery(document).ready(function() {
	$.ajaxSetup({
		username : "OmEkA" ,
		password : "nm493ie698vg"
	});  
  webroot = $('#phpWebRoot').val();
  collection = $('#collectionId').val();
  language = $('#item-language').val();
  
	$("#item-language, #item-sort").change(function () {
		$("#item-type").change();
	});
	$("#item-type").change(function() {
		$('#eman-ajax-results').empty();
		$.getJSON(webroot + "/emancolajax?lang=" + language + "&col=" + collection + "&q=" + $(this).val() + "&sort=" + $('#item-sort').val(),  function( data ) {
		  var items = [];
		  $.each( data, function( key, val ) {
		    items.push( "<div id='itemLists'><h3>" + val.titre + "</h3><span class='thumb'><img src='" + webroot + '/files/square_thumbnails/' + val.file + "'/></span><p>" + val.text + "</p></div>" );
		  });
		  items.unshift("<div id='itemsCount'>" + items.length  + " notice(s) correspondent à vos critères.</div>");		  
		  $( "#eman-ajax-results" ).html(items.join(""));
		});
	});
	
  // Chargement initial
  $("select#item-type").val('Tous').trigger('change');
	
});