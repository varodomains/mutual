<?php
	include "includes.php";

	$html = file_get_contents("https://data.iana.org/TLD/tlds-alpha-by-domain.txt");

	if ($html) {
		$tlds = explode("\n", $html);
		$tlds = array_filter($tlds);
		array_shift($tlds);

		$json = json_encode($tlds);
		file_put_contents($GLOBALS["path"]."etc/tlds.txt", $json);
	}
?>