$(document).ready(function(){
	$('#login').hide();
	//$('#header').hide();
	$('.liste_item').hide();
	$('.linklist_delete_edit_links').hide();
	$('li.admin').hide();
	
	/*	$('#header').slideDown('200', function() {
		        // Animation complete
				$('#login').fadeIn('1600');
		      });*/
	$('#login').fadeIn('1600');
	// Details/Summary-type funksjonalitet (ingen browsere støtter disse skikkelig enda)
	// pluss litt animasjon for å gjøre det mer åpenbart hva som skjer

	$('.liste_header').click(function()
		{
			$(this).next(".liste_item").slideToggle(200);
		});
/*
	$('.liste_header').click(function()
		{
			$(this).next(".liste_item").fadeToggle(600);
		});
		*/

// Vis/Skjul linker til slett/endre (og rss) i eget panel.
// dette for å gjøre det vanskeligere å trykke på f.eks slett ved et uhell
	  $('#linklist > ul').mouseenter(
	    function()
		{
			$(this).children('.admin').slideDown(200);
		});
	  $('#linklist > ul').mouseleave(
	    function()
		{
			$(this).children('.admin').slideUp(200);
		});

});