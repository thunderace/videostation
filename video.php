<?php
require_once("lib/API-allocine.php");
require_once("lib/config.php");
require_once('lib/system_config.php');
require_once('lib/API-TMDb.php');
require_once('lib/functions.php');
require_once('lib/lang.php');
connect($HOST_SQL, $USER_SQL,$PASSWORD_SQL,$DATABASE);
$link=addslashes(urldecode($_GET['link']));
$dir=$_GET['rep'];
if (al_is_serie($dir)){
    $sql = "SELECT * FROM series WHERE link='".$link."'";
    $req = mysql_query($sql) or die ('Erreur sql '.$sql.' '.mysql_error());
    $infos = mysql_fetch_array($req);
    $serie = true;
} else {
    $genres = "";
    $sql = "SELECT * FROM movies WHERE link='".$link."'";
    $req = mysql_query($sql) or die ('Erreur sql '.$sql.' '.mysql_error());
    $infos = mysql_fetch_array($req);
    $serie = false;
    $sqlgenres = "SELECT name FROM genres, movie_genre WHERE fk_id_movie='".$infos['id_movie']."' and id_genre=fk_id_genre";
    $reqgenres = mysql_query($sqlgenres) or die ('Erreur sql: '.mysql_error());
    while($genre = mysql_fetch_array($reqgenres)){
	    if(preg_match('#'.$genre['name'].'#',$genres)=='0') 
            $genres .= $genre['name'].',';
    }
    $genres = substr($genres,0,-1);
}

if(!$MODAL){
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>Home Server Videos</title>
<link type="text/css" rel="stylesheet" media="screen" href="css/default.css">
</head>
<body>
<?php
}
if($serie){
echo '<nav><div>';
echo '<h1>'.$infos['name'];
if ($infos['name']!=$infos['episode_original_name']) echo ' ('.$infos['episode_original_name'].')';
echo '</h1></div></nav>';
echo '<div id="content">';
?>

<table style="border:0px;">
	<tr><td rowspan="11" style="vertical-align:top;"><?php if (!empty($infos['poster'])) { resize($infos['poster']);} else echo 'Image indisponible';?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo length;?>:</b></td><td><?php echo $infos['length'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo season;?>:</b></td><td><?php echo $infos['season_nb'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo episode;?>:</b></td><td><?php echo $infos['episode_nb'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo note;?>:</b></td><td><?php echo $infos['note'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo actors;?>:</b></td><td><?php echo $infos['actors'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo synopsis;?>:</b></td><td><?php echo $infos['synopsis'];?></td></tr>
</table>
<?php
}
else {
echo '<nav><div>';
echo '<h1>'.$infos['name'];
if ($infos['name']!=$infos['original_name']) 
    echo ' ('.$infos['original_name'].')';
echo '</h1></div></nav>';
echo '<div id="content">';
?>

<table style="border:0px;">
	<tr><td rowspan="11" style="vertical-align:top;"><?php if (!empty($infos['poster'])) { resize($infos['poster']);} else echo 'Image indisponible';?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo year;?>:</b></td><td><?php echo $infos['year'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo length;?>:</b></td><td><?php echo $infos['length'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo note;?>:</b></td><td><?php echo round($infos['note'],1);?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo votes;?>:</b></td><td><?php echo $infos['votes'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo country;?>:</b></td><td><?php echo $infos['countries'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo genres;?>:</b></td><td><?php echo $genres;?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo director;?>:</b></td><td><?php echo $infos['directors'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo actors;?>:</b></td><td><?php echo $infos['actors'];?></td></tr>
	<tr><td style="vertical-align:top;"><b><?php echo synopsis;?>:</b></td><td><?php echo $infos['synopsis'];?></td></tr>

    <?php
    if ($TRAILER == TRUE) {
        echo '<tr><td style="vertical-align:top;"><b><?php echo trailer;?>:</b></td><td>';

    	if($infos['api'] == 'Allocine' and !empty($infos['trailer'])){
    		echo '<div><object type="application/x-shockwave-flash" data="'.$infos['trailer'].'" width="420" height="357"><param name="allowFullScreen" value="true"></object></div>';
    	}
    	elseif(($infos['api'] == 'TMDb' or $infos['api'] == 'manual') and $TRAILER == TRUE and !empty($infos['trailer'])){
            $codeyoutube = explode('v=',$infos['trailer']);
    		$codeyoutube = $codeyoutube[1];
    		if(strlen($codeyoutube) != 11) {
    			$codeyoutube = explode('&',$codeyoutube);
    			$codeyoutube = $codeyoutube[0];
    		}
		    echo '<iframe width="420" height="315" style="margin-left:auto;margin-right:auto;" src="http://www.youtube.com/embed/'.$codeyoutube.'" frameborder="0" allowfullscreen></iframe>';
    	}
	}
	?>
    </td></tr>
</table>
<?php
}
?>
</div>
<?php
if (!$MODAL){
echo '</body>';
echo '</html>';
}
?>
