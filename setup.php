<?php
	if(file_exists('./conf.php')){
		die("Allerede konfigurert");
	}
	
	$step = $_POST['step'];
	
	if (!$step){
		$setup_form = <<<HTML
			<form>
				<fieldset><legend>Databaseoppsett</legend>
					<legend><input type="text" name="db_host" /> Server</legend>
					<legend><input type="text" name="db_user" /> Brukernavn</legend>
					<legend><input type="text" name="db_pass" /> Passord</legend>
					<legend><input type="text" name="db_name" /> Databasenavn</legend>
				¨</fieldset>
	}


?>