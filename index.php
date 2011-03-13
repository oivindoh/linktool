<?php
if (!file_exists('conf.php')){ $_GET['setup'] = 1; }
if (!$_GET['setup']){
	# inkluder nødvendige filer, ikke fortsett uten samtlige
	require_once("conf.php");
	require_once("include/escape.php");
	require_once("include/login.php");
	require_once("include/url.php");
	require_once("include/subject.php");

	# opprett objekter og message-variabel
	$c = new Config();
	$l = new LoginHandler(&$c);
	$u = new URLHandler(&$c, &$l);
	$s = new SubjectHandler(&$c, &$l);
	
	# cookielogin (endelig, usynlig BOM character ødela...)
	# husk sed -i '1 s/^\xef\xbb\xbf//' *.txt og tail -c +4 filmedBOM > filutenBOM
	if ($_COOKIE['userName'] && $_COOKIE['passHash']){
		$l->login($_COOKIE['userName'], md5($_COOKIE['passHash']));
	}
	
	#
	#	Håndter innsendte form-handlinger ($_POST[faction])
	#
	switch($_POST['faction']){
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
			break;
		# Legge til nytt fag
		case "addsubject":
			$addsubject = $s->addSubject($_POST['code'], $_POST['term'], $_POST['name']);
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
			$addblog = $u->insertURL($_POST['url'], $_POST['rss'], $_POST['author'], $_POST['desc'], $_POST['freq'], $_POST['id']);
			switch($addblog){
				case "0":
					$message = "<h1>Feil</h1><p>Noe gikk forferdelig galt.</p>"; break;
				case "1":
					$message = "<h1>Feil</h1><p>Ugyldig URL</p>"; break;
				case "2":
					$message = "<h1>Feil</h1><p>Denne bloggen er allerede registrert</p>"; break;
				case "3":
					$message = "<h1>Feil</h1><p>Angitt fag eksisterer ikke"; break;
				default:
					$message = '<h1>Blogg lagt til</h1><p>Takk for ditt bidrag!<br />Ditt referansenummer er ' . $addblog . 
					' ( <a href="?editblog&id=' . $addblog . '">link</a> )</p><p>Ta vare på dette nummeret/denne linken i tilfelle du får behov for å redigere linken på et senere tidspunkt.';
			}
			break;
		# Rediger eksisterende blogg
		case "editblog":
			$editblog = $u->editURL($_POST['url'], $_POST['rss'], $_POST['author'], $_POST['desc'], $_POST['freq'], $_POST['id']);
			if ($editblog == 1){ $message = "<h1>Blogg oppdatert</h1></p>Takk for bidraget!</p>"; } else { $message = "<h1>Blogg ikke oppdatert</h1><p>Dette er garantert din egen feil.</p>"; }
			break;
	}
}
?>
<!DOCTYPE HTML>
<html lang="no">
	<head>
		<meta charset="utf-8" />
		<title>linktool 0.1</title>
		<!-- Øivind Hoel 2011 -->
		<link href="css3.css" rel="stylesheet" type="text/css" />
	</head>

	<body>
		<header id="header">
			<nav id="navigasjon">            
				<?php
				if(!$_GET['setup']){
				#
				# innloggingsskjema, registreringsskjema og innlogget bruker-info
				#
				if ($l->getUserName() == "" && $_GET['newuser'] != 1){
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
					<?php if(!$_GET['setup']) { ?>
					<li><a href="index.php">hjem</a></li>
					<li><a href="?action=newsubject">nytt fag</a></li>
					<li><a href="?action=newblog">ny blogg</a></li>
					<li><a href="?action=editblog">rediger blogg</a></li>
					<?php } ?>
				</ul>
			</nav>
		</header>
		
		<section id="innhold">
			<header id="innhold_header">
					
					<?php echo $message; ?>
					
			</header>
			<article id="innhold_tekst">

<?php
if (!$_GET['setup']){
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
		echo "I IZ ERAZIN UR HARDDRIVE MON OLOL ASDASD (NYI)";
		# method: delete all in subjectlinks
		#	then delete all entries in links
		#	theeeeen delete the actual subject
		#	also: request confirmation sinz diz is impossabal 2 andu
		# 			;-)
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
			echo $s->listByUser($l->getUserName());
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
		<script type="text/javascript" src="diverse/details.js"></script>
		
	</body>
</html>