<?php
require_once('lib/config.php');
require_once('lib/system_config.php');
require_once('lib/API-allocine.php');
require_once('lib/API-TMDb.php');
require_once('lib/functions.php');
connect($HOST_SQL, $USER_SQL,$PASSWORD_SQL,$DATABASE);
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title><?php echo $APP_NAME;?></title>
<link rel="stylesheet" href="css/default.css">
<link rel="stylesheet" href="css/nyroModal.css">
<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.8.17.custom.css" />

<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>-->
<script type="text/javascript" src="http://code.jquery.com/ui/jquery-ui-git.js"></script>
<script type="text/javascript" src="js/jquery.nyroModal.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.tools.min.js"></script>
</head>
<body>
<nav><div><?php echo $_GET['link'];?></div></nav>
<div id="content">
<?php
    if(empty($_GET['action']))  {
        $genres = "";
        $sql = "SELECT * FROM movies WHERE link='".htmlspecialchars(addslashes(urldecode($_GET['link'])))."'";
        $req = mysql_query($sql) or die ('Erreur sql: '.mysql_error());
        $data = mysql_fetch_array($req);
        $sqlgenres = "SELECT name FROM genres, movie_genre WHERE fk_id_movie='".$data['id_movie']."' and id_genre=fk_id_genre";
        $reqgenres = mysql_query($sqlgenres) or die ('Erreur sql: '.mysql_error());
        while($genre = mysql_fetch_array($reqgenres)){
	        $genres .= $genre['name'].',';
        }
        $genres = substr($genres,0,-1);
?>
	<div id="tabsup" style="width:85%;min-height:400px;margin-left:auto;margin-right:auto">
		<ul>
    		<li><a href="#tabs-1">Indexation automatique</a></li>
    		<li><a href="#tabs-2">Modification nom de fichier</a></li>
			<li><a href="#tabs-3">Indexation manuelle</a></li>
		</ul>
		<div id="tabs-1">
			<p><form method="POST" action="update.php?link=<?php echo urlencode($_GET['link']); ?>&oldcode=<?php echo $_GET['oldcode'];?>&action=auto" class="nyroModal" style="text-align:center;">
Rechercher un film: <input type="text" name="recherche" class="form">
<select name="database">
<?php
	if ($MOVIES_DATABASE == "TMDb") 
	{
	echo '<option value="TMDb">TMDb</option>';
	echo '<option value="Allocine">Allocine</option>';
	}
	else
	{
	echo '<option value="Allocine">Allocine</option>';
	echo '<option value="TMDb">TMDb</option>';
	}
//	<option value="Allocine">Allocine</option>
//	<option value="TMDb">TMDb</option>
?>
</select>
			<input type="submit" value="Rechercher" class="form">
			</form></p>
		</div>
    	<div id="tabs-2">
			<p><form method="POST" action="update.php?link=<?php urlencode($_GET['link']); ?>&dir=<?php urlencode($_GET['dir']); ?>&action=rename" class="nyroModal" style="text-align:center;">
Ancien nom : <?php echo pathinfo($_GET['link'], PATHINFO_FILENAME);?> - Nouveau nom : <input type="text" name="newlink" class="form"> <input value="<?php echo urlencode($_GET['link']); ?>" type="hidden" name="link"> <input value="<?php echo urlencode($_GET['dir']); ?>" type="hidden" name="dir">
			<input type="submit" value="Renommer" class="form">
			</form></p>
		</div>
		<div id="tabs-3">
			<p><form method="POST" action="update.php?action=manual&oldcode=<?php echo $_GET['oldcode'];?>&link=<?php echo urlencode($_GET['link']);?>" class="nyroModal" enctype="multipart/form-data">
			<table border="0">
			<tr><td rowspan="13"><?php  
			if(is_file('images/poster_small/'.$data['id_movie'].'.jpg')) echo '<img src="images/poster_small/'.$data['id_movie'].'.jpg"><br>';
			else echo 'Aucune affiche disponible<br>';
			?><input type="file" name="poster"></td></tr>
			<tr><td><b><?php echo name;?>:</b></td><td><input type="text" size="40" name="name" value="<?php if($data['name']!='0') echo $data['name'];?>"></td></tr>
			<tr><td><b><?php echo original_name;?>:</b></td><td><input type="text" size="40" name="original_name" value="<?php if($data['original_name']!='0') echo $data['original_name'];?>"></td></tr>
			<tr><td><b><?php echo year;?>:</b></td><td><input type="text" size="4" name="year" value="<?php if($data['year']!='0') echo $data['year'];?>"></td></tr>
			<tr><td><b><?php echo length;?>:</b></td><td><input type="text" size="4" name="length" value="<?php if($data['length']!='0') echo $data['length'];?>"></td></tr>
			<tr><td><b><?php echo note;?>:</b></td><td><input type="text" id="amount" style="border:0; color:#f6931f; font-weight:bold;" /><div id="slider-range-min" rel="<?php echo ($data['note'])*2;?>"></div><input id="inputamount" type="hidden" name="note"></td></tr>
			<tr><td><b><?php echo votes;?>:</b></td><td><input type="text" size="2" name="votes" value="<?php if($data['votes']!='0') echo $data['votes'];?>"></td></tr>
			<tr><td><b><?php echo country;?>:</b></td><td><input type="text" size="40" name="countries" value="<?php if($data['countries']!='0') echo $data['countries'];?>"></td></tr>
			<tr><td><b><?php echo genres;?>:</b></td><td><input type="text" size="40" name="genres" value="<?php if($genres!='0') echo $genres;?>"></td></tr>
			<tr><td><b><?php echo director;?>:</b></td><td><input type="text" size="40" name="directors" value="<?php if($data['directors']!='0') echo $data['directors'];?>"></td></tr>
			<tr><td><b><?php echo actors;?>:</b></td><td><input type="text" size="40" name="actors" value="<?php if($data['actors']!='0') echo $data['actors'];?>"></td></tr>
			<tr><td><b><?php echo synopsis;?>:</b></td><td><textarea name="synopsis" cols="45" rows="10"><?php if($data['synopsis']!='0') echo stripslashes($data['synopsis']);?></textarea></td></tr>
			<tr><td><b><?php echo trailer;?>:</b></td><td><input type="text" size="40" name="trailer" value="<?php if($data['trailer']!='0') echo $data['trailer'];?>"></td></tr>
			<tr><td colspan="2"><input type="submit" value="valider"></td></tr>
			</table>
			</form>
			<form method="POST" action="update.php?action=erase&oldcode=<?php echo $_GET['oldcode'];?>&link=<?php echo urlencode($_GET['link']);?>" class="nyroModal" enctype="multipart/form-data">
			<input type="submit" value="supprimer">
			<form method="POST" action="update.php?action=eraseandignore&oldcode=<?php echo $_GET['oldcode'];?>&link=<?php echo urlencode($_GET['link']);?>" class="nyroModal" enctype="multipart/form-data">
			<input type="submit" value="supprimer et ne plus indexer"></input>
			</form>
			</p>
			
		</div>
	</div>
<?php
}
else
{
switch($_GET['action']){
    case 'rename':
        // rename file
        $dir = urldecode($_POST['dir']);
        $link = urldecode($_POST['link']);
        $newlink = trim(urldecode($_POST['newlink']));
        
        debug("Rename " . joinPath($dir,$link) . " to " . joinPath($dir, $newlink.'.'.pathinfo($link, PATHINFO_EXTENSION)));
        rename(joinPath($dir, $link), joinPath($dir, $newlink.'.'.pathinfo($link, PATHINFO_EXTENSION)));
        if(!al_is_serie(joinPath($dir,$link))) {
            $sql = "UPDATE movies SET link='" . stripslashes($newlink) . "' WHERE link='" . stripslashes($link) ."'";
            mysql_query($sql) or die ('Erreur SQL '.mysql_error());
        }
            
        echo '<div style="text-align:center;">';
	    echo '<img src="images/check.png" alt="ok">'.$_GET['link'].' mis &agrave; jour!<br />Recharger la page pour prendre les modifications en compte.</div>';
        break;
	case 'auto':
	    if($_POST['database'] == 'Allocine') 
            $moviesSearch = new AlloCine($LANGUAGE);
	    else 
            $moviesSearch = new TMDb($LANGUAGE);
            
	    try {
	        $recherche = $moviesSearch->movieMultipleSearch($_POST['recherche'],10);
        }
	    catch(Exception $e) {
		    echo 'Erreur : ',$e->getMessage(), "\n";
	    }
	?>
	<form method="POST" action="update.php?link=<?php echo urlencode($_GET['link']); ?>&oldcode=<?php echo $_GET['oldcode'];?>&action=auto" class="nyroModal" style="text-align:center;">Nouvelle recherche : <input type="text" name="recherche" class="form">
<select name="database">
<?php
	if ($MOVIES_DATABASE == "TMDb") 
	{
	echo '<option value="TMDb">TMDb</option>';
	echo '<option value="Allocine">Allocine</option>';
	}
	else
	{
	echo '<option value="Allocine">Allocine</option>';
	echo '<option value="TMDb">TMDb</option>';
	}
//	<option value="Allocine">Allocine</option>
//	<option value="TMDb">TMDb</option>
?>
</select>
			<input type="submit" value="Rechercher" class="form">
			</form>
	<?php
	echo '<ul class="movielist">';
	for($i=0;$i<count($recherche);$i++){
		echo '<li>';
        $img_link = "";
		if(empty($recherche[$i]['affiche'])) 
            echo '<img src="images/movie.png" alt="video" />';
		else {
			if($_POST['database'] == 'Allocine'){
			$img = explode('/',$recherche[$i]['affiche']);
			$end_url = '';
			for($j=3;$j<count($img);$j++){
			$end_url = $end_url.'/'.$img[$j];
			}
			$img = $img[0].'//'.$img[2].'/r_150_210'.$end_url;
			echo '<img src="'.$img.'" />';
			$img_link = '&poster='.$img;
			}
			elseif($_POST['database'] == 'TMDb'){
			echo '<img src="'.$recherche[$i]['affiche'].'">';
			$img_link = '&poster='.$recherche[$i]['affiche'];
			}
		}
		echo '<br>'.$recherche[$i]['titre'];
		if ($recherche[$i]['annee'] != '0') echo ' ['.$recherche[$i]['annee'].']';	
		echo '<br><a href="update.php?link='.urlencode($_GET['link']).'&code='.$recherche[$i]['code'].'&oldcode='.$_GET['oldcode'].'&database='.$_POST['database'].$img_link.'&action=autoupdate" class="nyroModal"><input type="button" value="Selectionner"></a>';
		echo '</li>';
	}
	echo '</ul>';
	break;
	
	case 'autoupdate':
	if($_GET['oldcode'] != 0){
	$sql = "DELETE FROM movie_genre WHERE fk_id_movie = '".$_GET['oldcode']."'";
	mysql_query($sql) or die ('Erreur SQL '.mysql_error());
	}
	switch ($_GET['database']){
		case 'Allocine':
		$movie = new Allocine($LANGUAGE);
		break;
		case 'TMDb':
		$movie = new TMDb($LANGUAGE);
		break;
	}
	$infos = $movie->movieInfos($_GET['code']);
	$sql = sprintf("UPDATE movies SET 
	id_movie='%s',
	name='%s',
	original_name='%s',
	length='%s',
	countries='%s',
	directors='%s',
	actors='%s',
	synopsis='%s',
	poster='%s',
	trailer='%s',
	note='%f',
	votes='%d',
	year='%s',
	api='%s' 
	WHERE link='%s'",
	mysql_real_escape_string($_GET['code']),
	mysql_real_escape_string($infos['titre']),
	mysql_real_escape_string($infos['titre-original']),
	mysql_real_escape_string($infos['longueur']),
	mysql_real_escape_string($infos['pays']),
	mysql_real_escape_string($infos['realisateur']),
	mysql_real_escape_string($infos['acteurs']),
	mysql_real_escape_string($infos['resume']),
	$infos['affiche'],
	$infos['bande-annonce'],
	$infos['note-public'],
	$infos['nb-note-public'],
	$infos['annee'],
	mysql_real_escape_string($_GET['database']),
	mysql_real_escape_string($_GET['link']));
	mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
	$genre = explode(',',$infos['genres']);
	$sql = "SELECT name FROM genres";
	$req = mysql_query($sql);
	$exist_genres = array();
	while ($data = mysql_fetch_array($req)){
		$exist_genres[] = $data['name'];
	}
	for($i=0;$i<count($genre);$i++){
		$gnre = trim($genre[$i]);
		if (!in_array($gnre,$exist_genres) and $gnre != ''){
			$sql = "INSERT INTO genres VALUES ('','".$gnre."')";
			mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
		}
		if($gnre != ''){
		$sql = "SELECT id_genre FROM genres WHERE name='".$gnre."'";
		$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
		$data = mysql_fetch_array($req);
		$insert = "INSERT INTO movie_genre VALUES('','".$data['id_genre']."','".$_GET['code']."')";
		mysql_query($insert) or die ('Erreur SQL : '.mysql_error());
		}
	}

	if(isset($_GET['poster'])){
		copy($_GET['poster'], 'images/poster_small/'.$_GET['code'].'.jpg');
	}

	echo '<div style="text-align:center;">';
	if(is_file('images/poster_small/'.$_GET['code'].'.jpg')) echo '<img src="images/poster_small/'.$_GET['code'].'.jpg" alt="Poster"><br>';
	echo '<img src="images/check.png" alt="ok">'.$_GET['link'].' mis &agrave; jour!<br />Recharger la page pour prendre les modifications en compte.</div>';
	break;
	
	
	case 'erase':
	if($_GET['oldcode'] != 0){ //erase imdbid
	    $sql = "DELETE FROM movie_genre WHERE fk_id_movie = '".$_GET['oldcode']."'";
	    mysql_query($sql) or die ('Erreur SQL '.mysql_error());
	}
	// erase id_movie where link = link
	$sql = "UPDATE movies SET id_movie=0, name=0, original_name=0, length=0, countries=0, directors=0, actors=0, synopsis=0,poster=0, trailer=0, note=0, votes=0, year=0 WHERE link = '".$_GET['link']."'";
	mysql_query($sql) or die ('Erreur SQL '.mysql_error());
	
	echo '<div style="text-align:center;">';
	echo '<img src="images/check.png" alt="ok">'.$_GET['link'].' mis &agrave; jour!<br />Recharger la page pour prendre les modifications en compte.</div>';

	break;
	case 'eraseandignore':
	if($_GET['oldcode'] != 0){ //erase imdbid
	$sql = "DELETE FROM movie_genre WHERE fk_id_movie = '".$_GET['oldcode']."'";
	mysql_query($sql) or die ('Erreur SQL '.mysql_error());
	}
	// erase id_movie where link = link
	$sql = "UPDATE movies SET id_movie=-1, name=0, original_name=0, length=0, countries=0, directors=0, actors=0, synopsis=0,poster=0, trailer=0, note=0, votes=0, year=0 WHERE link = '".$_GET['link']."'";
	mysql_query($sql) or die ('Erreur SQL '.mysql_error());
	
	echo '<div style="text-align:center;">';
	echo '<img src="images/check.png" alt="ok">'.$_GET['link'].' mis &agrave; jour!<br />Recharger la page pour prendre les modifications en compte.</div>';

	break;
	case 'manual':

	if($_GET['oldcode'] != 0){
	$sql = "DELETE FROM movie_genre WHERE fk_id_movie = '".$_GET['oldcode']."'";
	mysql_query($sql) or die ('Erreur SQL '.mysql_error());
	}
	
	$code = random_string(8);
	if(!empty($_FILES['poster']['name'])){
		$dossier = 'images/poster_small/';
		$taille_maxi = 6000000;
		$taille = filesize($_FILES['poster']['tmp_name']);
		$extensions = array('.jpg', '.JPG', '.jpeg', '.JPEG');
		$extension = strrchr($_FILES['poster']['name'], '.');
		//Début des vérifications de sécurité...
		if(!in_array($extension, $extensions)){ //Si l'extension n'est pas dans le tableau
			$erreur = 'Vous devez uploader un fichier de type jpg';
			echo $extension;
		}
		if($taille>$taille_maxi){
			$erreur = 'Le fichier est trop gros...';
		}
		if(!isset($erreur)){ //S'il n'y a pas d'erreur, on upload
			 //On formate le nom du fichier ici...
		
			if(move_uploaded_file($_FILES['poster']['tmp_name'], $dossier.$code.'.jpg')){ //Si la fonction renvoie TRUE, c'est que ça a fonctionné...
			$poster_path = 'images/poster_small/'.$code.'.jpg';
			reduire_image($dossier.$code.'.jpg','150','250',$dossier);
			}
			else //Sinon (la fonction renvoie FALSE).
			{
			  echo info('Echec de l\'upload !');
			}
		}
		else
		{
			echo $erreur;
		}
	}
	else {
		if(is_file('images/poster_small/'.$_GET['oldcode'].'.jpg')){
		rename('images/poster_small/'.$_GET['oldcode'].'.jpg','images/poster_small/'.$code.'.jpg');
		}
	}
	$sql = sprintf("UPDATE movies SET 
	id_movie='%s',
	name='%s',
	original_name='%s',
	length='%s',
	countries='%s',
	directors='%s',
	actors='%s',
	synopsis='%s',
	poster='%s',
	trailer='%s',
	note='%f',
	votes='%d',
	year='%s',
	api='%s' 
	WHERE link='%s'",
	mysql_real_escape_string($code),
	mysql_real_escape_string($_POST['name']),
	mysql_real_escape_string($_POST['original_name']),
	mysql_real_escape_string($_POST['length']),
	mysql_real_escape_string($_POST['countries']),
	mysql_real_escape_string($_POST['genres']),
	
	mysql_real_escape_string($_POST['directors']),
	mysql_real_escape_string($_POST['actors']),
	mysql_real_escape_string($_POST['synopsis']),
	$poster_path,
	mysql_real_escape_string($_POST['trailer']),
	$_POST['note']/2,
	mysql_real_escape_string($_POST['votes']),
	mysql_real_escape_string($_POST['year']),
	'manual',
	mysql_real_escape_string($_GET['link']));
	mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
	
	//$genre = explode(',',$infos['genres']);
	$genre = explode(',',$_POST['genres']);
	
	$sql = "SELECT name FROM genres";
	$req = mysql_query($sql);
	$exist_genres = array();
	while ($data = mysql_fetch_array($req)){
		$exist_genres[] = $data['name'];
	}
	for($i=0;$i<count($genre);$i++){
		$gnre = trim($genre[$i]);
		if (!in_array($gnre,$exist_genres) and $gnre != ''){
			$sql = "INSERT INTO genres VALUES ('','".$gnre."')";
			mysql_query($sql) or die('Erreur SQL !'.$sql.'<br>'.mysql_error());
		}
		if($gnre != ''){
		$sql = "SELECT id_genre FROM genres WHERE name='".$gnre."'";
		$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
		$data = mysql_fetch_array($req);
		$insert = "INSERT INTO movie_genre VALUES('','".$data['id_genre']."','".$code."')";
		mysql_query($insert) or die ('Erreur SQL : '.mysql_error());
		}
	}

	echo '<div style="text-align:center;">';
	if(is_file('images/poster_small/'.$code.'.jpg')) echo '<img src="images/poster_small/'.$code.'.jpg" alt="Poster"><br>';
	echo '<img src="images/check.png" alt="ok">'.$_GET['link'].' mis &agrave; jour!<br />Recharger la page pour prendre les modifications en compte.</div>';
	break;

}
}
?>
</div>
<script>
$(function() {
		  $('.nyroModal').nyroModal();
		  $.nmObj({sizes: { minW: 300, minH: 400 }});
		$( "#tabsup" ).tabs();
		var note=$('#slider-range-min').attr('rel');
		$( "#slider-range-min" ).slider({
			range: "min",
			value: note,
			min: 1,
			max: 10,
			slide: function( event, ui ) {
				$( "#amount" ).val( ui.value );
				$( "#inputamount" ).val( ui.value );
			}
		});
		$( "#amount" ).val( $( "#slider-range-min" ).slider( "value" ) );
		$( "#inputamount" ).val( $( "#slider-range-min" ).slider( "value" ));
	});
</script>
</body>
</html>
