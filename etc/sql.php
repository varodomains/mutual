<?php
	$GLOBALS["localDatabaseInfo"] = [
		"host" => $config["localSqlHost"],
		"user" => $config["localSqlUser"],
		"pass" => $config["localSqlPass"],
		"db" => $config["localSqlDatabase"],
		"options" => [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		]
	];
	$GLOBALS["remoteDatabaseInfo"] = [
		"host" => $config["remoteSqlHost"],
		"user" => $config["remoteSqlUser"],
		"pass" => $config["remoteSqlPass"],
		"db" => $config["remoteSqlDatabase"],
		"options" => [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		]
	];
	$GLOBALS["localDatabaseDSN"] = "mysql:host=".$GLOBALS["localDatabaseInfo"]["host"].";dbname=".$GLOBALS["localDatabaseInfo"]["db"].";charset=utf8mb4";
	$GLOBALS["remoteDatabaseDSN"] = "mysql:host=".$GLOBALS["remoteDatabaseInfo"]["host"].";dbname=".$GLOBALS["remoteDatabaseInfo"]["db"].";charset=utf8mb4";

	function initSQL($remote) {
		retry:

		try {
			if ($remote) {
				$GLOBALS["remoteSQL"] = new PDO($GLOBALS["remoteDatabaseDSN"], $GLOBALS["remoteDatabaseInfo"]["user"], $GLOBALS["remoteDatabaseInfo"]["pass"], $GLOBALS["remoteDatabaseInfo"]["options"]);
			}
			else {
				$GLOBALS["localSQL"] = new PDO($GLOBALS["localDatabaseDSN"], $GLOBALS["localDatabaseInfo"]["user"], $GLOBALS["localDatabaseInfo"]["pass"], $GLOBALS["localDatabaseInfo"]["options"]);
			}
		}
		catch (\PDOException $e) {
			$message = $e->getMessage();

			if (strpos($message, "Connection refused") !== false) {
				goto retry;
			}
		}
	}

	function initCorrectSQL($remote) {
		if ($remote) {
			if (!@$GLOBALS["remoteSQL"]) {
				initSQL(true);
			}
			return "remoteSQL";
		}
		else {
			if (!@$GLOBALS["localSQL"]) {
				initSQL(false);
			}
			return "localSQL";
		}
	}

	function sql($query, $values = []) {
		$remote = false;
		if (substr($query, 0, 12) === "INSERT INTO " || substr($query, 0, 7) === "UPDATE " || substr($query, 0, 12) === "DELETE FROM ") {
			$remote = true;
		}

		$database = $GLOBALS[initCorrectSQL($remote)];

		retry:
		try {
			$statement = $database->prepare($query);
			$success = $statement->execute($values);

			$result = $statement->fetchAll();

			if (count($result) > 1) {
				return $result;
			}
			else if (count($result) == 1) {
				return [$result[0]];
			}
			else if ($success && (substr($query, 0, 12) === "INSERT INTO " || substr($query, 0, 12) === "DELETE FROM " || (substr($query, 0, 7) === "UPDATE "))) {
				return true;
			}

			return false;
		}
		catch (\PDOException $e) {
			$message = $e->getMessage();

			if (strpos($message, "MySQL server has gone away") !== false || strpos($message, "Communication link failure") !== false) {
				initCorrectSQL($remote);

				goto retry;
			}
			else {
				//return $message;
				//error
			}
		}
	}
?>