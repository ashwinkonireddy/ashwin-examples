<?php
/*
ftpsync
ver 0.2.3
by rek@rek.me

You can use this script under the GPLv3 license.
The full-text of GPLv3 is here http://www.gnu.org/licenses/gpl-3.0.txt
*/
$ftpUser = 'anonymous';
$ftpHost = 'localhost';
$ftpPass = 'abc@example.com';
$ftpPort = 21;
$activeMode = false;
$remoteRoot = '/';
$localRoot = getcwd();

if($argc <= 1) {
	echoUsage();
}
for($i = 1; $i < $argc; $i++) {
	if($argv[$i][0] == '-') {
		switch($argv[$i]) {
			case '-u': case '--user':
				$ftpUser = $argv[++$i];
				break;
			case '-h': case '--host':
				$ftpHost = $argv[++$i];
				break;
			case '-o': case '--port':
				$ftpPort = $argv[++$i];
				break;
			case '-p': case '--pass':
				$ftpPass = $argv[++$i];
				break;
			case '-t': case '--active':
				$activeMode = true;
				break;
			case '-c': case '--chdir':
				$remoteRoot = $argv[++$i];
				break;
			case '-r': case '--root':
				$localRoot = $argv[++$i];
				break;
			case '-f': case '--sync-file':
				$fileName = $argv[++$i];
				$syncMode = 'f';
				break;
			case '-i': case '--sync-incremental':
				$syncMode = 'i';
				break;
			case '-a': case '--sync-whole-site':
				$syncMode = 'a';
				break;
			default:
				echoUsage();
		}
	} else {
		echoUsage();
	}
}
if(!isset($syncMode)) $syncMode = 'i';
/*
echo $ftpUser."\n";
echo $ftpHost."\n";
echo $ftpPass."\n";
echo $ftpPort."\n";
var_dump($activeMode)."\n";
echo $remoteRoot."\n";
echo $localRoot."\n";
echo $syncMode."\n";
echo $fileName;
*/

switch($syncMode) {
	case 'f':
		echo "Doing single file sync.\n";
		if(basename($fileName) == $fileName) {
			$fileName = clearPath(getcwd()).$fileName;
		}
		if(!is_file($fileName)) {
			echo "$fileName is not a valid file.\n";
			die();
		}
		$localRoot = clearPath($localRoot);
		if(substr($fileName, 0, strlen($localRoot) -1) != substr($localRoot, 0, strlen($localRoot) -1)) {
			echo "$fileName is not in the local root, skip upload.\n";
			break;
		}
		echo "Loading file cache...";
		//Check local file cache, if failed, do incremental sync.
		$cache = loadCache($localRoot);
		if($cache !== false) {
			echo "OK\n";
			//Upload single file.
			$t = filemtime($fileName);
			$f = substr($fileName, strlen($localRoot));
			$files = array($f);
			$ftp = getFtpConnection($ftpHost, $ftpPort, $ftpUser, $ftpPass, $activeMode);
			upload($ftp, $remoteRoot, $localRoot, $files);
			ftp_close($ftp);
			//Update local file cache
			$cache[md5($f)] = $t;
			echo "Saving local file cache...";
			if(saveCache($cache, $localRoot, false))
				echo "OK\n";
			else
				echo "Failed\n";
			break;
		} else {
			echo "Failed! ";
		}
	case 'i':
		echo "Doing incremental sync.\n";
		echo "Scanning local files...";
		$localFiles = scanLocal($localRoot); //Scan local files.
		if($localFiles === false) {
			echo "Failed!\n";
			die();
		} else {
			echo "OK\n";
		}
		echo "Loading file cache...";
		$fileCache = loadCache($localRoot); //Load local file cache if exists.
		echo "OK\n";

		//Find out newer files and upload them.
		$newCache = $localFiles;
		echo "Comparing files...";
		foreach($localFiles as $f => $t) {
			$mf = md5($f);
			if(isset($fileCache[$mf]) && $t <= $fileCache[$mf])
				unset($localFiles[$f]);
		}
		echo "OK\n";
		if(count($localFiles) == 0) {
			echo "No files need to upload.\n";
			die();
		}
		$ftp = getFtpConnection($ftpHost, $ftpPort, $ftpUser, $ftpPass, $activeMode);
		upload($ftp, $remoteRoot, $localRoot, array_keys($localFiles));
		ftp_close($ftp);

		echo "Saving local file cache...";
		if(saveCache($newCache, $localRoot))
			echo "OK\n";
		else
			echo "Failed\n";
		break;
	case 'a':
	echo "Doing whole site sync.\n";
	echo "Scanning local files...";
	$localFiles = scanLocal($localRoot); // Scan local files
	if($localFiles === false) {
		echo "Failed!\n";
		die();
	} else {
		echo "OK\n";
	}
	$ftp = getFtpConnection($ftpHost, $ftpPort, $ftpUser, $ftpPass, $activeMode);
	echo "Scanning remote files...";
	$remoteFiles = scanRemote($ftp, $remoteRoot); //Scan remote files.
	if($remoteFiles === false) {
		echo "Failed!\n";
		die();
	} else {
		echo "OK\n";
	}

	//Find out and upload newer files.
	echo "Comparing files...";
	$cache = $localFiles;
	foreach($localFiles as $f => $t) {
		if(isset($remoteFiles[$f]) && $t <= $remoteFiles[$f])
			unset($localFiles[$f]);
/*		else {
			echo $f;
			echo date(" Y-m-d H:i:s ", $t);
			echo date("Y-m-d H:i:s\n", $remoteFiles[$f]);
		}*/
	}
	echo "OK\n";
	if(count($localFiles) == 0) {
		echo "No files need to upload.\n";
		die();
	}
	upload($ftp, $remoteRoot, $localRoot, array_keys($localFiles));
	ftp_close($ftp);
	echo "Saving local file cache...";
	if(saveCache($cache, $localRoot))
		echo "OK\n";
	else
		echo "Failed\n";
}




function getFtpConnection($host, $port, $user, $pass, $active) {
	echo "Connecting to FTP server...";
	$ftp = @ftp_connect($host, $port);
	if($ftp === false) {
		echo "Failed!\n";
		die();
	} else {
		echo "OK\n";
	}
	echo "Logging in...";
	if(!@ftp_login($ftp, $user, $pass)) {
		echo "Failed!\n";
		die();
	} else {
		echo "OK\n";
	}
	echo "Switching to ".($active?"ACTIVE":"PASV")." mode...";
	if(!@ftp_pasv($ftp, !$active)) {
		echo "Failed!\n";
		die();
	} else {
		echo "OK\n";
	}
	return $ftp;
}
function upload($ftp, $remoteRoot, $localRoot, $uploadFiles) {
	if(count($uploadFiles) == 0) {
		echo "No files need to upload.\n";
		return;
	}
	$remoteRoot = clearPath($remoteRoot);
	$localRoot = clearPath($localRoot);

	echo "Changing directory to $remoteRoot...";
	if(!@ftp_chdir($ftp, $remoteRoot)) {
		echo "Failed!\n";
		die();
	} else {
		echo "OK\n";
	}
	foreach($uploadFiles as $f) {
		echo "Uploading $f...";
		$d = dirname($f);
		if(!@ftp_chdir($ftp, $remoteRoot.$d))  { // if cannot chdir, try to create so
			ftp_chdir($ftp, $remoteRoot);
			$dir = strtok($d, '/\\');
			do {
				if($dir == '') continue;
				@ftp_mkdir($ftp, $dir);
				ftp_chdir($ftp, $dir);
			} while($dir = strtok('/\\'));
		}
		if(ftp_put($ftp, basename($f), $localRoot.$f, FTP_BINARY)) {
			echo "OK\n";
		} else {
			echo "Failed!Skip it.\n";
		}
	}
}

function saveCache($localFiles, $root, $hash=true) {
	$root = clearPath($root);
	$fp = @fopen($root.'.sync', 'w');
	if($fp === false) return false;
	foreach($localFiles as $f => $t) {
		fprintf($fp, "%s\t%d\n", $hash?md5($f):$f, $t);
	}
	fclose($fp);
	return true;
}

function loadCache($root) {
	$root = clearPath($root);
	$fp = @fopen($root.'.sync', 'r');
	if($fp === false) return false;
	$cache = array();
	while($n = fscanf($fp, "%s\t%d\n")) {
		list($fileHash, $modTime) = $n;
		$cache[$fileHash] = $modTime;
	}
	fclose($fp);
	return $cache;
}

function scanRemote($ftp, $dir, $rootLength=null) {
	$dir = clearPath($dir);
	if(is_null($rootLength)) $rootLength = strlen($dir);

	$files = array();
	$curDirList = ftp_rawlist($ftp, $dir);
	foreach($curDirList as $n) {
		if(!preg_match('/^(\S)[rwx\-]{9}[ 0-9]+\w+\s+\d+\s+[0-9:]+\s+(.+)$/', $n, $m)) continue;
		$isFile = $m[1] == '-';
		$isDir = $m[1] == 'd';
		$n = $dir.$m[2];
		if($isFile) {
			$fileName = substr($n, $rootLength);
			$files[$fileName] = ftp_mdtm($ftp, $n);
		} else if($isDir){
			$subDirFiles = scanRemote($ftp, $n, $rootLength);
			$files = array_merge($files, $subDirFiles);
		}
	}
	return $files;
}

function scanLocal($dir, $rootLength=null) {
	$dir = clearPath($dir);
	if(is_null($rootLength)) $rootLength = strlen($dir);

	// check whether $root is a valid directory
	if(!is_dir($dir)) {
		echo "$dir is not a valid directory\n";
		die();
	}

	//scan dir, create file modification time view
	$files = array();
	$curDirList = scandir($dir) ;
	foreach($curDirList as $n) {
		if($n[0] == '.') continue; // jump the hidden directory and file
		$n = $dir.$n;
		if(is_file($n)) {
			$fileName = substr($n, $rootLength);
			$files[$fileName] = filemtime($n);
		} else if(is_dir($n)){
			$subDirFiles = scanLocal($n, $rootLength);
			$files = array_merge($files, $subDirFiles);
		}
	}
	return $files;
}

function clearPath($p) {
	$length = strlen($p);
	if($p[$length - 1] != '\\' && $p[$length - 1] != '/')
		$p .= '/';
	return $p;
}
function echoUsage() {
echo
"Usage: ftpsync [OPTIONS] [FLAG] [file]
Options could be:
-u, --user              FTP login user Default is anonymous
-h, --host              FTP host Default is localhost
-o, --port              FTP port Default is 21
-p, --pass              FTP login password Default is abc@example.com
-c, --chdir             Change to such remote dir when start to sync
-r, --root              Local site root Default is current working directory
-f, --sync-file         Upload a single file
Flag is either:
-t, --active            Turn off PASV mode
-i, --sync-incremental  Upload files that newer then last upload
-a, --sync-whole-site   Compare FTP files and upload newer file
";
exit();
}
?>