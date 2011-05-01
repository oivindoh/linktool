<?php
	
	class OPMLHandler{

		public $c;
		
		public function OPMLHandler(&$config){
			$this->c = $config;
		}

		public function listBlogs($id){
			$id = $this->esc($id);
			$db = $this->c->db_name;
			
			# Mal for godkjent OPML-fil:
			$opml = <<<OPML
<?xml version="1.0" encoding="utf-8"?>
<opml version="1.1">
	<head>
		<title>Linkliste for ___SUBJECTNAME___</title>
		<dateCreated>___TIME___</dateCreated>
	</head>
	<body>
		<outline text="Directories">
			<outline text="Blogger">
				___LINKLIST___
			</outline>
		</outline>
	</body>
</opml>
OPML;

			$opml = <<<OPML
<?xml version="1.0" encoding="utf-8"?>
<opml version="1.1">
	<head>
		<title>Linkliste for ___SUBJECTNAME___</title>
		<dateCreated>___TIME___</dateCreated>
	</head>
	<body>
		<outline text="Linkliste for ___SUBJECTNAME___">
			___LINKLIST___
		</outline>
	</body>
</opml>
OPML;
			# Samme som i subject.php
			$SQL = sprintf("SELECT DISTINCT name, ref, url, rss, author, description, frequency, clicks, title FROM $db.links
			INNER JOIN $db.subjectlinks ON
			$db.subjectlinks.links_ref = $db.links.ref
			INNER JOIN $db.subjects ON
			$db.subjects.unique = $db.subjectlinks.subjects_unique
			WHERE $db.subjectlinks.subjects_unique = '%s'", $id);
			$result = $this->runSQL($SQL);

			# Hent første rad for å vise fagnavn over listen
			$number_of_links = mysql_num_rows($result);
			$first_row = mysql_fetch_assoc($result);
			mysql_data_seek($result, 0);

			# Opprett OMPL-skjema
			$ompl_list = "";
			while($result_array = mysql_fetch_assoc($result)){
				$opml_list .= '<outline text="' . html_entity_decode($result_array['title'], ENT_COMPAT, "UTF-8") . '" type="rss" htmlUrl="' . $result_array['url'] . '" xmlUrl="' . $result_array['rss'] . '"/>' . "\n\t\t\t";
			}
			$opml = str_replace("___TIME___", date("r"), $opml);
			$opml = str_replace("___SUBJECTNAME___", $first_row['name'], $opml);
			$opml = str_replace("___LINKLIST___", $opml_list, $opml);
			echo $opml;
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

if ($_GET['id']){
	include('conf.php');
	$c = new Config();
	$o = new OPMLHandler($c);
	header ("Content-Type:text/xml");
	$o->listBlogs($_GET['id']);
}
else{
	echo "/dev/null";
}
?>