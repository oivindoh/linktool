$(document).ready(function(){
	$('#login').hide();
	$('.liste_item').hide();
	$('li.admin').hide();
	$('#subjects_overview').hide();
	$('#form_description h1').hide();
	$('#subject_menu').hide();
	$('#subjects_overview').fadeIn('slow');
	$('#form_description h1').fadeIn('slow');
	$('#login').slideToggle(500, 'easeOutBounce');
	
	// Details/Summary-type funksjonalitet (ingen browsere støtter disse skikkelig enda)
	// pluss litt animasjon for å gjøre det mer åpenbart hva som skjer
	$('.liste_header').click(function(){
		$(this).next(".liste_item").slideToggle(300, 'easeOutExpo')
	});

	$('#header').mouseenter(function(){
		$('#login').slideDown(200);
	});
	
	$('#header').mouseleave(function(){
		$('#login').slideUp(200);
	});

	// Vis/Skjul linker til slett/endre (og rss) i eget panel.
	// dette for å gjøre det vanskeligere å trykke på f.eks slett ved et uhell
	$('#linklist > ul').mouseenter(function(){
		$(this).children('.admin').slideDown(200);
	});
	
	$('#linklist > ul').mouseleave(function(){
		$(this).children('.admin').slideUp(200);
	});
	
	
	jQuery.easing.def = "easeInQuad";
});