<?php
	$path = "/var/www/html/mutual/";

	$config["localSqlHost"] = "localhost";
	$config["localSqlUser"] = "user";
	$config["localSqlPass"] = "pass";
	$config["localSqlDatabase"] = "pdns";

	$config["remoteSqlHost"] = "localhost";
	$config["remoteSqlUser"] = "user";
	$config["remoteSqlPass"] = "pass";
	$config["remoteSqlDatabase"] = "pdns";

	$GLOBALS["normalSOA"] = "ns1.hshub.io ops.hshub.io 1 10800 3600 604800 3600";
	$GLOBALS["normalNS1"] = "ns1.hshub.io";
	$GLOBALS["normalNS2"] = "ns2.hshub.io";

	$GLOBALS["handshakeSOA"] = "ns1.hshub ops.hshub.io 1 10800 3600 604800 3600";
	$GLOBALS["handshakeNS1"] = "ns1.hshub.";
	$GLOBALS["handshakeNS2"] = "ns2.hshub.";
?>