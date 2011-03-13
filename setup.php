<?php
	if(file_exists('conf.php')){
		include('conf.php');
		$c = new Config();
		if(!$_POST['masterpass']){
			$passform = <<<HTML
			<form method="post" action="?setup=1">
				<h1>Allerede konfigurert</h1>
				<p>Har du gyldig administratorpassord, kan du overskrive innstillingene:</p>
				<legend><input type="password" name="masterpass" /> Passord</legend>
				<input type="hidden" name="setup" value="0" /><br />
				<button type="submit">Videre</button>
			</form>
HTML;
			echo $passform;
		}
	}
	
	if(!file_exists('conf.php') || (isset($_POST['masterpass']) && !empty($_POST['masterpass']))){
		if($_POST['masterpass'] != ""){
			if (md5($_POST['masterpass'] . $c->salt) != $c->masterpass){
				# skriv lockfelt til config, kanskje?
				# send mail?
				die("Feil passord.");
			}
		}
	
	$setup = $_POST['setup'];

	switch($setup){
		
		case 1:
			#$salt = time();
			$salt = 1299387344;
			$masterpass = md5($_POST['adminpass'] . $salt);
			if ($_POST['debug'] != 1){ $debug = 0; } else { $debug = 1; }
			$config = <<<PHP
<?php
class Config{
# Databaseoppsett
	public \$db_host = "___DB_HOST___";
	public \$db_user = "___DB_USER___";
	public \$db_pass = "___DB_PASS___";
	public \$db_name = "___DB_NAME___";

	# Anti-rainbow-salt
	public \$salt = ___DB_SALT___;

	# Debug mode
	public \$debug = ___DEBUG___;
	
	# Adminpassord
	public \$masterpass = "___MASTER___";
	}
?>
PHP;
			$config = str_replace("___DB_HOST___", $_POST['db_host'], $config);
			$config = str_replace("___DB_USER___", $_POST['db_user'], $config);
			$config = str_replace("___DB_PASS___", $_POST['db_pass'], $config);
			$config = str_replace("___DB_NAME___", $_POST['db_name'], $config);
			$config = str_replace("___DB_SALT___", $salt, $config);
			$config = str_replace("___DEBUG___", $debug, $config);
			$config = str_replace("___MASTER___", $masterpass, $config);
			
			#echo $config;
			$writeconfig = file_put_contents('conf.php', $config);
			echo '<h1>Innstillinger lagret</h1><p>Du kan nå <a href="index.php">ta systemet i bruk</a></p>';
		break;
		default:
		# Første steg, skaff databaseopplysningene
		$md5time = md5(time());
		if(isset($_POST['masterpass'])){ $followthrough = '<input type="hidden" name="masterpass" value="' . $_POST['masterpass'] . '" />'; }
		$setup_form = <<<HTML
		<h1>Oppsett av linktool</h1>
		<p>Dette verktøyet gjør bruk av en mysql-database, og du må derfor oppgi den informasjonen verktøyet trenger for å kjøre
			sin kode.</p><p>Data du skriver inn her vil lagres i filen conf.php, og all informasjon unntatt administratorpassord til selve 
			verktøyet vil lagres ukryptert. Det anbefaler at du sørger for at denne filen ikke er lesbar for andre brukere.</p>
			<form method="post" action="?setup=1">
				<fieldset><legend>Databaseoppsett</legend>
					<input type="hidden" name="setup" value="1" />
					$followthrough
					<legend><input type="text" name="db_host" placeholder="db.stud.aitel.hist.no" required /> Server</legend>
					<legend><input type="text" name="db_user" placeholder="root" required /> Brukernavn</legend>
					<legend><input type="text" name="db_pass" placeholder="passord" required /> Passord</legend>
					<legend><input type="text" name="db_name" placeholder="linktool" required /> Databasenavn</legend>
				</fieldset>
				<fieldset><legend>Andre innstillinger</legend>
					<legend><input type="text" name="adminpass" value="$md5time"/> Administratorpassord for å overskrive konfigurasjon</legend>
					<legend><input type="checkbox" name="debug" value="1"/> Feilsøkingsmodus</legend>
				</fieldset>
				<button type="submit">Lagre</button>
			</form>
HTML;
		echo $setup_form;
		break;
	}
}
	
?>