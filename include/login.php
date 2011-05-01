<?php
/*
	Håndterer innlogging for lærere
	
	*/
	
class LoginHandler {
	private $username = "";
	private $passhash = "";
	private $c;
	
	public $name = "";
	
	# Vi trenger konfigurasjonen for en del av arbeidet vårt,
	# dette sendes gjennom gjennom constructor
	public function LoginHandler($configuration){
		$this->c = $configuration;
	}
	
	public function loggedIn(){
		if ($this->username != ""){
			return true;
		}
		return false;
	}
	
	#
	# 	login(): Innlogging, verifisering av bruker
	#		parameter: username, password
	#		return: 1 (logged in) | 2 (wrong passord) | 0 (user not found)
	#
	public function login($u, $p, $change = false){
		$u = $this->esc(trim($u));
		$p = $this->esc(trim($p));
		
		# passord fra cookie er 32 lang (md5)
		if (strlen($p) != 32){
			$cookie = 0;
			$p_cookie = $this->cryptPass($p, true);
			$p = $this->cryptPass($p);
		}
		# passord kommer fra cookie. gjør stringen sikker
		else {
			$cookie = 1;
		}
		
		$SQL = sprintf("SELECT * FROM `users` WHERE email='%s'", $u);
		$result = $this->runSQL($SQL);
		$result = mysql_fetch_array($result, MYSQL_ASSOC);
		if ($result){
			# brukerdata funnet i db
			if ($p == $result['password']) {
				# Passord stemmer overens med det som er lagret,
				# sett infofelt
				$this->setPassHash($result['password']);
			#	$this->setUserName($result['email']);
				$this->setUserName($u);
				$this->name = $result['name'];
				
				# sett cookie, gyldig en time
				if ($cookie != 1){
					setcookie("userName", $this->username, time()+604800);
					setcookie("passHash", $p_cookie, time()+604800);
					}							
				return 1;
			}
			
			else { 
				# feil passord
				return 2;
			}	
		}
		# ingen brukerdata funnet i db
		return 0;
	}
	
	#
	# 	logout(): Utlogging av bruker
	#		parameter: 
	#		return: 1 (logged out, cannot fail)
	#
	public function logout(){
		# slett cookie ved å tømme innhold og sette expiry tilbake i tid
		setcookie("userName", "", 1);
		setcookie("passHash", "", 1);
		
		# tøm variabler, inkludert cookie slik at endringer trer ikraft umiddelbart
		$this->username = "";
		$this->passhash = "";
		$this->name = "";
		
		$_COOKIE['userName'] = "";
		$_COOKIE['passHash'] = "";
		
		return 1;
	}
	
	#
	# 	register(): Registrering av ny bruker
	#		parameter: username, password
	#		return: 1 (registered) | 0 (user already exists/general error)
	#
	public function register($u, $p){
		$p = $this->cryptPass($p);
		$u = $this->esc($u);
		
		# Sjekk om bruker allerede er registrert
		$SQL = sprintf("SELECT * FROM `users` WHERE email='%s'", $u);
		$result = $this->runSQL($SQL);
		$result = mysql_fetch_array($result, MYSQL_ASSOC);
		if(!$result){
			# bruker finnes ikke fra før, let's go!
			$SQL = sprintf("INSERT INTO users VALUES('%s','%s','Navn...')", $u, $p);
			$result = $this->runSQL($SQL);
			if ($result){
				return 1;
			}
		}
		return 0;
	}
	
	#
	# 	showLoginForm(): Vis innlogginsskjema
	#		parameter:
	#		return: string (html: loginform)
	#
	public function showLoginForm(){
		$loginform = <<<HTML
		<div id="login">
		<form method="post" action="?">
			<input type="hidden" name="faction" value="login"/>
			<input class="short" type="email" name="username" placeholder="epost@hist.no" required />
			<input class="short" type="password" name="password" placeholder="passord" required />
			<button type="submit">logg inn</button>
			<a href="?newuser=1"> Ny bruker?</a>
		</form>
		</div>
HTML;
		return $loginform;
	}
	
	#
	# 	showRegistrationForm(): Vis registreringsskjema
	#		parameter:
	#		return: string (html: registrationform)
	#
	public function showRegistrationForm(){
		$registerform = <<<HTML
		<div id="login">
			<h3>Registrer ny bruker</h3>
		<form method="post" action="?">
			<input type="hidden" name="faction" value="register"/>
			<input class="short" type="email" name="username" placeholder="epost@hist.no" required />
			<input class="short" type="password" name="password" placeholder="passord" required/>
			<button type="submit">Registrer</button>
		</form>
		<p>Oppgi gjerne en gyldig epostadresse, slik at du har<br /> mulighet til å
			nullstille passord og få tilsendt rapporter (NYI)</p>
		</div>
HTML;
		return $registerform;
	}
	
	public function updateAccount($user, $name, $chgpass, $pass1 = false, $pass2 = false){
		if(!$chgpass || $this->cryptPass($chgpass) != $this->getPassHash()){ return 66; }
		
		$user = $this->esc($user);
		$name = $this->esc($name);
		if ($pass1){
			$pass1 = $this->esc($pass1);
		}
		if ($pass2){
			$pass2 = $this->esc($pass2);
		}
		
		# hent nåværende bruker
		$curuser = $this->getUserName();
		if ($pass1 && $pass2){
			# Passord skal endres
			if ($pass1 === $pass2){
				echo "passordene: $pass1 \n $pass2";
				# passord stemmer overens
				# krypter passord
				$pass1 = $this->cryptPass($pass1);
				$SQL = sprintf("UPDATE users SET email='%s', password='%s', name='%s' WHERE email='$curuser'", $user, $pass1, $name);
				$result = $this->runSQL($SQL);

				if ($result){
					# logg inn på nytt
						$gone = $this->logout();
					#$this->login($user, $pass2);
					return 2;
				}
			}
			else {
				# ulikt passord, men to passord registrert
				return 0; 
			}
		}
		elseif($pass1){
			return 0;
		}
		# passord skal ikke endres
		
		$SQL = sprintf("UPDATE users SET email='%s', password='%s', name='%s' WHERE email='$curuser'", $user, $this->getPassHash(), $name);
		echo "$SQL\n";
		$result = $this->runSQL($SQL);
		if ($result){
			if ($curuser != $user){
				$this->logout();
			}
			return 1;
			}

		return 99;
	}
	
	
	#
	# 	showInfo(): Vis relevant info for innlogget bruker
	#		parameter: 
	#		return: string (html: info)
	#
	public function showInfo($more = false){
		if($more){
			$lname = $this->getUserFullName();
			$user = $this->getUserName();
			$info = <<<HTML
			<div id="form_description">
			<h1>Kontoinnstillinger (Meh.)</h1><p>Endre informasjon</p>
			</div>
			<form method="post" action="?">
				<fieldset><legend>Personalia</legend>
					<input type="hidden" name="faction" value="accountupdate"/>
					<label><input type="email" class="short" name="email" value="$user" required /> Epost</label><br />
					<label><input type="text" class="short" name="name" value="$lname" /> Navn</label>
				</fieldset>
				<fieldset><legend>Passord</legend>
					<label><input type="password" class="short" name="chgpass" /> (Må oppgis)</label>
				</fieldset>
				<h3 class="liste_header">Endre passord</h3>
					<div class="liste_item" style="padding: 10px 0 10px 100px">
					<label><input type="password" class="short" name="pass1" value="" /> Nytt passord</label><br />
					<label><input type="password" class="short" name="pass2" value="" /> Gjenta nytt passord</label>
					</div>
					<button type="submit">Endre</button><button type="reset">Tilbakestill</button>
				</fieldset>
			</form>
HTML;
			return $info;
		}
		$info = <<<HTML
		<div id="login">
		<form method="post" action="?">
			<span>innlogget som <a href="?action=account">$this->username</a></span>
			<input type="hidden" name="faction" value="logout" />
			<button type="submit">Logg ut</button>
		</form>
		</div>
HTML;
		return $info;
	}
	
	
	#
	# Getters/Setters for user/pass
	#
	public function setUserName($u){
		$this->username = $u;
	}
	public function setPassHash($p){
		$this->passhash = $p ;
	}
	public function getUserName(){
		return $this->username;
	}
	public function getPassHash(){
		return $this->passhash;
	}
	
	public function getUserFullName(){
		return $this->name;
	}
	
	#
	# 	cryptPass(): Innlogging, verifisering av bruker
	#		parameter: klartekst passord, typebryter
	#		return: string (kryptert passord)
	#
	public function cryptPass($p, $type = false){
		$salted = $p . $this->c->salt;
		if ($type) {
			# til lagring i cookie
			$encrypted = md5($salted);
		}
		else {
			# til lagring i database
			$encrypted = md5(md5($salted));
		}
		return $encrypted;
	}
	
	#
	# 	esc(): Utkommentering av SQL-tegn
	#		parameter: string
	#		return: utkommentert string
	#
	public function esc($string){
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string); 
	}
	
	#
	# 	runSQL(): Kjøring av SQL-spørringer
	#		parameter: SQL-spørring
	#		return: resultat av spørring som ressurs
	#
	public function runSQL($SQL){
		try{
			mysql_connect($this->c->db_host, $this->c->db_user,$this->c->db_pass);
			@mysql_select_db($this->c->db_name) or die("asd... ingen tilgang til database");
			$return = mysql_query($SQL);
			mysql_close();
		} catch(Exception $e){
			echo 'Feil: ' . $e->getMessage();
		}
		return $return;
	}
}	
?>
