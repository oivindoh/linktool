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
			#$salt = time(); TODO BRUK md5(time());
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
			
			# Opprett database om dette er valgt. Tar ibruk sql.sql-filen som blir distribuert sammen med verktøyet
			# denne filen er igjen opprettet ved hjelp av mysqlworkbench pour le mac
			if($_POST['createdb'] == 1){
				$distributed_sql_file = file_get_contents('sql.sql');
				# den originale modellen har med databasenavn oivindoh--
				$sql_file = str_replace('oivindoh', $_POST['db_name'], $distributed_sql_file);
				# filen er bare en samling spørringer delt opp av ;
				# sett hver spørring inn i en array siden mysq_query() bare utfører én i slengen
				$sql_array = explode(';', $sql_file);
				mysql_connect($_POST['db_host'], $_POST['db_user'],$_POST['db_pass']);
				$results = '';
				$i = 0;
				# utfør alle spørringene
				foreach($sql_array as $query){
					$result .= $i . ': ' . mysql_query($query);
					$i++;
					
				}
				# lukk tilkoblingen
				mysql_close();
			}
			
			
			echo '<h1>Innstillinger lagret</h1><p>Du kan nå <a href="index.php">ta systemet i bruk</a></p>';
		break;
		default:
		
		if(isset($c)){
			$hoform = $c->db_host;
			$usform = $c->db_user;
			$paform = $c->db_pass;
			$dbform = $c->db_name;
			if($c->debug == 1){
				$deform = ' checked';
			}	
		}
		
		
		# Første steg, skaff databaseopplysningene
		$md5time = substr(md5(time()), -10);
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
					<label><input type="text" name="db_host" placeholder="db.stud.aitel.hist.no" value="$hoform" required /> Server</label><br />
					<label><input type="text" name="db_user" placeholder="root" value="$usform" required /> Brukernavn</label><br />
					<label><input type="text" name="db_pass" placeholder="passord" value="$paform" required /> Passord</label><br />
					<label><input type="text" name="db_name" placeholder="linktool" value="$dbform" required /> Databasenavn</label><br />
				</fieldset>
				<fieldset><legend>Andre innstillinger</legend>
					<label><input type="text" name="adminpass" value="$md5time"/> Administratorpassord for å overskrive konfigurasjon</label><br />
					<label><input type="checkbox" name="debug" value="1" $deform /> Feilsøkingsmodus</label><br />
					<label><input type="checkbox" name="createdb" value="1" /> Opprett database <small>(overskriver eventuell eksisterende database ved samme navn)</small></label><br />
				</fieldset>
				<fieldset>
					<button type="submit">Lagre</button>
				</fieldset>
			</form>
HTML;
		echo $setup_form;
		break;
	}
}
?>
