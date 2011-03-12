<?php
# Setup
require_once('conf.php');
$c = new Config();
$to = $c->esc(htmlentities($_GET['to']));

# Finn url i DB
$SQL = "SELECT * FROM links WHERE url='$to'";
mysql_connect($c->db_host, $c->db_user,$c->db_pass);
@mysql_select_db($c->db_name) or die("asd... ingen tilgang til database");
$result = mysql_query($SQL);
$results = mysql_fetch_assoc($result);

# Oppdater klikk
$newclick = $results['clicks'] + 1;
$ref = $results['ref'];
$SQL = "UPDATE links SET clicks='$newclick' WHERE ref='$ref'";
$result = mysql_query($SQL);
mysql_close();

# Redirect til siden
header('Location: ' . $to);
?>