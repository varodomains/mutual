<?php
	include "etc/includes.php";

	$json = file_get_contents('php://input');
	$data = json_decode($json, true);

	if (@$GLOBALS["pass"]) {
		if (!@$data["pass"] || $data["pass"] !== $GLOBALS["pass"]) {
			die("No password or invalid password provided.");
		}
	}

	if (!@$data["action"]) {
		die("No action provided.");
	}

	switch ($data["action"]) {
		case "addRecord":
		case "newRecord":
			$domain = domainForZone($data["zone"]);

			if ($data["type"] !== "MX") {
				$data["prio"] = 0;
			}

			if ($data["content"] === "@") {
				$data["content"] = $domain;
			}

			$data["name"] = formatName($data["name"], $domain);
			break;

		case "updateRecord":
			$domain = domainForZone($data["zone"]);

			if (@$data["type"] !== "MX") {
				$data["prio"] = 0;
			}

			switch ($data["column"]) {
				case "name":
					$data["value"] = formatName($data["value"], $domain);
					break;

				case "content":
					if (@$data["value"] === "@") {
						$data["value"] = $domain;
					}
					break;
			}
			break;

		case "editRecord":
			$domain = domainForZone($data["zone"]);

			$data["name"] = formatName($data["name"], $domain);
			break;
		
		default:
			break;
	}

	switch ($data["action"]) {
		case "domainInfo":
			$domainInfo = domainInfo($data["domain"]);
			die(json_encode($domainInfo));
			break;

		case "getSLDS":
			$getSLDS = sql("SELECT `name`,`uuid` AS `id`,`expiration`,`renew` FROM `domains` WHERE `account` = ? AND `uuid` IS NOT NULL AND `registrar` IS NOT NULL ORDER BY `name` ASC", [$data["user"]]);
			die(json_encode($getSLDS));
			break;

		case "getZones":
			$getZones = sql("SELECT `name`,`uuid` AS `id` FROM `domains` WHERE `account` = ? AND `uuid` IS NOT NULL AND `registrar` IS NULL AND `name` NOT LIKE '[%' ORDER BY `name` ASC", [$data["user"]]);
			die(json_encode($getZones));
			break;

		case "getRecords":
			$domainId = idForZone($data["zone"])["id"];

			$name = "%";
			if (@$data["name"]) {
				$name = $data["name"];
			}

			$type = "%";
			if (@$data["type"]) {
				$type = $data["type"];
			}

			$content = "%";
			if (@$data["content"]) {
				$content = $data["content"];
			}

			$getRecords = sql("SELECT `name`,`type`,`content`,`ttl`,`prio`,`uuid` FROM `records` WHERE `domain_id` = ? AND `name` LIKE ? AND `type` LIKE ? AND `content` LIKE ? AND `system` != 1 AND `type` IS NOT NULL AND `uuid` IS NOT NULL ORDER BY `type`,`name`,`prio` ASC", [$domainId, $name, $type, $content]);
			die(json_encode($getRecords));
			break;

		case "createZone":
			$zone = uuid();

			$domainInfo = domainInfo($data["domain"]);

			$handshake = 0;
			$soaRecord = $GLOBALS["normalSOA"];
			$ns1Record = $GLOBALS["normalNS1"];
			$ns2Record = $GLOBALS["normalNS2"];
			if ($domainInfo["handshake"]) {
				$handshake = 1;
				$soaRecord = $GLOBALS["handshakeSOA"];
				$ns1Record = $GLOBALS["handshakeNS1"];
				$ns2Record = $GLOBALS["handshakeNS2"];
			}

			$insertDomain = sql("INSERT INTO `domains` (name, type, uuid, account, handshake) VALUES (?,?,?,?,?)", [$data["domain"], "NATIVE", $zone, $data["user"], $handshake]);
			$domainId = idForZone($zone)["id"];
			$createSOA = sql("INSERT INTO `records` (domain_id, name, type, content, ttl, prio, uuid, system) VALUES (?,?,?,?,?,?,?,?)", [$domainId, $data["domain"], "SOA", $soaRecord, 20, 0, uuid(), 1]);
			$createNS1 = sql("INSERT INTO `records` (domain_id, name, type, content, ttl, prio, uuid, system) VALUES (?,?,?,?,?,?,?,?)", [$domainId, $data["domain"], "NS", $ns1Record, 20, 0, uuid(), 1]);
			$createNS2 = sql("INSERT INTO `records` (domain_id, name, type, content, ttl, prio, uuid, system) VALUES (?,?,?,?,?,?,?,?)", [$domainId, $data["domain"], "NS", $ns2Record, 20, 0, uuid(), 1]);
			$createParkingAlias = sql("INSERT INTO `records` (domain_id, name, type, content, ttl, prio, uuid, system) VALUES (?,?,?,?,?,?,?,?)", [$domainId, $data["domain"], "LUA", luaAlias("parking"), 20, 0, uuid(), 1]);
			$secureZone = pdns("secure-zone ".$data["domain"]);

			$output = [
				"zone" => $zone
			];
			die(json_encode($output));
			break;

		case "deleteZone":
			$domainId = idForZone($data["zone"])["id"];
			if ($domainId > 0) {
				sql("DELETE FROM `records` WHERE `domain_id` = ?", [$domainId]);
				sql("DELETE FROM `domains` WHERE `id` = ?", [$domainId]);
			}
			break;

		case "showZone":
			$domainInfo = domainInfoForZone($data["zone"]);
			$domain = $domainInfo["name"];
			$dsRecord = dsForDomain($domain);
			$nsRecords = nsForDomain($domain);

			$output = [
				"NS" => $nsRecords,
				"DS" => $dsRecord,
				"editable" => false
			];

			if ($domainInfo["registrar"]) {
				$output["editable"] = true;
			}

			die(json_encode($output));
			break;

		case "addRecord":
		case "newRecord":
			$domain = domainForZone($data["zone"]);
			$domainId = idForZone($data["zone"])["id"];

			if ($domain === $data["name"]) {
				deleteParkingRecords($domain, $domainId);
			}
			
			if (in_array($data["type"], ["REDIRECT", "WALLET"])) {
				$addRecord = sql("INSERT INTO `records` (domain_id, name, type, content, ttl, prio, uuid, auth, disabled) values (?,?,?,?,?,?,?,?,?)", [$domainId, $data["name"], $data["type"], $data["content"], $data["ttl"], $data["prio"], uuid(), 0, 1]);
				$addRecord = sql("INSERT INTO `records` (domain_id, name, type, content, ttl, prio, uuid, system) values (?,?,?,?,?,?,?,?)", [$domainId, $data["name"], "LUA", luaAlias("parking"), $data["ttl"], $data["prio"], uuid(), 1]);
			}
			else {
				$addRecord = sql("INSERT INTO `records` (domain_id, name, type, content, ttl, prio, uuid) values (?,?,?,?,?,?,?)", [$domainId, $data["name"], $data["type"], $data["content"], $data["ttl"], $data["prio"], uuid()]);
			}

			$rectifyZone = pdns("rectify-zone ".$domain);
			break;

		case "updateRecord":
			$domain = domainForZone($data["zone"]);
			$domainId = idForZone($data["zone"])["id"];
			$recordInfo = recordForID($data["record"]);

			if ($data["column"] === "name" && $data["value"] === $domain) {
				deleteParkingRecords($domain, $domainId);
			}

			if (in_array($recordInfo["type"], ["REDIRECT", "WALLET"])) {
				if ($data["column"] === "name") {
					$parkingRecords = recordsForParking($data["record"]);
					$updateRecord = sql("UPDATE `records` SET `name` = ? WHERE `uuid` = ?", [$data["value"], $parkingRecords["LUA"]]);

					if (@$parkingRecords["TLSA"]) {
						$updateRecord = sql("UPDATE `records` SET `name` = ? WHERE `uuid` = ?", ["_443._tcp.".$data["value"], $parkingRecords["TLSA"]]);
					}
				}
				$updateRecord = sql("UPDATE `records` SET `".$data["column"]."` = ? WHERE `uuid` = ?", [$data["value"], $data["record"]]);
			}
			else {
				$updateRecord = sql("UPDATE `records` SET `".$data["column"]."` = ? WHERE `uuid` = ?", [$data["value"], $data["record"]]);
			}

			if ($recordInfo["name"] === $domain) {
				addParkingIfNeeded($domain, $domainId);
			}

			$rectifyZone = pdns("rectify-zone ".$domain);
			die(json_encode(["value" => $data["value"]]));
			break;

		case "editRecord":
			$domain = domainForZone($data["zone"]);
			$domainId = idForZone($data["zone"])["id"];
			$recordInfo = recordForID($data["record"]);

			if ($data["name"] === $domain) {
				deleteParkingRecords($domain, $domainId);
			}

			if (in_array($recordInfo["type"], ["REDIRECT", "WALLET"])) {
				$parkingRecords = recordsForParking($data["record"]);
				$updateRecord = sql("UPDATE `records` SET `name` = ? WHERE `uuid` = ?", [$data["name"], $parkingRecords["LUA"]]);
				
				if (@$parkingRecords["TLSA"]) {
					$updateRecord = sql("UPDATE `records` SET `name` = ? WHERE `uuid` = ?", ["_443._tcp.".$data["name"], $parkingRecords["TLSA"]]);
				}

				$updateRecord = sql("UPDATE `records` SET `name` = ?, `content` = ?, `prio` = ?, `ttl` = ? WHERE `uuid` = ?", [$data["name"], $data["content"], $data["prio"], $data["ttl"], $data["record"]]);
			}
			else {
				$updateRecord = sql("UPDATE `records` SET `name` = ?, `content` = ?, `prio` = ?, `ttl` = ? WHERE `uuid` = ?", [$data["name"], $data["content"], $data["prio"], $data["ttl"], $data["record"]]);
			}

			if ($recordInfo["name"] === $domain) {
				addParkingIfNeeded($domain, $domainId);
			}
			
			$rectifyZone = pdns("rectify-zone ".$domain);
			die(json_encode($data));
			break;

		case "deleteRecord":
			$domain = domainForZone($data["zone"]);
			$domainId = idForZone($data["zone"])["id"];
			$recordInfo = recordForID($data["record"]);

			if (in_array($recordInfo["type"], ["REDIRECT", "WALLET"])) {
				$parkingRecords = recordsForParking($data["record"]);
				$deleteRecord = sql("DELETE FROM `records` WHERE `uuid` = ?", [$data["record"]]);
				$deleteRecord = sql("DELETE FROM `records` WHERE `uuid` = ?", [$parkingRecords["LUA"]]);
				if (@$parkingRecords["TLSA"]) {
					$deleteRecord = sql("DELETE FROM `records` WHERE `uuid` = ?", [$parkingRecords["TLSA"]]);
				}
			}
			else {
				$deleteRecord = sql("DELETE FROM `records` WHERE `uuid` = ?", [$data["record"]]);
			}

			if ($recordInfo["name"] === $domain) {
				addParkingIfNeeded($domain, $domainId);
			}

			$rectifyZone = pdns("rectify-zone ".$domain);
			break;

		default:
			break;
	}
?>