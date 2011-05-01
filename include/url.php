<?php
class URLHandler{
	private $c;
	private $l;
	private $s;
	private $d;
	
	public function URLHandler(&$conf, &$login, &$sub, &$db){
		$this->c = &$conf;
		$this->l = &$login;
		$this->s = &$sub;
		$this->d = &$db;
	}
	
	public function generateKey($url, $author){
		$cleartextkey = $url . $author . $this->c->salt;
		return md5($cleartextkey);
	}
	
	public function insertURL($url, $rss, $author, $desc, $freq, $subj){
		$url = $this->esc($url);
		$vdump = filter_var($url, FILTER_VALIDATE_URL);
		# Sjekk om url faktisk er gyldig
		if ($vdump === false){
			return 1;
		}
		$rss = $this->esc($rss);
		$author = $this->esc($author);
		$desc = $this->esc($desc);
		$freq = $this->esc($freq);
		$subj = $this->esc($subj);
		
		# Generer unik ID til referansenøkkel
		$ref = $this->generateKey($url, $author);

		# Hent tittel fra faktisk side
		$title = $this->esc($this->fetchTitle($url));
		
		# Sjekk at link ikke eksisterer og ikke blir lagt til i ikke-eksisterende fag
		$SQL = sprintf("SELECT * FROM links WHERE ref='%s'", $ref);
		$result = $this->runSQL($SQL);
		$found_link = mysql_num_rows($result);
		$fl_assoc = mysql_fetch_assoc($result);
		$result = "";
		$SQL = sprintf("SELECT * FROM subjects WHERE subjects.unique='%s'", $subj);
		$result = $this->runSQL($SQL);
		$found_subj = mysql_num_rows($result);
		$fs_assoc = mysql_fetch_assoc($result);
		
		
		# TODO / work in progress. 
		/*
		$SQL = sprintf("SELECT * FROM subjectlinks WHERE links_ref='%s'", $ref);
		$result = $this->runSQL($SQL);
		$foundmatchinglink = mysql_num_rows($result);
		if($foundmatchinglink){
			$matchassoc = mysql_fetch_assoc($result);
			if($matchassoc['subjects_unique'] != $subj){
				# go for it
				# ... hva med duplicate ref i db? bruke et nummerfelt med autoincrement som key istedenfor?
			}
		}*/
				
		if($found_link == 0 || !$found_link){
			if($found_subj > 0 || $found_subj){
				# "noe gikk forferdelig galt-land", men også tegn på at vi kom et stykke :)
				$SQL = sprintf("INSERT INTO links VALUES('%s','%s','%s', '%s', '%s', '%s', 0, '%s')", $ref, $url, $rss, $author, $desc, $freq, $title);
				echo '1 ' . $SQL;
				$result = $this->runSQL($SQL);
				if($result){
					# lagt til; legg så inn i mange-mange-tabellen
					$SQL = sprintf("INSERT INTO subjectlinks VALUES('%s', '%s')", $subj, $ref);
					echo '2 ' . $SQL;
					$result = $this->runSQL($SQL);
					if($result){
						# alt ok
						# TODO: hook for sending av mail om ønskelig?
						return $ref .'|' . $subj;
					}
				}
			}
			else {
				return 3;
			}
		}
		else {
			return 2;
		}
		return 0;
		
		# returnvalues: 0 = it blew up on us, 2 = link finnes, 3 = fag finnes ikke, referansenummer (md5-hash) = alt ok
		# 1 = ugyldig url
	}

	public function editURL($url, $rss, $author, $desc, $freq, $ref){
		$url = $this->esc($url);
		$rss = $this->esc($rss);
		$author = $this->esc($author);
		$desc = $this->esc($desc);
		$freq = $this->esc($freq);
		$ref = $this->esc($ref);
		
		$SQL = sprintf("UPDATE links SET url='%s', rss='%s', author='%s', description='%s', frequency='%d' WHERE ref='$ref'", $url, $rss, $author, $desc, $freq, $ref);
		$result = $this->runSQL($SQL);
		if ($result){
			# Oppdatert
			# TODO: burde egentlig oppdatere referanse også?
			return 1;
		}
		return 0;
	}
	
	public function deleteURL($id){
		$id = $this->esc($id);
		
		# Sjekk om URL tilhører bruker (via fag)
		$SQL = sprintf("SELECT * FROM subjectlinks INNER JOIN subjects ON subjects.unique = subjectlinks.subjects_unique WHERE links_ref = '%s'", $id);
		$result = mysql_fetch_assoc($this->runSQL($SQL));
		if ($result['users_email'] == $this->l->getUserName()){
			# Bruker tilhører dette faget
			$SQL = sprintf("DELETE FROM `subjectlinks` WHERE `subjectlinks`.`links_ref` = '%s'", $id);
			$result_manymany = $this->runSQL($SQL);
			if ($result_manymany){
				# Bloggen er fjernet
				$SQL = sprintf("DELETE FROM `links` WHERE `ref` = '%s'", $id);
				$result_links = $this->runSQL($SQL);
				if ($result_links){
					# Bloggen er fjernet
					return 1;
				}
			}
		}
		return 0;
		
	}
	public function showURLEditForm($url_reference){
		# Skaff info
		$urlmatch = 'pattern="https?://.+" title="Må være en gyldig adresse som begynner med http:// eller https://"';
		if(!isset($url_reference)){
			echo '<div id="form_description"><h1>Rediger blogg</h1><p>Blogg må angis ved referanse. Har du denne, kan du skrive den inn her:</p></div>';
			$html =
<<<HTML
<form method="get">
	<fieldset><legend>Referanse</legend>
		<input type="hidden" name="action" value="editblog" />
		<input class="long" type="text" name="id" maxlength="32" pattern="^[0-9a-f]{32}$" title="Sørg for at du kopierer inn referansen eksakt" required />
		<button type="submit">Gå videre</button>
	</fieldset>
</form>
HTML;
			echo $html;
			return 0;
		}
		$url_reference = $this->esc($url_reference);
		
		$SQL = sprintf("SELECT * FROM links WHERE ref='%s'", $url_reference);
		$result = $this->runSQL($SQL);
		$selected_blog = mysql_fetch_assoc($result);
		if(!$selected_blog) { echo "<h1>Feil</h1><p>Angitt blogg eksisterer ikke</p>"; return 0; }
		$html = <<<HTML
		<div id="form_description">
			<h1>Rediger blogg</h1>
			<p>$selected_blog[url] ($selected_blog[author])</p>
		</div>
		<form action="?" method="post">
			<fieldset><legend>Adresser</legend>
				<input type="hidden" name="faction" value="editblog"/>
				<input type="hidden" name="id" value="$_GET[id]"/>
				<label><input class="long" type="text" name="url" value="$selected_blog[url]" $urlmatch required /> <strong>Adresse *</strong></label><br />
				<label><input class="long" type="text" name="rss" value="$selected_blog[rss]" $urlmatch /> RSS-feed</label><br />
			</fieldset>
			<fieldset><legend>Annen informasjon</legend>
				<label>
				<select class="short" name="freq">
HTML;
					$selected_blog['frequency'] == 0 ? $html .= '<option value="0" selected>Daglig</option>' : $html .= '<option value="0">Daglig</option>';
					$selected_blog['frequency'] == 1 ? $html .= '<option value="1" selected>Ukentlig</option>' : $html .= '<option value="1">Ukentlig</option>';
					$selected_blog['frequency'] == 2 ? $html .= '<option value="2" selected>Månedlig</option>' : $html .= '<option value="2">Månedlig</option>';
					$selected_blog['frequency'] == 3 ? $html .= '<option value="3" selected>Intent mønster</option>' : $html .= '<option value="3">Intet mønster</option>';

$html = $html . <<<HTML
				</select> Oppdateringsfrekvens</label><br />
				<label><input class="short" type="text" name="author" value="$selected_blog[author]" required /> <strong>Ditt navn *</strong></label><br />
				<label>Beskrivelse<br />
					<textarea type="text" name="desc" required>$selected_blog[description]</textarea>
				</label>
			</fieldset>
			<fieldset>
				<button type="submit">Lagre</button>
				<button type="reset">Fjern endringer</button>
			</fieldset>
		</form>
HTML;
		echo $html;
		return 1;
		# sjekk get-id mot ref-id? 				
	}
	
	public function showURLForm($id){
		$urlmatch = 'pattern="https?://.+"';
		$has_subjects = $this->s->listByUser($this->l->getUserName());
		$username = $this->l->getUserName();
		if (empty($id) || strlen($id) != 32){
			# Hvis ID ikke er oppgitt eller feil lengde og bruker ikke er innlogget, spør etter ID.
			if($username == "" || !$has_subjects){
			$infotext = (!$has_subjects && $username == "" ? '<p>Har du ikke en fagreferanse (lang remse bokstaver og tall), kan du sjekke it\'sLearning eller kontakte din faglærer for å få oppgitt denne. Uten fagreferanse kan du dessverre ikke legge til din blogg.</p>'
										: '<p>Inntil du har <a href="?action=newsubject">registrert et fag på deg selv</a>, må du ha en gyldig fagreferanse (fra et fag du ikke er tilknyttet) for å legge til en blogg.</p>');
				
			$form_id = <<<HTML
			<fieldset><legend>Fagreferanse</legend>
				$infotext
				<input type="hidden" name="faction" value="selectnewblog">
				<label><input class="long" type="text" name="manual_id" maxlength="32" required pattern="^[0-9a-f]{32}$" title="Sørg for at du kopierer inn referansen eksakt"/></label>
			</fieldset>
HTML;
			}
			# Hvis bruker derimot er innlogget, vis heller en liste over brukerens fag; anta at han ikke vil
			# legge inn i noen annens fag.
			else{
				$SQL = sprintf("SELECT * FROM subjects WHERE users_email='%s'", $username);
				$result = $this->runSQL2($SQL);
				while ($subjectrow = $result->fetch_object()){
					$subjectoptions .= '<option value="' . $subjectrow->unique . '">' . $subjectrow->name . '</option>';
				}
				$result->close;
				
				$form_id = <<<HTML
				<fieldset><legend>Fag</legend>
					<p>En blogg må være tilknyttet et fag for å kunne registreres. Siden du er innlogget og er tilknyttet fag, vil du her få velge mellom disse. Vil du legge til en blogg i et fag registrert på en annen person, må du benytte link oppgitt av denne.</p>
					<input type="hidden" name="faction" value="selectnewblog">
					<select name="manual_id">
						$subjectoptions
					</select>
				</fieldset>
HTML;
			}
		}
		$html = <<<HTML
		<div id="form_description">
			<h1>Registrer ny blogg</h1>
			<p>Her kan du registrere din blogg. Alle feltene under bortsett fra rss må fylles ut med passende informasjon, 
				mens tittel på blogg vil hentes automatisk fra adressen du oppgir, nærmere bestemt fra &lt;title&gt;-tagen</p>
		</div>
		<form action="?" method="post">
			$form_id
			<fieldset><legend>Adresser</legend>
				<input type="hidden" name="faction" value="addblog"/>
				<input type="hidden" name="id" value="$_GET[id]"/>
				<label>
					<input class="long" type="text" name="url" placeholder="http://domene.tld/sti/" $urlmatch required />
					<strong> Blogg *</strong>
				</label><br />
				<label>
					<input class="long" type="text" name="rss" placeholder="http://domene.tld/sti/RSS" $urlmatch />
					 RSS
				</label>
			</fieldset>
			<fieldset><legend>Annen informasjon</legend>
				<label>
					<select name="freq">
						<option value="0">Daglig</option>
						<option value="1">Ukentlig</option>
						<option value="2">Månedlig</option>
						<option value="3">Intet mønster</option>
					</select> Oppdateringsmønster
				</label><br />
				<label>
					<input class="short" type="text" name="author" placeholder="Jason Bourne" required />
					<strong> Ditt navn *</strong>
				</label><br />
				<label>Beskrivelse <br />
					<textarea type="text" name="desc" placeholder="Verdens beste haiku-blogg" required></textarea>
				</label>
			</fieldset>
			<br />
			<button type="submit">Legg til blogg</button>
			<button type="reset">Nullstill</button>
		</form>
HTML;
		echo $html;
	}
	
	function fetchTitle($url){
		$urlContents = file_get_contents($url);
		if ($urlContents === false) {return null;}
		$dom = new DOMDocument();
		@$dom->loadHTML($urlContents);
		$title = $dom->getElementsByTagName('title');
		return $title->item(0)->nodeValue;
	}
		
	public function esc($string){
		return htmlentities(str_replace(array('--', ';', '\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('- -', '\;', '\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string), ENT_QUOTES, 'UTF-8'); 
	}
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
	
	public function runSQL2($SQL){
		// Se etter feilmelding under tilkobling
		if (mysqli_connect_errno()){
			printf("Tilkoblingsfeil (mySQL - url.php)\n %s", mysqli_connect_error());
			exit();
		}
		// Kjør spørring
		if($result = $this->d->query($SQL)){
			// Spørring utført, bruk resultat
			return $result;
		}
		// Noe gikk galt under kjøring av spørring
		return 0;
	}
}

?>