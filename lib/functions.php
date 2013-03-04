<?php
require_once('config.php');
require_once('system_config.php');
require_once('lang.php');

require_once('/volume1/web/lib/PhpConsole/PhpConsole.php');
PhpConsole::start(true, true, dirname(__FILE__));

$root = true;
$logFile = NULL;


function joinPath($dir, $file){
    return rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $file;
}

function al_is_serie($dir){
    global $SERIES_DIR;
    if(mb_ereg($SERIES_DIR, $dir)) 
        return true;
    else {
        // try to find serie.videostation
        $seriefile = $dir . '/serie.videostation';
        if (file_exists($seriefile))
            return true;
        return false;
    }
}


function openLogFile() {
    global $logFile;
    if ($logFile == NULL) {
        // open
        $dir = dirname(__FILE__);
        $logFile = fopen(dirname($dir)."/videostation.log", "a");
    }
    return $logFile;
}

function logError($msg){
    fwrite(openLogFile(), "ERR : " . $msg . "\n");
}

function logInfo($msg){
    fwrite(openLogFile(), "INFO : " . $msg . "\n");
}

function logWarn($msg){
    fwrite(openLogFile(), "WARNING : " . $msg . "\n");
}


function connect($host, $user, $pass, $basename){
    $db = mysql_connect($host,$user,$pass);
    if (!$db) {
        logError('Connexion impossible : ' . mysql_error());
        return false;
    }
    $db_selected = mysql_select_db($basename,$db);
    if (!$db_selected) {
      logError('Impossible de sélectionner la base de données : ' . mysql_error());
      return false;
    }
    return true;
}

function repertoire($dir){
    global $VIDEO_DIR;
    $edir = str_replace($VIDEO_DIR,'',$dir);
    $edir = explode('/',$edir);
    $redir = "";
    echo '<a href="?rep='.urlencode($VIDEO_DIR).'"><img src="images/home.png" alt="home"></a>' ;
    for ($i=0;$i<=(count($edir)-1);$i++) {
	    if (empty($redir)) 
            $slash='';
	    else 
            $slash='/';
	    $redir = $redir.$slash.$edir[$i];
	    $redir = str_replace(home,$VIDEO_DIR,$redir);
        if ($i == 0)
	        echo '<a style="font-weight:bold;margin-left:10px;" href="?rep='.urlencode(joinPath($VIDEO_DIR,$redir)).'">'.$edir[$i].'</a>';
        else
            echo '<a style="font-weight:bold;" href="?rep='.urlencode(joinPath($VIDEO_DIR,$redir)).'">/'.$edir[$i].'</a>';
	}
}

function length($string,$maxlenght){
	if(strlen($string)>$maxlenght){
		$string = explode(" ",$string);
		$str = "";
		for($i=0;$i<count($string);$i++){
			if(strlen($str)<$maxlenght) 
                $str .= $string[$i].' ';
			else 
                break;
		}
	    if ($i == count($string)) 
            return $str;
	    else 
            return $str.'...';
	}
	else 
        return $string;
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
    if($note-round($note)==0) 
        $half_star=false;
    else 
        $half_star=true;
    $string = "";
    for($i=1;$i<=5;$i++) {
	    if($i<=$full_star) 
            $string .= '<img src="images/star_full.png" title="'.$fullnote.'" alt="'.$fullnote.'"> ';
	    if($i>$full_star) {
		    if($half_star) {
 			    $string .= '<img src="images/star_half.png"  title="'.$fullnote.'" alt="'.$fullnote.'"> ';
 			    $half_star = false;
 		    } else 
                $string .= '<img src="images/star_empty.png" title="'.$fullnote.'" alt="'.$fullnote.'"> ';
	    }
    }
    return $string;
}


function savePoster($link, $img, $code)
{
    global $POSTER_WITH_VIDEO, $SERIES_DIR;
    // store to local dir
    copy($img,'../images/poster_small/'.$code.'.jpg');   
    $path_parts = pathinfo($link);
    $seriefile = $path_parts['dirname'] . '/serie.videostation';
    if (file_exists($seriefile) || al_is_serie($path_parts['dirname']))
        copy($img,'../images/poster_small/s-'.$code.'.jpg');
    if ($POSTER_WITH_VIDEO == TRUE) {
        // TODO : for series, only copy the first episode and make link to it for all other episodes
        copy('../images/poster_small/'.$code.'.jpg',$path_parts['dirname'] . '/' . $path_parts['filename'] . '.jpg');   
    }
    
}

function resize($urlimg){
    $taille = getimagesize($urlimg);
    $x = $taille[0];
    $y = $taille[1];
    $xmax = 250;
    if($x>$xmax) {
        $y = ($xmax*$y)/$x;
        $x = $xmax;
    }
    echo '<img src="'.$urlimg.'" width="'.round($x,0).'" height="'.round($y,0).'" alt="Affiche">';
}


function cleanFilename($dir, $link) {
    
    // remove links : find . -maxdepth 1 -lname '*' -exec rm {} \;
    global $DELETED_WORDS, $SAFE_MODE, $DRY_RUN;
    // generate uniqueid
    $uuid = uniqid ("", true);
    
    $path_parts = pathinfo($dir . '/' . $link);
    $newlink = keywordsAdapt($path_parts['filename'], $DELETED_WORDS);
    if ($newlink != $path_parts['filename']) { // name change
        if ($SAFE_MODE == TRUE) {
            // do not really rename the file but move it to a special hidden folder (folder videostation.safe with an ignore.videostation in it) and create a link to this file 
            @mkdir($dir.'/videostation.safe');
            @touch($dir.'/videostation.safe/ignore.videostation');
            
            // before operate, verify that there will be no errors : move ok, link ok
            if (file_exists($dir.'/videostation.safe/'.$link) || file_exists($dir .'/'.$newlink.'.'.$path_parts['extension'])) {
                // error : log error and return
                logError($dir.'/videostation.safe/'.$link . " or " . $dir .'/'.$newlink.'.'.$path_parts['extension'] . "exists!");
                return $link;
            }
            if (file_exists($dir.'/'.$path_parts['filename'].'.jpg') && (file_exists($dir.'/videostation.safe/'.$path_parts['filename'].'.jpg'  ) || file_exists($dir.'/'.$newlink.'.jpg'))) {
                // error : log error and return
                logError($dir.'/'.$path_parts['filename'].'.jpg' . ' or ' . $dir.'/videostation.safe/'.$path_parts['filename'].'.jpg' . ' or ' . $dir.'/'.$newlink.'.jpg' . "exists!");
                return $link;
            }
            // ACTION
            if ($DRY_RUN == FALSE) {
                rename($dir .'/'.$link, $dir.'/videostation.safe/'.$link); // = move
                symlink ( $dir.'/videostation.safe/'.$link , $dir .'/'.$newlink.'.'.$path_parts['extension'] );
            }
            // do the same with the jpeg file
            if ($DRY_RUN == FALSE && file_exists($dir.'/'.$path_parts['filename'].'.jpg')) {
                rename($dir.'/'.$path_parts['filename'].'.jpg', $dir.'/videostation.safe/'.$path_parts['filename'].'.jpg');
                symlink($dir.'/videostation.safe/'.$path_parts['filename'].'.jpg' , $dir.'/'.$newlink.'.jpg' );
            }
        } else {
            // rename
            if (file_exists($dir . '/' . $newlink . '.' .$path_parts['extension'])) {
                // error : log error and return
                return $link;
            } 
            if (file_exists($dir.'/'.$path_parts['filename'].'.jpg') && file_exists($dir.'/'.$newlink.'.jpg')) {
                // error : log error and return
                return $link;
            }
            
            if ($DRY_RUN == FALSE) {
                rename($dir . '/' . $link, $dir . '/' . $newlink . '.' .$path_parts['extension']);
            }
            // do the same with the jpeg file
            if (file_exists($dir.'/'.$path_parts['filename'].'.jpg')) {            
                if ($DRY_RUN == FALSE) {
                    rename($dir.'/'.$path_parts['filename'].'.jpg', $dir.'/'.$newlink.'.jpg');
                }
            }
        }


        if (false) { // TODO Be gentle to rename all existing files with the same $path_parts['filename']
            
            $all = glob($dir . '/' . $path_parts['filename'] . '.*');
            foreach ($all as $samefilename) {
                echo "$samefilename size " . filesize($samefilename) . "\n";
                // with a full path here : split
                // if not the original file : rename and link if in safe mod
            }
        }
    }
    if ($DRY_RUN == FALSE) {
        return $newlink . '.' . $path_parts['extension'];
    } else {
        return $link;
    }
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


function ignore_dir($dir) {
//    logError('ignore_dir test : '.$dir);
    if (is_dir($dir) == FALSE) {
        return false;
    }
    // try to find ignore.videostation under this dir
    $ignorefile = $dir . '/ignore.videostation';
    if (file_exists($ignorefile))
        return true;
    return false;
}

/*
function is_serie($SERIES_DIR){
    if(mb_ereg($SERIES_DIR,urldecode($_GET['rep']))) 
        return true;
    else 
        return false;
}
*/

function get_serie_code_infos($dir) {
    $sql = "SELECT * FROM series_id WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."'";
	$req = mysql_query($sql) or die ('Erreur SQL'.$sql.mysql_error());
    if (mysql_num_rows($req) == 1) {
        $data = mysql_fetch_array($req);
        return $data;
    }
    $data = array();
    return $data;
}

function set_serie_code($dir, $code) {
    global $SERIES_DATABASE;
	$sql = "INSERT INTO series_id VALUES('".$code."','" . mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR)) . "','".$SERIES_DATABASE."')";
	mysql_query ($sql) or die('1.Erreur SQL !'.$sql.'<br>'.mysql_error());
}


function banner_serie($dir){
    global $DELETED_WORDS, $SERIE_WITH_BANNER, $SERIES_DIR;
    if ($SERIE_WITH_BANNER == FALSE)
        return;
    $serieDir = explode('/', rtrim($SERIES_DIR, DIRECTORY_SEPARATOR));
	$infos = explode('/', rtrim($dir, DIRECTORY_SEPARATOR));
    $infos = array_reverse($infos);
    if (count($infos) - count($serieDir) == 1)
        {
        // try to get serie code from DDB
        $serie_infos = get_serie_code_infos($dir);
        if (!empty($serie_infos)) {
            $localBanner = "./images/poster_small/banner_".$serie_infos['id_serie'].".jpg";
            if (file_exists($localBanner))
                return $localBanner;
            
        } else
        {
            $name = $infos[0];
            $allo = new AlloCine();
            $recherche = $allo->serieSearch(keywordsAdapt($name,$DELETED_WORDS,1));//recherche serie
	        if (!empty($recherche['code'])){
	            $id=$recherche['code'];
	            $serie = $allo->serieInfos($id);
                set_serie_code($dir, $id);
                $localBanner = "./images/poster_small/banner_".$id.".jpg";
                if (!empty($serie['topBanner'])) {
                    copy($serie['topBanner'],$localBanner);   
                }
	            return $serie['topBanner'];
	        }
        }
    }
}


function fsize($file) {
    $fmod = filesize($file);
    if ($fmod < 0) 
        $fmod += 2.0 * (PHP_INT_MAX + 1);

    $i = 0;

    $myfile = fopen($file, "r");
  
    while (strlen(fread($myfile, 1)) === 1) {
        fseek($myfile, PHP_INT_MAX, SEEK_CUR);
        $i++;
    }

    fclose($myfile);

    if ($i % 2 == 1) 
        $i--;

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

function login_check($login, $secure){
	if($login){
		if(!empty($_COOKIE['user'])){
		$_SESSION['user'] = $_COOKIE['user'];
		}
	}
//	if($_GET["action"] == 'logout') logout();
//	if($_GET["action"] == 'login') login($_POST['user'],$_POST['pass'],$_POST['cookie'],$port,$secure);
}

function admin($root){
	if($_SESSION['user'] == 'admin') 
        $root = true;
	else 
        $root = false;
	return $root;
}

function rep($rep){
    global $VIDEO_DIR;
	if (empty($rep)) {
        $dir=$VIDEO_DIR;
	} else {
        if ($rep == '.' or $rep == './') 
            $dir = $VIDEO_DIR;
	    else {
//$$AL$$            $dir = addslashes($rep);
            $dir = $rep;
	    }
	}
    return $dir; 
}

function tri($sort){
	if (empty($sort)) 
        $tri='name';
	else 
        $tri = $sort;
        
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


function scanFileNameRecursivly($path, &$dirArray, &$fileArray, $base_movies)
{
    global $EXT, $HIDDEN_FILES;
	$lists = @scandir($path);
	if(!empty($lists)) {
		foreach($lists as $f) {
            $dir = joinPath($path,$f);
//            logInfo("scanFileNameRecursivly : file : " . $dir);
			if(is_dir($dir)) {
                if(!in_array($f, $HIDDEN_FILES)) {
                    if (ignore_dir($dir) == false) {
                        $dirArray[]  = $dir; 
				        scanFileNameRecursivly($dir, $dirArray, $fileArray, $base_movies); 
			        } 
                }
			} else {
    			$extension = strtolower(pathinfo($f, PATHINFO_EXTENSION));
				if (in_array($extension, $EXT)) {
    				if(!in_array($dir,$base_movies)){
					    $fileArray[] = urlencode($dir);
    				}
				}
			}
		}
	}
}

function index_all() {
    global $HIDDEN_FILES,$ext,$SERIES_DIR, $EXT, $VIDEO_DIR;
    $dirArray = array();
    $fileArray = array();
    $dirArray[] = $VIDEO_DIR;
    $sql = "SELECT link,dir FROM series WHERE 1 UNION SELECT link,dir FROM errors WHERE 1 UNION SELECT link,dir FROM movies WHERE 1" ;
    $req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
    $base_movies = array();
	while($data = mysql_fetch_array($req)){
		$base_movies[] = joinPath(stripslashes($data['dir']), stripslashes($data['link']));
	}

    scanFileNameRecursivly($VIDEO_DIR, $dirArray, $fileArray, $base_movies );
    logInfo("index_all - File count : " .  count($fileArray));
    $tot = count($fileArray);
	echo '<script>';
	//echo '$("#empty").html("<div id=\"progressbar\" style=\"width:200px;\"></div>");';
	echo "\n";
	echo 'var tabnonindexed = '.php2js($fileArray).';';
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
   				data: "link="+value,
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
                        console.log(data);
						$(\'#error\').html(data);
					}
					if(i == '.$tot.'){
						location.replace("index.php");
					}
				i++;	
				}
				
		});
			
	}); }';
	echo '</script>';
}

function index_auto($dir,$HIDDEN_FILES,$ext,$SERIES_DIR, $force_index = 0){
    
    $base_movies = array();
	$nonindexed = array();
    if ($force_index == 1) {
        if(al_is_serie($dir)) {
            $sql =  "SELECT link FROM series WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."' UNION SELECT link FROM errors WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."' AND type='serie'";
            $sql1 = "DELETE FROM series WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."'";
            $sql2 = "DELETE FROM errors WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."' AND type='serie'";
        } else {
            $sql = "SELECT link FROM movies WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."' UNION SELECT link FROM errors WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."' AND type='movie'";
            $sql1 = "DELETE FROM movies WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."'";
            $sql2 = "DELETE FROM errors WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."' AND type='movie'";
        }
        try {
            $req = mysql_query($sql1);
            $req = mysql_query($sql2);
        }catch (Exception $e) {
            debug("SQL Exception : " . $e->getMessage());
            logError("SQL Exception : " . $e->getMessage());
            return;
        }
    } else {
        if(al_is_serie($dir)) 
            $sql = "SELECT link FROM series WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."' UNION SELECT link FROM errors WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."' AND type='serie'";
    	else 
            $sql = "SELECT link FROM movies WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."' UNION SELECT link FROM errors WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."' AND type='movie'";
    	$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
    	while($data = mysql_fetch_array($req)){
    		$base_movies[] = stripslashes($data['link']);
    	}
    }
    $files = array();
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, $HIDDEN_FILES)) {
				$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
				if (in_array($extension, $ext)){
					if(!in_array($file,$base_movies)){
                        debug("index_auto loop non indexed : " . $file);
						$nonindexed[] = urlencode($file);
					}
					$files[] = urlencode($file);
				}
			}	
    	}
    closedir($handle);
	}
	else {
        debug ("Echec ouverture repertoire". $dir);
        echo "Echec ouverture repertoire". $dir;
	}
    
    
    
	echo '<script>';
	//echo '$("#empty").html("<div id=\"progressbar\" style=\"width:200px;\"></div>");';
	echo "\n";
    $tot = count($nonindexed);
//    debug("auto_index - non indexed : " . $tot );
//    debug("auto_index - all files  : " . count($files) );
    
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
   				data: "rep='.urlencode($dir).'&link="+value+"&force='.$force_index.'",
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
                        console.log(data);
						$(\'#error\').html(data);
					}
					if(i == '.$tot.'){
    					location.replace("index.php?rep='.urlencode($dir).'");
//						location.reload();
					}
				i++;	
				}
				
		});
			
	}); }';
	echo '</script>';
    if ($force_index == 0) {
    	$req = mysql_query($sql) or die ('Erreur SQL : '.mysql_error());
    	$nb_entree_bdd = mysql_num_rows($req); 
//        debug("auto_index nb_entree_bdd " . $nb_entree_bdd);
//        debug("auto_index count files " . count($files));
    	if ($nb_entree_bdd > count($files)){ //si le nb d'entree de la table mysql > nb entree effectif du rep
    		$i = 0;
    		while ($data = mysql_fetch_array($req)){
    			if (!in_array($data['link'],$files)){
                    debug("index_auto 3 " . $data['link']);
    				mysql_query('DELETE FROM movies WHERE link="'.addslashes($data['link']).'"') or die ('Erreur SQL : '.mysql_error());
    				mysql_query("DELETE FROM movie_genre WHERE fk_id_movie = '".$data['id_movie']."'") or die ('Erreur SQL '.mysql_error());
    				echo addslashes($data['link']).' mis a jour<br>';
    				$i++;
    			}
    			if ($i == ($nb_entree_bdd-count($files))) break; //si on a atteint le nombre de fichier modifier on sort de la boucle
    		}	
    	}
    }
}

function folders($dir,$HIDDEN_FILES){
	$folders = array();
    $dir = str_replace("\\", "", $dir);
	if ($handle = opendir($dir)) {
		while (false !== ($file = readdir($handle))) {
			if (!in_array($file, $HIDDEN_FILES) && is_dir($dir.'/'.$file)) {
				if(ignore_dir(joinPath($dir,$file)) == false){
					$folders[] = $file;
				} else {
                    logInfo(joinPath($dir,$file) . " skipped for ignore rule!");
				}
			}
    	}
        closedir($handle);
	} else {
        logError('Erreur ouverture repertoire '.$dir);
        echo 'Erreur ouverture repertoire '.$dir;
	}
    natcasesort($folders);
    return $folders;
}

function check_files_folders($dir,$tri,$DELETED_WORDS,$ext,$HIDDEN_FILES,$SERIES_DIR){
	$sql = "SELECT * FROM movies WHERE dir='".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."' ORDER BY ".$tri;
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
						$taille = round(fsize(joinPath($dir,$file))/1048576);
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
	else 
        echo "Echec ouverture repertoire ".$dir;
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
                if (!in_array($element_dossier,$HIDDEN_FILES) && is_dir(joinPath($path,$element_dossier)))
                {
                        //On fusionne ici le tableau grâce à la fonction array_merge. Au final, tous les résultats de nos appels récursifs à la fonction listage fusionneront dans le même tableau
                        $tableau_elements = array_merge($tableau_elements, listage($path.'/'.$element_dossier,$HIDDEN_FILES,$ext));
                }
                elseif ($element_dossier != '.' && $element_dossier != '..')
                {
                $extension = strtolower(pathinfo($element_dossier, PATHINFO_EXTENSION));
                        //Sinon, l'élément est un fichier : on l'enregistre dans le tableau
                  if(in_array($extension,$ext)) $tableau_elements[] = joinPath($path,$element_dossier);
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