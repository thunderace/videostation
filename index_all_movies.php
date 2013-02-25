<?php

require_once("lib/movies_series.php");
require_once("lib/config.php");
require_once('lib/system_config.php');


//$dirPath = "/volume1/video/Divx/Alphabetique/A";  // for test
$dirPath = $VIDEO_DIR;

$filenames = scanFileNameRecursivly($dirPath);

foreach($filenames as $file) {
    echo "Scanning " . basename($file) . " from directory " . dirname($file) . "\n";
    index(dirname($file), basename($file));
}

function scanFileNameRecursivly($path = '', &$name = array())
{
    global $EXT;
	$path = $path == ''? dirname(__FILE__) : $path;
	$lists = @scandir($path);
  
	if(!empty($lists))  {
		foreach($lists as $f) { 
			if(is_dir($path.DIRECTORY_SEPARATOR.$f)) {
				if ($f != ".." && $f != ".")
					scanFileNameRecursivly($path.DIRECTORY_SEPARATOR.$f, &$name); 
			} else {
				$extension = strtolower(pathinfo($f, PATHINFO_EXTENSION));
				if (in_array($extension, $EXT))
					$name[] = $path.DIRECTORY_SEPARATOR.$f;
			}
		}
	}
	
	return $name;
}
?>