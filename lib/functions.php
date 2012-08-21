<?php
require('config.php');
require('lang.php');
/**********************************************************************************************************

								========FONCTIONS========


 
**********************************************************************************************************/

function connect($pass, $basename){
$db = mysql_connect('localhost','root',$pass);
mysql_select_db($basename,$db);
}

function repertoire($dir){

$edir = str_replace('./video',home,$dir);
$edir = explode('/',$edir);

echo '<img src="images/home.png" alt="home"> ';
for ($i=0;$i<=(count($edir)-1);$i++) {
	if (empty($redir)) $slash='';
	else $slash='/';
	$redir = $redir.$slash.$edir[$i];
	$redir = str_replace(home,'./video',$redir);
	echo '<a href="?rep='.urlencode($redir).'">'.$edir[$i].'</a> / ';
	}
}

function login($user,$pass,$cookie,$port,$secure){
if($secure) $http = 'https://';
else $http = 'http://';
$urlSyno=$http.$_SERVER['SERVER_ADDR'].':'.$port.'/webman/login.cgi?username='.urlencode($user).'&passwd='.urlencode($pass);
$reponseLogin = file_get_contents($urlSyno);

if (json_decode($reponseLogin)->{'success'}){
	$_SESSION['user'] = $user;
		if($cookie == 'on'){
		$expire = 365*24*3600;
		setcookie('user',$user,time()+$expire);
		}
}
else echo '<div style="text-align:center;color:red;">Mauvais login/password</div>';
}

function logout(){
session_unset();
session_destroy();

setcookie('user');

}

function length($string,$maxlenght){
	if(strlen($string)>$maxlenght){
		$string = explode(" ",$string);
		$str = "";
		for($i=0;$i<count($string);$i++){
			if(strlen($str)<$maxlenght) $str .= $string[$i].' ';
			else break;
		}
	if ($i == count($string)) return $str;
	else return $str.'...';
	}
	else return $string;
}

function checklogin($cookie){
	if(!empty($cookie)){
	$_SESSION['user'] = $cookie;
	}
}

function stars($note){
$fullnote = $note;
$note = round($note*2)/2;
$full_star = intval($note);
if($note-round($note)==0) $half_star=false;
else $half_star=true;
$string = "";
for($i=1;$i<=5;$i++){
	if($i<=$full_star) $string .= '<img src="images/star_full.png" title="'.$fullnote.'" alt="'.$fullnote.'"> ';
	if($i>$full_star){
		if($half_star){
 			$string .= '<img src="images/star_half.png"  title="'.$fullnote.'" alt="'.$fullnote.'"> ';
 			$half_star = false;
 		}
	else $string .= '<img src="images/star_empty.png" title="'.$fullnote.'" alt="'.$fullnote.'"> ';
	}
}
return $string;
}


function resize($urlimg){
$taille = getimagesize($urlimg);
$x = $taille[0];
$y = $taille[1];
$xmax = 250;
if($x>$xmax){
$y = ($xmax*$y)/$x;
$x = $xmax;
}
echo '<img src="'.$urlimg.'" width="'.round($x,0).'" height="'.round($y,0).'" alt="Affiche">';
}

function keywordsAdapt($entry,$DELETED_WORDS,$index='0'){
	$entry = strtolower($entry);
	$entry = str_replace("_"," ",$entry);
	$entry = str_replace("-"," ",$entry);
	$entry = str_replace("."," ",$entry);
	$entry = str_replace("+"," ",$entry);
	for($i=0;$i<count($DELETED_WORDS);$i++){
	//$entry = str_replace($DELETED_WORDS[$i],"",$entry);
	$entry = preg_replace('#'.$DELETED_WORDS[$i].'.*#si','',$entry);
	}
	$entry = preg_replace('#\(.+\)#i','',$entry);
	$entry = preg_replace('#((19)[0-9]{2})|((200)[0-9]{1})|((201)[0-1]{1}).*#','',$entry); //supprime les annees 19xx a 2011
	$entry = ucfirst($entry);
	$entry = trim($entry);
	if ($index == '1'){
	$entry = preg_replace('#s[0-9]{1,}e[0-9]{1,}.+#i','',$entry); //supprime sxxexx et ce qui suit
	$entry = preg_replace('#[0-9]{1,2}x[0-9]{1,2}.+#i','',$entry);//supprime 6x02 et ce qui suit
	$entry = preg_replace('#cd[0-9]{1}#i','',$entry);
	$entry = preg_replace("#1080p.*#si","",$entry);
	$entry = preg_replace("#720p.*#si","",$entry);
	}
return $entry;
}

function is_serie($SERIES_DIR){
if(mb_ereg($SERIES_DIR,urldecode($_GET['rep']))) return true;
else return false;
}

function banner_serie(){
	$allo = new AlloCine();
	$infos = explode('/',urldecode($_GET['rep']));
	if(count($infos)>= 4){
		$name = $infos[3];
		$recherche = $allo->serieSearch(keywordsAdapt($name,$DELETED_WORDS,1));//recherche serie
		if (!empty($recherche['code'])){
		$id=$recherche['code'];
		$serie = $allo->serieInfos($recherche['code']);
		return $serie['topBanner'];
		}
	}
}


function fsize($file) {
  $fmod = filesize($file);
  if ($fmod < 0) $fmod += 2.0 * (PHP_INT_MAX + 1);

  $i = 0;

  $myfile = fopen($file, "r");
  
  while (strlen(fread($myfile, 1)) === 1) {
    fseek($myfile, PHP_INT_MAX, SEEK_CUR);
    $i++;
  }

  fclose($myfile);

  if ($i % 2 == 1) $i--;
  return ((float)($i) * (PHP_INT_MAX + 1)) + $fmod;
}

function rename_link($dir,$HIDDEN_FILES){
	$contenu = array();
	$realpath = $dir;
	if (!is_dir($realpath)) exit;
	if($handle = opendir($realpath)) {

		while (false !== ($file = readdir($handle))) {
			 if (!in_array($file, $HIDDEN_FILES)) $contenu[] = $file;	
	    }
	closedir($handle);
	}
	else echo "Echec ouverture repertoire". $dir;

	foreach($contenu as $contenu){

		if ($dir == 'all') $sql = "SELECT * FROM movies_tbl WHERE link='".$contenu."'";
		else $sql = "SELECT * FROM movies_tbl WHERE link='".$contenu."' AND dir='DivX'";
		$req = mysql_query($sql) or die ('Erreur SQL'.$sql.mysql_error());
		$data = mysql_fetch_array($req);
		if(!empty($data['imdbid']) and !ereg('serie',$data['imdbid'])){
				$extension = strtolower(pathinfo($contenu, PATHINFO_EXTENSION));
				$movie = new allocine();
				$infos = $movie->movieInfos($data['imdbid']);
				$titre = str_replace(" ",$infos['titre']).'.'.$extension;
				echo $titre.'<br>';
			
		}
	}
}

function login_check($login, $port, $secure){
	if($login){
		if(!empty($_COOKIE['user'])){
		$_SESSION['user'] = $_COOKIE['user'];
		}
	}
	if($_GET['action'] == 'logout') logout();
	if($_GET['action'] == 'login') login($_POST['user'],$_POST['pass'],$_POST['cookie'],$port,$secure);
}

function admin($root){
	if($_SESSION['user'] == 'admin') $root = true;
	else $root = false;
	return $root;
}

function rep($rep){
	if (empty($rep)) $dir='./video';
	elseif ($rep == '.' or $rep == './') $dir = './video';
	else $dir = addslashes($rep);
	return $dir;
}

function tri($sort){
	if (empty($sort)) $tri='name';
	else $tri = $sort;
	return $tri;
}

function php2js ($var) {
    if (is_array($var)) {
        $res = "[";
        $array = array();
        foreach ($var as $a_var) {
            $array[] = php2js($a_var);
        }
        return "[" . join(",", $array) . "]";
    }
    elseif (is_bool($var)) {
        return $var ? "true" : "false";
    }
    elseif (is_int($var) || is_integer($var) || is_double($var) || is_float($var)) {
        return $var;
    }
    elseif (is_string($var)) {
        return "\"" . addslashes(stripslashes($var)) . "\"";
    }
    // autres cas: objets, on ne les gère pas
    return FALSE;
}

function index_auto($dir,$HIDDEN_FILES,$ext,$SERIES_DIR){
	if(is_serie($SERIES_DIR)) $sql = "SELECT link FROM series WHERE dir='".$dir."' UNION SELECT link FROM errors WHERE dir='".$dir."' AND type='serie'";
	else $sql = "SELECT link FROM movies WHERE dir='".$dir."' UNION SELECT link FROM errors WHERE dir='".$dir."' AND type='movie'";
	$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
	$base_movies = array();
	$nonindexed = array();
	while($data = mysql_fetch_array($req)){
		$base_movies[] = stripslashes($data['link']);
	}
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, $HIDDEN_FILES)) {
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				if (in_array($extension, $ext)){
					if(!in_array($file,$base_movies)){
						$nonindexed[] = urlencode($file);
					}
					$files[] = $file;
				}
			}	
    	}
    closedir($handle);
	}
	else echo "Echec ouverture repertoire". $dir;
	$tot = count($nonindexed);
	echo '<script>';
	//echo '$("#empty").html("<div id=\"progressbar\" style=\"width:200px;\"></div>");';
	echo "\n";
	echo 'var tabnonindexed = '.php2js($nonindexed).';';
	echo "\n";
	echo 'if(tabnonindexed.length > 0){
			$("#indexing").html("<br>'.indexing.'<br><br><div id=\"progressbar\" style=\"width:200px;\"></div><span id=\"nbindex\"></span><div id=\"error\"></div>").show();
			//$("#empty").html("<div id=\"progressbar\" style=\"width:200px;\"></div>);
			'; 
	echo "\n";
	echo 'var i = 1;';
	echo "\n";
	echo '$.each(tabnonindexed, function(index, value){
			$.ajax({
  				type: "GET",
  				url: "lib/index_movie.php",
   				data: "rep='.urlencode($dir).'&link="+value,
   				error:function(msg){
     				alert( "Error ! : " + msg );
   				},
   				success:function(data){
					$( "#progressbar" ).progressbar({
						value: (((i)/'.$tot.')*100)
					});
					$(\'#nbindex\').html(Math.round((((i)/'.$tot.')*100))+\'%\');
					//document.write(data);
					if(data != \'\'){
						$(\'#error\').html(data);
					}
					if(i == '.$tot.'){
						location.reload();
					}
				i++;	
				}
				
		});
			
	}); }';
	echo '</script>';
	$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
	$nb_entree_bdd = mysql_num_rows($req); 
	if ($nb_entree_bdd > count($files)){ //si le nb d'entree de la table mysql > nb entree effectif du rep
		$i = 0;
		while ($data = mysql_fetch_array($req)){
			if (!in_array($data['link'],$files)){
				mysql_query('DELETE FROM movies WHERE link="'.addslashes($data['link']).'"') or die ('Erreur SQL : '.mysql_error());
				mysql_query("DELETE FROM movie_genre WHERE fk_id_movie = '".$data['id_movie']."'") or die ('Erreur SQL '.mysql_error());
				echo addslashes($data['link']).' mis a jour<br>';
				$i++;
			}
			if ($i == ($nb_entree_bdd-count($files))) break; //si on a atteint le nombre de fichier modifier on sort de la boucle
		}	
	}

}

function folders($dir,$HIDDEN_FILES){
$folders = array();
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, $HIDDEN_FILES)) {
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				if(empty($extension)){
					$folders[] = $file;
				}
			}	
    	}
    closedir($handle);
	}
	else echo 'Erreur ouverture repertoire '.$dir;
natcasesort($folders);
return $folders;
}

function check_files_folders($dir,$tri,$DELETED_WORDS,$ext,$HIDDEN_FILES,$SERIES_DIR){
	$sql = "SELECT * FROM movies WHERE dir='".$dir."' ORDER BY ".$tri;
	$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
	$nb_entree_bdd = mysql_num_rows($req); 
	$stocked_links = array();
	while ($data=mysql_fetch_array($req)){
		$stocked_links[] = $data['link'];
	}
	$files = array();
	$folders = array();
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, $HIDDEN_FILES)) {
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				if (in_array($extension, $ext)){
					if(!in_array($file,$stocked_links)){
						$taille = round(fsize($dir.'/'.$file)/1048576);
						index($file, $taille, $DELETED_WORDS,$SERIES_DIR);
					}
					$files[] = $file;//contient la liste des films
				}
				if(empty($extension)){
					$folders[] = $file;
				}
			}	
    	}
    closedir($handle);
	}
	else echo "Echec ouverture repertoire ".$dir;
	natcasesort($files); //trier les fichiers par ordre alphabetique
	$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
	$nb_entree_bdd = mysql_num_rows($req); 
	if ($nb_entree_bdd > count($files)){ //si le nb d'entree de la table mysql > nb entree effectif du rep
		$i = 0;
		while ($data = mysql_fetch_array($req)){
			if (!in_array($data['link'],$files)){
				mysql_query('DELETE FROM movies WHERE link="'.addslashes($data['link']).'"') or die ('Erreur SQL : '.mysql_error());
				mysql_query("DELETE FROM movie_genre WHERE fk_id_movie = '".$data['id_movie']."'") or die ('Erreur SQL '.mysql_error());
				echo addslashes($data['link']).' mis a jour<br>';
				$i++;
			}
			if ($i == ($nb_entree_bdd-count($files))) break; //si on a atteint le nombre de fichier modifier on sort de la boucle
		}	
	}
	natcasesort($folders);
	return $folders;
}

function listage($path,$HIDDEN_FILES,$ext)
{
        //On déclare le tableau qui contiendra tous les éléments de nos dossiers
        $tableau_elements = array();
 
        //On ouvre le dossier
        $dir = opendir($path);
 
        //Pour chaque élément du dossier...
        while (($element_dossier = readdir($dir)) !== FALSE)
        {
                //Si l'élément est lui-même un dossier (en excluant les dossiers parent et actuel), on appelle la fonction de listage en modifiant la racine du dossier à ouvrir
                if (!in_array($element_dossier,$HIDDEN_FILES) && is_dir($path.'/'.$element_dossier))
                {
                        //On fusionne ici le tableau grâce à la fonction array_merge. Au final, tous les résultats de nos appels récursifs à la fonction listage fusionneront dans le même tableau
                        $tableau_elements = array_merge($tableau_elements, listage($path.'/'.$element_dossier,$HIDDEN_FILES,$ext));
                }
                elseif ($element_dossier != '.' && $element_dossier != '..')
                {
                $extension = strtolower(pathinfo($element_dossier, PATHINFO_EXTENSION));
                        //Sinon, l'élément est un fichier : on l'enregistre dans le tableau
                  if(in_array($extension,$ext)) $tableau_elements[] = $path.'/'.$element_dossier;
                }
        }
        //On ferme le dossier
        closedir($dir);
 
        //On retourne le tableau
        return $tableau_elements;
}

function moviesinbase(){
	$sql = "SELECT link,dir FROM movies";
	$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
	$stocked_links = array();
	while ($data=mysql_fetch_array($req)){
		$stocked_links[] = $data['dir'].'/'.stripslashes($data['link']);
	}
	$sql = "SELECT link,dir FROM series";
	$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
	while ($data=mysql_fetch_array($req)){
		$stocked_links[] = $data['dir'].'/'.stripslashes($data['link']);
	}
	$sql = "SELECT link,dir FROM errors";
	$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
	while ($data=mysql_fetch_array($req)){
		$stocked_links[] = $data['dir'].'/'.stripslashes($data['link']);
	}
	
	return $stocked_links;
}

function random_string($car) {
$string = "";
$chaine = "abc1234567890";
srand((double)microtime()*1000000);
for($i=0; $i<$car; $i++) {
$string .= $chaine[rand()%strlen($chaine)];
}
return $string;
}

function reduire_image($source,$largeurmax,$hauteurmax,$tmpdir) //cree une miniature
{
	$a=strlen($source)-1;
	while(substr($source,$a,1)<>"/")
		$a=$a-1;
	$fichier=substr($source,$a+1);
	
	$destination=$tmpdir.$fichier;

		
		$caracteristiques = getimagesize($source);
		
		if(($caracteristiques[1]>$hauteurmax)or($caracteristiques[0]>$largeurmax))
			{
			if($caracteristiques[2] == "1")
				$depart = imagecreatefromgif($source);
			if($caracteristiques[2] == "2")
				$depart = imagecreatefromjpeg($source);
			if($caracteristiques[2] == "3")
				$depart = imagecreatefrompng($source);
		
			$h_i = $caracteristiques[1];
			$w_i = $caracteristiques[0];
			if($h_i >$hauteurmax)
				{
				$convert=$hauteurmax/$h_i;
				$h_i=$hauteurmax;
				$w_i=ceil($w_i*$convert);
				}
			if($w_i >$largeurmax)
				{
				$convert=$largeurmax/$w_i;
				$w_i=$largeurmax;
				$h_i=ceil($h_i*$convert);
				}
		 
			$arrivee = imagecreatetruecolor($w_i,$h_i);

			
			imagecopyresampled($arrivee,$depart, 0, 0, 0, 0, $w_i,$h_i,$caracteristiques[0], $caracteristiques[1]);
		
			imagepng($arrivee,$destination);
			}
		
		else
			$destination=$source;
		
	return($destination);
}

function countVideos(){
$sql = "SELECT COUNT(link) as nbMovies FROM movies";
$req = mysql_query($sql) or die (mysql_error());
$data1 = mysql_fetch_array($req);
$sql = "SELECT COUNT(link) as nbSeries FROM series";
$req = mysql_query($sql) or die (mysql_error());
$data2 = mysql_fetch_array($req);
$sql = "SELECT COUNT(*) as Wrong FROM (
SELECT link FROM movies WHERE id_movie='0'
UNION ALL
SELECT link FROM series WHERE id_serie='0-0-0'
) AS Wrong";
$req = mysql_query($sql) or die (mysql_error());
$data3 = mysql_fetch_array($req);
$sql = "SELECT COUNT(link) as nbError FROM errors";
$req = mysql_query($sql) or die (mysql_error());
$data4 = mysql_fetch_array($req);

$results = array(
'movies' => $data1['nbMovies'],
'series' => $data2['nbSeries'],
'wrong' => $data3['Wrong'],
'errors' => $data4['nbError']
);

return $results;
}

?>