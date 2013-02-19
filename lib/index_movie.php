<?php
/**
Requiert deux parametres : link, rep
**/
require_once('config.php');
require_once('API-allocine.php');
require_once('API-TMDb.php');
require_once('functions.php');
require_once('movies_series.php');
connect($USER_SQL,$PASSWORD_SQL,$DATABASE);

$link=urldecode($_GET['link']);
$dir=urldecode($_GET['rep']);
index($dir, $link);

?>