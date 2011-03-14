<?php
# Setup
require_once('conf.php');
$c = new Config();
$to = str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), htmlentities($_GET['to']));

# Finn url i DB
$SQL = sprintf("SELECT * FROM links WHERE url='%s'", $to);
mysql_connect($c->db_host, $c->db_user,$c->db_pass);
@mysql_select_db($c->db_name) or die("asd... ingen tilgang til database");
$result = mysql_query($SQL);
$results = mysql_fetch_assoc($result);

# Oppdater klikk
$newclick = $results['clicks'] + 1;
$ref = $results['ref'];
$SQL = sprintf("UPDATE links SET clicks='%d' WHERE ref='%s'", $newclick, $ref);
$result = mysql_query($SQL);
mysql_close();

# Redirect til siden
header('Location: ' . $to);
?>