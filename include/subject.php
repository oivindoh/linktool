<?php
class SubjectHandler{
	private $c;
	private $l;
	
	public function SubjectHandler(&$config, &$login){
		$this->c = &$config;
		$this->l = &$login;
	}
	public function addSubject($code, $term, $name){
		if(!$name || $name == ""){
			return 0;
		}
		$user = $this->l->getUserName();
		
		if($user == ""){
			#ikke innlogget
			return 0;
		}
		$unique = $this->generateKey($name);
		$term = $this->esc($term);
		$code = $this->esc($code);
		$name = $this->esc($name);
		$SQL = sprintf("INSERT INTO subjects VALUES('%s','%s','%s','%s','%s')", $unique, $code, $term, $name, $user);
		$result = $this->runSQL($SQL); # true/false for INSERT
		if ($result){
			return 1;
		}
		return 0;
	}
	
	public function showAddSubjectForm(){
		# Lag optionliste for årstall; inneværende pluss to
		$currentyear = date(Y);
		$options_year = '';
		for ($i = $currentyear; $i < $currentyear+3; $i++){
			$options_year .= '<option value="'. $i . '">' . $i . '</option>';
		}
		
		# Forhåndsvelg semester utifra hvilket vi er i
		# - usannsynlig at noen vil opprette et fag for samme semester etter oktober (høst)
		# og mars (vår), så vær litt semismart her
		$options_term = (date(n) > 10 || date(n) < 4 
							? '<option value="H">Høst</option><option value="V" selected>Vår</option>'
							: '<option value="H" selected>Høst</option><option value="V">Vår</option>'
							);
		$html = <<<HTML
		<div id="form_description">
			<h1>Registrer nytt fag</h1>
			<p>Her kan du registrere et nytt fag, der du blir stående som eneste person med full oversikt og mulighet til å 
			slette/endre linker uten å måtte huske på referansenummer i hvert enkelt tilfelle.</p>
		</div>
		<form action="?" method="post">
			<fieldset><legend>Faginformasjon</legend>
				<input type="hidden" name="faction" value="addsubject">
				
				<label><input class="long" type="text" name="name" required placeholder="PHP - Introduksjonskurs"/> Navn</label><br />
				<label><input class="short" type="text" name="code" placeholder="PHP101"/> Fagkode</label><br />
					<select name="term_semester">
						$options_term
					</select>
				<label>
					<select name="term_year">
						$options_year
					</select>
					 Semester
				</label>
			</fieldset>
			<div>
			<button type="submit">Registrer fag</button>
			<button type="reset">Begynn på nytt</button>
			</div>
		</form>
HTML;
		echo $html;
				
	}
	
	public function removeSubject($id, $confirm = false){
		if($this->l->getUserName() == ""){
			return 0;
		}
		$id = $this->esc($id);
		if ($confirm){
			# Nødvendig SQL
			$SQL = sprintf("DELETE FROM `subjectlinks` WHERE `subjectlinks`.`subjects_unique` = '%s'", $id);
			$SQL2 = sprintf("DELETE FROM `subjects` WHERE `unique` = '%s'", $id);
			
			# Finn ut hvilke linker som også må bort
			$array_of_links_to_delete = Array();
			$SQL3 = sprintf("SELECT * FROM `subjectlinks` WHERE `subjectlinks`.`subjects_unique` = '%s'", $id);
			$result = $this->runSQL($SQL3);
			if ($result){
				$i = 0;
				while($result_row = mysql_fetch_assoc($result)){
					$array_of_links_to_delete[$i] = $result_row['links_ref'];
					$i++;
				}
				$deletelinks = true;
			}
			else{
				$deletelinks = false;
			}
			
			$result = $this->runSQL($SQL);
			if($result){
				if($deletelinks){
					# ingen grunn til at dette skulle feile....
					foreach($array_of_links_to_delete as $key => $delete_target){
						$SQL3 = "DELETE FROM `links` WHERE `ref`='$delete_target'";
						$result = $this->runSQL($SQL3);
					}
				}
				$result = $this->runSQL($SQL2);
				if ($result){
					# Alt ok!
					return 2;
				}
			}
			# Meh, feil!
			return 1;
		}
		# ikke bekreftet
		$SQL = sprintf("SELECT * FROM subjects WHERE `unique`='%s'", $id);
		$result = $this->runSQL($SQL);
		if($result){
			$result = mysql_fetch_assoc($result);
			$html = <<<HTML

<!-- output block -->
			<h1>Bekreft sletting av $result[name]</h1>
			<p>Det er <strong>ikke</strong> mulig å angre dette valget, og samtlige blogger 
				tilknyttet faget vil også bli fjernet fra systemet</p>
			<form>
				<input type="hidden" name="id" value="$id" />
				<input type="hidden" name="action" value="deletesubject" />
				<input type="hidden" name="confirm" value="1" />
				<button type="submit">Bekreft</button>
			</form>
<!-- /output block-->
HTML;
		return $html;
		}
		return 0;
	}
	
	public function listBlogs($id, $opml = no){
		$id = $this->esc($id);
		$db = $this->c->db_name;
		if ($this->l->getUserName() != ""){
			$helpertext = '<p>Du kan trygt gi lenken til denne siden til studenter: 
							de vil ikke få mulighet til å slette eller endre oppføringer med 
							mindre de har referansenummer til den enkelte oppføring.</p>';
		}
				
		# Vis valg av felt
		# Sørg for at valgte felt allerede er krysset av.
		# løses ved å sette variabler med samme navn som boksen til checked
		if (isset($_GET['show'])){
			foreach ($_GET['show'] as $key => $valuechecked){
				$$valuechecked = "checked";
			}
		}
		# Kryss av alle felt om ingenting annet er oppgitt
		!$_GET['show'] ? $url = $rss = $author = $description = $frequency = $clicks = $title = "checked" : false ;
		
		# Sett opp skjema for valg av felt
		$form_selection = <<<HTML

<!-- output block -->
<form method="get">
	<fieldset>
		<legend>Visningsvalg</legend>
		<input type="hidden" name="id" value="$id" />
		<input type="hidden" name="action" value="listblogs" />
		<label>
			<input type="checkbox" name="show[]" value="url" $url />
			 url &nbsp;</label> 
		<label>
			<input type="checkbox" name="show[]" value="rss" $rss />
			 rss &nbsp;</label> 
		<label>
			<input type="checkbox" name="show[]" value="author" $author />
			 forfatter &nbsp;</label> 
		<label>
			<input type="checkbox" name="show[]" value="description" $description />
			 beskrivelse &nbsp;</label> 
		<label>
			<input type="checkbox" name="show[]" value="frequency" $frequency />
			 oppdateringer &nbsp;</label>
		<label>
			<input type="checkbox" name="show[]" value="clicks" $clicks />
			 klikk &nbsp;</label>
		<button type="submit">Vis</button>
	</fieldset>
</form>
<!-- /output block -->
HTML;
		# Inner Join Is The Enemy
		$SQL = sprintf("SELECT DISTINCT name, ref, url, rss, author, description, frequency, clicks, title FROM $db.links
		INNER JOIN $db.subjectlinks ON
		$db.subjectlinks.links_ref = $db.links.ref
		INNER JOIN $db.subjects ON
		$db.subjects.unique = $db.subjectlinks.subjects_unique
		WHERE $db.subjectlinks.subjects_unique = '%s'", $id);
	
		$result = $this->runSQL($SQL);
		
		# Hent første rad for å vise fagnavn over listen
		$number_of_links = mysql_num_rows($result);								# Burde vel egentlig gjøres i sql
		if($number_of_links === 0){
			echo '<h1>Ingen tilknyttede blogger</h1>';
			return 0;
		}
		$first_row = mysql_fetch_assoc($result);
		$out = '
			<h1>Blogger for ' . $first_row['name'] . ' (' . $number_of_links . ')</h1>
			'. $helpertext . $form_selection . '
			<div id="linklist">';
			
		# Sett pointer tilbake til rad 0 før utlisting av blogger
		mysql_data_seek($result, 0);
		
		# Opprett OMPL-skjema
		$ompl_list = "";
		while($result_array = mysql_fetch_assoc($result)){
			$delete_change_links = '';
			$rss_link = '';
			# Vis RSS-link om feltet ikke er tomt og bruker har valgt å vise RSS

			if($result_array['rss'] != "" && $rss){
				$rss_link = '<li><a href="'. $result_array['rss'] .'">RSS</a></li>';
				}

			if ($this->l->getUserName() != ""){
				# Vis linker til slett og endre om bruker er innlogget
				$delete_change_links = '<li class="admin"><ul><li class="slett""><a href="?action=deleteblog&amp;id='. $result_array['ref'] 
				.'">Slett</a></li><li><a href="?action=editblog&amp;id='. $result_array['ref'] .'">Endre</a></li>'. $rss_link . '</ul></li>';
			}
			elseif ($rss_link) {
				$delete_change_links = '<li class="admin"><ul><li>' . $rss_link . '</li></ul></li>';
			}
		
			# Bruk tittel som linknavn om denne er hentet fram
			$result_array['title'] != "" || $result_array['title'] != null
				? $url_link_text = $result_array['title'] 
				: $url_link_text = $result_array['url'];
				
			$out .= '
			<ul>
				<li>
					<h3><a href="go.php?to='. $result_array['url'] . '">'. $url_link_text 
					.'</a></h3></li>'. $delete_change_links;
			
			$description ? $out .= '<li class="description"><blockquote>' . $result_array['description'] . '</blockquote></li>' : $out = $out;
			$url ? $out .= '<li>' . $result_array['url'] . '</li>' : $out = $out;
			$author ? $out .= '<li>Forfatter: ' . $result_array['author'] . '</li>' : $out = $out;
			
			switch($result_array['frequency']){
				case 0: $frequency_text = "Daglig";break;
				case 1: $frequency_text = "Ukentlig";break;
				case 2: $frequency_text = "Månedlig"; break;
				case 3: $frequency_text = "Intet mønster"; break;
			}
			
			$frequency ? $out .= '<li>Oppdateringsfrekvens: ' . $frequency_text . '</li>' : $out = $out;
			$clicks ? $out .= '<li>Antall visninger: ' . $result_array['clicks'] . '</li>' : $out = $out;
			$out .= "\n</ul>";
		}
		
		$opml_form = <<<HTML
		<form action="opml.php" method="get">
			<fieldset><legend>Aggregering</legend>
			<input type="hidden" name="id" value="$id" />
			<button type="submit">Hent OPML</button>
			</fieldset>
		</form>
HTML;
		echo $out . '</div><div id="opml-form">' . $opml_form .'</div>';
	}
	
	// Returns mysql resource or false on no subjects
	public function getUserSubjects($user){
		$user = $this->esc($user);
		$SQL = sprintf("SELECT * FROM subjects WHERE users_email='%s'", $user);
		$result = $this->runSQL($SQL);
		if (mysql_num_rows($result) < 1){
			return false;
		}
		return $result;
	}
	
	public function listByUser($user){
		$result = $this->getUserSubjects($user);
		if(!$result){
			return false;
		}
		$out = '<div id="subjects_overview">';
		while ($row = mysql_fetch_array($result, MYSQL_ASSOC)){
			$out .= <<<HTML
			<h3 class="liste_header">$row[name]</h3>
			<div class="liste_item">
				<ul>
					<!-- admin, rss -->
					<li class="admin">hello there</li>
					
					<li title="Fagkode">$row[code]</li>
					<li title="Semester">$row[term]</li>
					<li title="Unik link"><a href="?action=newblog&id=$row[unique]">ny blogg/studentlink</a></li>
					<li title="Liste over tilknyttede blogger"><a href="?action=listblogs&id=$row[unique]">vis/rediger blogger</a></li>
					<li class="delete" title="Slett"><a href="?action=deletesubject&id=$row[unique]">slett</a></li>
				</ul>
			</div>
HTML;
		}
		$out .= '</div>';
		return $out;
	
	}
	# Her genereres nøkkelen som deles ut til studenter
	# Dette er også PK i fagtabellen
	public function generateKey($navn){
		$key = $navn . $this->c->salt;
		return md5($key);
	}
	
	public function esc($string){
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string); 
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
}
?>