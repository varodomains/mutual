<?php
	$path = "/var/www/html/mutual/";

	$GLOBALS["hnsHostname"] = "varo";
	$GLOBALS["icannHostname"] = "varo.domains";

	$GLOBALS["localSqlHost"] = "localhost";
	$GLOBALS["localSqlUser"] = "user";
	$GLOBALS["localSqlPass"] = "pass";
	$GLOBALS["localSqlDatabase"] = "pdns";

	$GLOBALS["remoteSqlHost"] = "localhost";
	$GLOBALS["remoteSqlUser"] = "user";
	$GLOBALS["remoteSqlPass"] = "pass";
	$GLOBALS["remoteSqlDatabase"] = "pdns";

	$GLOBALS["pass"] = "";

	$GLOBALS["normalSOA"] = "ns1.".$GLOBALS["icannHostname"]." ops.".$GLOBALS["icannHostname"]." 1 10800 3600 604800 3600";
	$GLOBALS["normalNS1"] = "ns1.".$GLOBALS["icannHostname"];
	$GLOBALS["normalNS2"] = "ns2.".$GLOBALS["icannHostname"];
	
	$GLOBALS["handshakeSOA"] = "ns1.".$GLOBALS["hnsHostname"]." ops.".$GLOBALS["icannHostname"]." 1 10800 3600 604800 3600";
	$GLOBALS["handshakeNS1"] = "ns1.".$GLOBALS["hnsHostname"].".";
	$GLOBALS["handshakeNS2"] = "ns2.".$GLOBALS["hnsHostname"].".";
?>