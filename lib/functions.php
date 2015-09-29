<?php

/**
 * Returns system information
 * @return string
 */
function getSys() {
	$os = strtolower(substr(PHP_OS, 0, 3));
	$web = strtolower(PHP_SAPI) !== "cli";
	
	return $web ? 'web' : $os;
}

/**
 * Calculates the parameter passed in bytes, and then rounds it to one decimal place
 * @param int $bytes
 * @return string
 */
function hr_bytes($bytes) {
	if ($bytes >= pow(1024, 3)) {
		return sprintf("%.1f", ($bytes / pow(1024, 3))) . "G";
	}
	if ($bytes >= pow(1024, 2)) {
		return sprintf("%.1f", ($bytes / pow(1024, 2))) . "M";
	}
	if ($bytes >= 1024) {
		return sprintf("%.1f", ($bytes / (1024))) . "K";
	}
	return $bytes . "B";
}

/**
 * Calculates the parameter passed in bytes, and then rounds it to the nearest integer
 * @param int $bytes
 * @return string
 */
function hr_bytes_rnd($bytes) {
	if ($bytes >= pow(1024, 3)) {
		return round(($bytes / pow(1024, 3)), 0) . "G";
	}
	if ($bytes >= pow(1024, 2)) {
		return round(($bytes / pow(1024, 2)), 0) . "M";
	}
	if ($bytes >= 1024) {
		return round(($bytes / (1024)), 0) . "K";
	}
	return round($bytes, 0) . "B";
}

/**
 * Calculates the parameter passed to the nearest power of 1000, then rounds it to the nearest integer
 * @param int $bytes
 * @return string
 */
function hr_num($bytes) {
	if ($bytes >= pow(1000, 3)) {
		return round(($bytes / pow(1000, 3)), 0) . "G";
	}
	if ($bytes >= pow(1000, 2)) {
		return round(($bytes / pow(1000, 2)), 0) . "M";
	}
	if ($bytes >= 1000) {
		return round(($bytes / (1000)), 0) . "K";
	}
	return round($bytes, 0) . "B";
}

/**
 * Calculates uptime to display in a more attractive form
 * @param int $uptime
 * @return string
 */
function pretty_uptime($uptime) {
	$seconds = $uptime % 60;
	$minutes = (int)(($uptime % 3600) / 60);
	$hours = (int)(($uptime % 86400) / 3600);
	$days = (int)($uptime / 86400);
	
	if ($days > 0) {
		return "${days}d ${hours}h ${minutes}m ${seconds}s";
	}
	if ($hours > 0) {
		return "${hours}h ${minutes}m ${seconds}s";
	}
	if ($minutes > 0) {
		return "${minutes}m ${seconds}s";
	}
	return "${seconds}s";
}

/**
 * parseArgs Command Line Interface (CLI) utility function.
 * @usage               $args = parseArgs($_SERVER['argv']);
 * @author              Patrick Fisher <patrick@pwfisher.com>
 * @source              https://github.com/pwfisher/CommandLine.php
 */
function parseArgs($argv) {
	array_shift($argv);
	$o = array();
	foreach ( $argv as $a ) {
		if (substr($a, 0, 2) == '--') {
			$eq = strpos($a, '=');
			if ($eq !== false) {
				$o[substr($a, 2, $eq - 2)] = substr($a, $eq + 1);
			}
			else {
				$k = substr($a, 2);
				if (!isset($o[$k])) {
					$o[$k] = true;
				}
			}
		}
		else if (substr($a, 0, 1) == '-') {
			if (substr($a, 2, 1) == '=') {
				$o[substr($a, 1, 1)] = substr($a, 3);
			}
			else {
				foreach ( str_split(substr($a, 1)) as $k ) {
					if (!isset($o[$k])) {
						$o[$k] = true;
					}
				}
			}
		}
		else {
			$o[] = $a;
		}
	}
	return $o;
}

/**
 * Loads options into options array, based on command line parms, or get/post params
 */
function loadOpts() {
	global $argv, $opts;
	
	if (isset($argv)) {
		foreach (parseArgs($argv) as $key => $value) {
			if(isset($opts[$key]))
				$opts[$key] = $value;
		}
	}

	if (isset($_REQUEST)) {
		foreach ($_REQUEST as $key => $value) {
			if(isset($opts[$key])) {
				$opts[$key] = $value;
			}
		}
	}
}

/**
 * Show usage information then exit
 * @return string
 */
function showUsage() {
	global $version;
	return("   MySQLTuner $version - MySQL High Performance Tuning Script" . PHP_EOL .
		'   Bug reports, feature requests, and downloads at https://github.com/zeryl/MySQLTuner-PHP' . PHP_EOL .
		'   Maintained by Zeryl (lordsaryon@gmail.com) - Licensed under GPL' . PHP_EOL .
		'   Original by Major Hayden (major@mhtx.net)' . PHP_EOL .
		PHP_EOL .
		'   Important Usage Guidelines:' . PHP_EOL .
		'      To run the script with the default options, run the script without arguments' . PHP_EOL .
		'      Allow MySQL server to run for at least 24-48 hours before trusting suggestions' . PHP_EOL .
		'      Some routines may require root level privileges (script will provide warnings)' . PHP_EOL .
		'      You must provide the remote server\'s total memory when connecting to other servers' . PHP_EOL .
		PHP_EOL .
		'   Connection and Authentication' . PHP_EOL .
		'      --host <hostname>    Connect to a remote host to perform tests (default: localhost)' . PHP_EOL .
		'      --socket <socket>    Use a different socket for a local connection' . PHP_EOL .
		'      --port <port>        Port to use for connection (default: 3306)' . PHP_EOL .
		'      --user <username>    Username to use for authentication' . PHP_EOL .
		'      --pass <password>    Password to use for authentication' . PHP_EOL .
		PHP_EOL .
		'   Performance and Reporting Options' . PHP_EOL .
		'      --skipsize           Don\'t enumerate tables and their types/sizes (default: on)' . PHP_EOL .
		'                             (Recommended for servers with many tables)' . PHP_EOL .
		'      --checkversion       Check for updates to MySQLTuner (default: don\'t check)' . PHP_EOL .
		'      --forcemem <size>    Amount of RAM installed in megabytes' . PHP_EOL .
		'      --forceswap <size>   Amount of swap memory configured in megabytes' . PHP_EOL .
		PHP_EOL .
		'   Output Options:' . PHP_EOL .
		'      --nogood             Remove OK responses' . PHP_EOL .
		'      --nobad              Remove negative/suggestion responses' . PHP_EOL .
		'      --noinfo             Remove informational responses' . PHP_EOL .
		'      --nocolor            Don\'t print output in color' . PHP_EOL .
		PHP_EOL);
}
