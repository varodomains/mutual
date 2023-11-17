<?php
	function uuid($data = null) {
		$data = $data ?? random_bytes(16);
		assert(strlen($data) == 16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
	}

	function pdns($command) {
		return shell_exec("sudo pdnsutil ".$command);
	}

	function idForZone($zone) {
		return sql("SELECT `id` FROM `domains` WHERE `uuid` = ?", [$zone])[0];
	}

	function domainInfoForZone($zone) {
		$getDomain = sql("SELECT * FROM `domains` WHERE `uuid` = ?", [$zone]);
		
		return $getDomain[0];
	}

	function domainForZone($zone) {
		$getDomain = sql("SELECT `name` FROM `domains` WHERE `uuid` = ?", [$zone]);
		
		return $getDomain[0]["name"];
	}

	function dsForDomain($domain) {
		$showZone = pdns("show-zone ".$domain);

		preg_match("/(?:DS .+ IN DS )(?<ds>.+?)(?: ;.+)(?=SHA256 digest)/", $showZone, $zoneMatch);
		$dsRecord = $zoneMatch["ds"];
		return $dsRecord;
	}

	function nsForDomain($domain) {
		$isHandshake = @sql("SELECT `handshake` FROM `domains` WHERE `name` = ?", [$domain])[0]["handshake"];

		if ($isHandshake && strpos($domain, ".") == false) {
			$nsRecords = [$GLOBALS["handshakeNS1"], $GLOBALS["handshakeNS2"]];
		}
		else {
			$nsRecords = [$GLOBALS["normalNS1"], $GLOBALS["normalNS2"]];
		}
		
		return $nsRecords;
	}

	function recordForID($uuid) {
		return sql("SELECT * FROM `records` WHERE `uuid` = ?", [$uuid])[0];
	}

	function recordsForRedirect($record) {
		$recordInfo = recordForID($record);
		$aliasRecord = sql("SELECT * FROM `records` WHERE `type` = 'ALIAS' AND `name` = ? AND `system` = 1 AND `domain_id` = ?", [$recordInfo["name"], $recordInfo["domain_id"]])[0]["uuid"];
		$txtRecord = sql("SELECT * FROM `records` WHERE `type` = 'TXT' AND `name` = ? AND `system` = 1 AND `domain_id` = ?", ["_redirect.".$recordInfo["name"], $recordInfo["domain_id"]])[0]["uuid"];

		return [
			"ALIAS" => $aliasRecord,
			"TXT" => $txtRecord
		];
	}

	function getTLDS() {
		$json = file_get_contents($GLOBALS["path"]."etc/tlds.txt");
		$tlds = json_decode($json);

		return $tlds;
	}

	function domainInfo($domain) {
		$output = [
			"reserved" => false,
			"handshake" => false,
			"invalid" => false,
		];

		$split = explode(".", $domain);
		$count = count($split);
		$last = end($split);

		$lower = strtolower($last);
		$upper = strtoupper($last);

		$reserved = ["corp", "domain", "example", "home", "host", "invalid", "lan", "local", "localdomain", "localhost", "test"];
		if (in_array($lower, $reserved)) {
			$output["reserved"] = true;
		}

		$tlds = getTLDS();
		if (!in_array($upper, $tlds)) {
			$output["handshake"] = true;
		}
		else {
			if ($count < 2) {
				$output["invalid"] = true;
			}
		}

		return $output;
	}

	function formatName($name, $domain) {
		$name = strtolower($name);
		if ($name === "@") {
			$name = $domain;
		}

		$suffix = ".".$domain;
		$suffixLength = strlen($suffix);
		$nameSuffix = substr($name, -$suffixLength);

		if ($name !== $domain && $nameSuffix !== $suffix) {
			$name = $name.$suffix;
		}

		return $name;
	}
?>