<?php
// This function gives the file size with its human readable units.

echo filesizeUnits(filesize($_SERVER['PHP_SELF']))."\n";



function filesizeUnits($size) {
 
    // Adapted from: http://www.php.net/manual/en/function.filesize.php
 
    $mod = 1024;
 
    $units = explode(' ','B KB MB GB TB PB');
    for ($i = 0; $size > $mod; $i++) {
        $size /= $mod;
    }
    return round($size, 2) . ' ' . $units[$i];
}

