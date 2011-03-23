$(document).ready(function(){
	$('#login').hide();
	$('#header').hide();
	$('.liste_item').hide();
	
		$('#header').slideDown('200', function() {
		        // Animation complete
				$('#login').fadeIn('1600');
		      });
	// Details/Summary-type funksjonalitet (ingen browsere støtter disse skikkelig enda)
	// pluss litt animasjon for å gjøre det mer åpenbart hva som skjer
	$('.liste_header').click(function()
		{
			$(this).next(".liste_item").slideToggle(200);
		});

});