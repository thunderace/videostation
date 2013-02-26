<?php
/**
Requiert deux parametres : link, rep
**/
require_once('config.php');
require_once('system_config.php');
require_once('API-allocine.php');
require_once('API-TMDb.php');
require_once('functions.php');
require_once('movies_series.php');
connect($HOST_SQL, $USER_SQL,$PASSWORD_SQL,$DATABASE);

logInfo("index_movie.php - link " . $_GET['link']);
//$link=urldecode($_GET['link']);
$link=$_GET['link'];
if (!isset($_GET['rep'])) {
    $dir = dirname($link);
    $link = basename($link);
} else {
    $dir=urldecode($_GET['rep']);
}
$force = 0;
if (isset($_GET['force']))
    $force = $_GET['force'];
    
logInfo("index_movie.php - indexing " . joinPath($dir, $link) . " force = " . $force);
index($dir, $link, $force);

?>