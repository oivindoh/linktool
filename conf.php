<?php
class Config{
	
	# Databaseoppsett lokalt
	public $db_host = "localhost";
	public $db_user = "root";
	public $db_pass = "test123";
	public $db_name = "oivindoh";
		
	# Salt som brukes i md5-hashing for å unngå å
	# bli bitt i ræva av rainbow-tabeller
	public $salt = 1299387344;
	
	# Debug mode
	public $debug = 1;

	public function esc($string){
		return str_replace(array('\\', "\0", "\n", "\r", "'", '"', "\x1a"), array('\\\\', '\\0', '\\n', '\\r', "\\'", '\\"', '\\Z'), $string); 
	}
}

?>