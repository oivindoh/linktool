<?php
class URLHandler{
	private $c;
	private $l;
	
	public function URLHandler(&$conf, &$login){
		$this->c = &$conf;
		$this->l = &$login;
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
		$SQL = "SELECT * FROM links WHERE ref='$ref'";
		$result = $this->runSQL($SQL);
		$found_link = mysql_num_rows($result);
		$fl_assoc = mysql_fetch_assoc($result);
		$result = "";
		$SQL = "SELECT * FROM subjects WHERE subjects.unique='$subj'";
		$result = $this->runSQL($SQL);
		$found_subj = mysql_num_rows($result);
		$fs_assoc = mysql_fetch_assoc($result);
		
		
		# TODO / work in progress. 
		$SQL = "SELECT * FROM subjectlinks WHERE links_ref='$ref'";
		$result = $this->runSQL($SQL);
		$foundmatchinglink = mysql_num_rows($result);
		if($foundmatchinglink){
			$matchassoc = mysql_fetch_assoc($result);
			if($matchassoc['subjects_unique'] != $subj){
				# go for it
				# ... hva med duplicate ref i db? bruke et nummerfelt med autoincrement som key istedenfor?
			}
		}
				
		if($found_link == 0 || !$found_link){
			if($found_subj > 0 || $found_subj){
				# "noe gikk forferdelig galt-land", men også tegn på at vi kom et stykke :)
				$SQL = "INSERT INTO links VALUES('$ref','$url','$rss', '$author', '$desc', '$freq', 0, '$title')";
				$result = $this->runSQL($SQL);
				if($result){
					# lagt til; legg så inn i mange-mange-tabellen
					$SQL = "INSERT INTO subjectlinks VALUES('$subj', '$ref')";
					$result = $this->runSQL($SQL);
					if($result){
						# alt ok
						# TODO: hook for sending av mail om ønskelig?
						return $ref;
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
		
		$SQL = "UPDATE links SET url='$url', rss='$rss', author='$author', description='$desc', frequency='$freq' WHERE ref='$ref'";
		$result = $this->runSQL($SQL);
		if ($result){
			# Oppdatert
			# TODO: burde egentlig oppdatere referanse også?
			return 1;
		}
		return 0;
	}
	
	public function deleteURL($id){
		$id = $this->c->esc($id);
		
		# Sjekk om URL tilhører bruker (via fag)
		$SQL = "SELECT * FROM subjectlinks INNER JOIN subjects ON subjects.unique = subjectlinks.subjects_unique WHERE links_ref = '$id'";
		$result = mysql_fetch_assoc($this->runSQL($SQL));
		if ($result['users_email'] == $this->l->getUserName()){
			# Bruker tilhører dette faget
			$SQL = "DELETE FROM `subjectlinks` WHERE `subjectlinks`.`links_ref` = '$id'";
			$result_manymany = $this->runSQL($SQL);
			if ($result_manymany){
				# Bloggen er fjernet
				$SQL = "DELETE FROM `links` WHERE `ref` = '$id'";
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
			echo "<h1>Rediger blogg</h1><p>Blogg må angis ved referanse. Har du denne, kan du skrive den inn her:</p>";
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
		
		$SQL = "SELECT * FROM links WHERE ref='$url_reference'";
		$result = $this->runSQL($SQL);
		$selected_blog = mysql_fetch_assoc($result);
		if(!$selected_blog) { echo "<h1>Feil</h1><p>Angitt blogg eksisterer ikke</p>"; return 0; }
		$html = <<<HTML
		<h1>Rediger blogg</h1>
		<p>$selected_blog[url] ($selected_blog[author])</p>
		<form action="?" method="post">
			<fieldset>
				<input type="hidden" name="faction" value="editblog"/>
				<input type="hidden" name="id" value="$_GET[id]"/>
				<label><input type="text" name="url" value="$selected_blog[url]" $urlmatch required /> <strong>Adresse *</strong></label><br />
				<label><input type="text" name="rss" value="$selected_blog[rss]" $urlmatch /> RSS-feed</label><br />
				<label><input type="text" name="author" value="$selected_blog[author]" required /> <strong>Ditt navn *</strong></label><br />
				<label><input type="text" name="desc" value="$selected_blog[description]" required /> Beskrivelse av bloggen</label><br />
				<label>
				<select name="freq">
HTML;
					$selected_blog['frequency'] == 0 ? $html .= '<option value="0" selected>Daglig</option>' : $html .= '<option value="0">Daglig</option>';
					$selected_blog['frequency'] == 1 ? $html .= '<option value="1" selected>Ukentlig</option>' : $html .= '<option value="1">Ukentlig</option>';
					$selected_blog['frequency'] == 2 ? $html .= '<option value="2" selected>Månedlig</option>' : $html .= '<option value="2">Månedlig</option>';
					$selected_blog['frequency'] == 3 ? $html .= '<option value="3" selected>Intent mønster</option>' : $html .= '<option value="3">Intet mønster</option>';

$html = $html . <<<HTML
				</select> Oppdateringsfrekvens</label>
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
		if (empty($id) || strlen($id) != 32){
			$form_id = <<<HTML
			<fieldset><legend>Fagreferanse</legend>
				<p>Har du ikke en fagreferanse (lang remse bokstaver og tall), kan du sjekke it'sLearning eller kontakte din faglærer for å få oppgitt denne. Uten fagreferanse kan du dessverre ikke legge til din blogg.</p>
				<input type="hidden" name="faction" value="selectnewblog">
				<label><input class="long" type="text" name="manual_id" maxlength="32" required pattern="^[0-9a-f]{32}$" title="Sørg for at du kopierer inn referansen eksakt"/></label>
			</fieldset>
HTML;
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
					<input class="long" type="text" name="url" maxlength="45"placeholder="http://domene.tld/sti/" $urlmatch required />
					<strong> Blogg *</strong>
				</label><br />
				<label>
					<input class="long" type="text" name="rss" maxlength="45" placeholder="http://domene.tld/sti/RSS" $urlmatch />
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
				<label>
					<textarea type="text" name="desc" maxlength="45" placeholder="Verdens beste haiku-blogg" required></textarea>
					 Beskrivelse
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
		return str_replace(array('--', ';', '\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('- -', '\;', '\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string); 
	}
	public function runSQL($SQL){
		$connection = mysql_connect($this->c->db_host, $this->c->db_user,$this->c->db_pass);
		@mysql_select_db($this->c->db_name) or die("asd... ingen tilgang til database");
		$return = mysql_query($SQL);
		mysql_close();
		return $return;
	}
}

?>