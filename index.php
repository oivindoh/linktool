<?php
error_reporting(E_ALL ^ E_NOTICE);

if (!file_exists('conf.php')){ $_GET['setup'] = 1; }
if (!isset($_GET['setup'])){
	# inkluder nødvendige filer, ikke fortsett uten samtlige
	require_once("conf.php");
	require_once("include/login.php");
	require_once("include/url.php");
	require_once("include/subject.php");

	# opprett objekter og message-variabel
	$c = new Config();
	$d = new mysqli($c->db_host, $c->db_user, $c->db_pass, $c->db_name);
	$l = new LoginHandler(&$c);
	$u = new URLHandler(&$c, &$l, &$s, &$d);
	$s = new SubjectHandler(&$c, &$l);
	
	
	# cookielogin (endelig, usynlig BOM character ødela...)
	# husk sed -i '1 s/^\xef\xbb\xbf//' *.txt og tail -c +4 filmedBOM > filutenBOM
	if (isset($_COOKIE['userName']) && isset($_COOKIE['passHash'])){
		$l->login($_COOKIE['userName'], md5($_COOKIE['passHash']));
	}
	
	#
	#	Håndter innsendte form-handlinger ($_POST[faction])
	#
	if(isset($_POST['faction'])){
	switch($_POST['faction']){
		case "accountupdate":
			$accountupdate_result = $l->updateAccount($_POST['email'], $_POST['name'], $_POST['chgpass'], $_POST['pass1'], $_POST['pass2']) ;

			switch($accountupdate_result){
				case 0:
					$message = '<h1>Oppdatering av konto</h1><p>Du ser ut til å ville endre passord, men du oppga ikke identisk passord to ganger; dette er 
				nødvendig for å unngå å lagre passord med skrivefeil. <a href="?action=account">Prøv gjerne igjen</a>!</p>';
					break;
				case 1:
					$message = '<h1>Success!</h1><p>Brukerdata endret, passord er ikke endret.</p>';
					break;
				case 2:
					$message = '<h1>Endring utført</h1><p>Passord og eventuell annen brukerdata endret, og du er nå utlogget.</p>';
					$l->logout();
					break;
				case 66:
					$message = '<h1>Passord kreves oppgitt for endringer</h1>';
					break;
				case 99:
					$message = '<h1>Epic breakage</h1>';
					break;
			}
			break;
		# Bruker har sendt inn data for registrering av ny bruker
		case "register":
			$reg_result = $l->register($_POST['username'], $_POST['password']);
			if ($reg_result === 1){
				# om registreringen gikk bra, kan vi liksågodt logge inn
				$l->login($_POST['username'], $_POST['password']);
			}
			break;
		# Bruker vil logge inn
		case "login":
			$result = $l->login($_POST['username'], $_POST['password']);
			break;
		# Logg ut
		case "logout":
			$logout_status = $l->logout();
			$message = ($logout_status == 1) ? "Utlogget" : "Ikke utlogget likevel zomg!";
			break;
		# Legge til nytt fag
		case "addsubject":
			$posted_term = $_POST['term_semester'] . '/' . $_POST['term_year'];
			$addsubject = $s->addSubject($_POST['code'], $posted_term, $_POST['name']);
			$message = '<h1>Legg til fag</h1><span class="error">Fag finnes allerede i databasen';
			if($addsubject == 1){ $message = '<h1>Legg til fag</h1><span class="success">Fag lagt til</span>'; }
			break;
		# Bruker har oppgitt en fag-ID via skjema og vil legge til ny blogg
		case "selectnewblog":
			$_GET['id'] = $_POST['id'];
			$message = "<h1>Legg til blogg</h1><p>Vennligst oppgi detaljer om din blogg</p>";
			break;
		# Legg til blogg
		case "addblog":
			if($_POST['manual_id']){ $_POST['id'] = $_POST['manual_id']; }
			$addblog_reply = $u->insertURL($_POST['url'], $_POST['rss'], $_POST['author'], $_POST['desc'], $_POST['freq'], $_POST['id']);
			$addblog_array = explode("|", $addblog_reply);
			$addblog = $addblog_array[0];
			$subjref = $addblog_array[1];
			switch($addblog){
				case "0":
					$message = "<h1>Feil</h1><p>Noe gikk forferdelig galt.</p>"; break;
				case "1":
					$message = "<h1>Feil</h1><p>Ugyldig URL</p>"; break;
				case "2":
					$message = "<h1>Feil</h1><p>Denne bloggen er allerede registrert</p>"; break;
				case "3":
					$message = "<h1>Feil</h1><p>Angitt fag eksisterer ikke</p>"; break;
				default:
					$message = '<h1>Blogg lagt til</h1><p>Takk for ditt bidrag!<br />Ditt referansenummer er ' . $addblog . 
					' ( <a href="?action=editblog&id=' . $addblog . '">link</a> )</p><p>Ta vare på dette nummeret/denne linken i tilfelle du får behov for å redigere linken på et senere tidspunkt.</p><p>Ta gjerne en titt på <a href="?action=listblogs&id='. $subjref . '">andre blogger registrert på faget</a>!';
			}
			break;
		# Rediger eksisterende blogg
		case "editblog":
			$editblog = $u->editURL($_POST['url'], $_POST['rss'], $_POST['author'], $_POST['desc'], $_POST['freq'], $_POST['id']);
			if ($editblog == 1){ $message = "<h1>Blogg oppdatert</h1></p>Takk for bidraget!</p>"; } else { $message = "<h1>Blogg ikke oppdatert</h1><p>Dette er garantert din egen feil.</p>"; }
			break;
		}
	}
}
?>
<!DOCTYPE HTML>
<html lang="no">
	<head>
		<meta charset="utf-8" />
		<title>linktool 0.2</title>
		<!-- Øivind Hoel 2011 -->
		<link href="css3.css" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="diverse/jQuery.js"></script>   
		<script type="text/javascript" src="diverse/linktool.js"></script>
		<script type="text/javascript" src="diverse/easing.js"></script>                 
		                 
	</head>

	<body>
		<header id="header">
			<nav id="navigasjon">            
				<?php
				if(!isset($_GET['setup'])){
				#
				# innloggingsskjema, registreringsskjema og innlogget bruker-info
				#
				if ($l->getUserName() == "" && !isset($_GET['newuser'])){
					# ikke innlogget; vis skjema
					echo $l->showLoginForm();
					}
					elseif ($l->getUserName() == "" && $_GET['newuser'] == 1){
						# bruker vil registrere seg; vis skjema
						echo $l->showRegistrationForm();
					}
					else {
						# bruker er innlogget; vis relevant info
						echo $l->showInfo();
					}
				}
				?>
				<ul>
					<?php if(!isset($_GET['setup'])) { ?>
					<li><a href="index.php">hjem</a></li>
					<li><a href="?action=newsubject">nytt fag</a></li>
					<li><a href="?action=newblog">ny blogg</a></li>
					<li><a href="?action=editblog">rediger blogg</a></li>
					<?php
					/* TODO: fiks (css, markup) eller fjern
					if ($l->getUserName() != ""){
						$subjects = $s->getUserSubjects($l->getUserName());
						if ($subjects != false){
							# Har fag
							$subjectmenu = '<li style="width: 250px; display: inline-block;">Fagliste:<ul id="subject_menu">';
							while($row = mysql_fetch_array($subjects, MYSQL_ASSOC)){
								$subjectmenu .= '<li>' . $row['name'] . '</li>';
							}
							$subjectmenu .= '</ul></li>';
							echo $subjectmenu;
						}
					}*/
					?>
					
					<?php } ?>
				</ul>
			</nav>
		</header>		
		<section id="innhold">
			<header id="innhold_header">
					
					<?php if(isset($message)){ echo $message; }?>
					
			</header>
			<article id="innhold_tekst">

<?php
if (!isset($_GET['setup'])){
#
#	Håndter link-handlinger ($_GET[action])
#

switch ($_GET['action']){
	case "account":
		echo $l->showInfo(true);
	break;

	case "newblog":
		$u->showURLForm($_GET['id']);
	break;

	case "newsubject":
		if ($l->getUserName() == ""){
			echo '<h1>Nytt fag</h1><p>Du må være innlogget for å kunne legge til fag</p>';
			break;
		}
		$s->showAddSubjectForm();
	break;
	
	case "deletesubject":
		if (!isset($_GET['confirm'])){
			echo $s->removeSubject($_GET['id']);
			break;
			}
		$deletesubjectreturn = $s->removeSubject($_GET['id'], $_GET['confirm']);
		switch($deletesubjectreturn){
			case 0:
				echo '<h1>Feil</h1><p>Det er et under at denne siden i det hele tatt vises.</p>';
				break;
			case 1:
				echo '<h1>Feil</h1><p>Kunne ikke slette rad fra database.</p>';
				break;
			case 2:
				echo '<h1>Fag slettet</h1><p>Blogger tilknyttet faget ble også slettet.</p>';
				break;
		}
	break;
	case "listblogs":
		if (isset($_GET['id'])){
			$s->listBlogs($_GET['id']);
		}
	break;
	
	case "editblog":
		$u->showURLEditForm($_GET['id']);
	break;

	case "deleteblog":
		# litt overflødig siden vi egentlig vil la hvem som helst slette
		# så lenge de har referanse
		if ($l->getUserName() == ""){
			echo '<h1>Logg inn</h1><p>Denne funksjonen kan bare brukes av innloggede brukere</p>';
			break;
		}
		$blog_deleted = $u->deleteURL($_GET['id']);
		if($blog_deleted == 1){
			echo '<h1>;-(</h1><p>Bloggen ble fjernet</p>';
		} 
		else{
			echo '<h1>Feil</h1><p>Bloggen ble ikke fjernet; mest sannsynlig fordi du ikke har eierskap til denne bloggen.</p>';
		}
	break;
	default:
		if ($l->getUserName() != ""){
			echo '<h1>Dine fag</h1>';
			$user_subject_list = $s->listByUser($l->getUserName());
			if(!$user_subject_list){
				echo '<div id="subjects_overview"><p>Du har enda ikke registrert et fag; <a href="?action=newsubject">hva med å gjøre dette nå?</a></p>';
				break;
			}
			echo $user_subject_list;
			break;
		}
		echo "<h1>Bloggverktøy</h1><p>Her skjedde det lite... Hva med å registrere deg/logge deg inn?</p>
		<p><small>Denne siden gjør utstrakt bruk av (x)html5-markup og css3, så det anbefales virkelig 
		å oppdatere til siste skrik innen browsere for maks tilfredsstillelse. Vi har selvfølgelig 
		fallbacks på plass for eldre nettlesere (som firefox 3.6), så dette er absolutt ikke 
		et krav.</small></p>";
	break;
}
}
# Hvis Setupscriptet skal kjøre
else {
	include('setup.php');
}
?>
			</article>
			<footer id="innhold_footer">
				<pre><?php if ($c->debug == 1) { print_r($_COOKIE); print_r($_GET); print_r($_POST); }?></pre>
			</footer>
		</section>
		
		<div id="feitfot">&nbsp;</div>
		<footer>
			<div id="feitfot_innhold">
				<a href="http://validator.w3.org/check/referer">html5</a> <a href="http://jigsaw.w3.org/css-validator/check/referer?profile=css3">css3</a> - ses best (identisk) i Firefox 4+, Safari 5+ og Chrome 9+, <small>men fungerer nå også utmerket i ff3.6 og opera11 med et par små hacks</small>
			</div>
			</footer>
	</body>
</html>