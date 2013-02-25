<?php

require_once('config.php');
require_once('system_config.php');
require_once('API-allocine.php');
require_once('API-TMDb.php');
require_once('functions.php');
require_once('/volume1/web/lib/PhpConsole/PhpConsole.php');
PhpConsole::start(true, true, dirname(__FILE__));



function index($dir, $link) {
	if(al_is_serie($dir)){
		series($dir, $link);
	}
	else {
		movies($dir, $link);
	}
}


function movies($dir, $link) {
    global $MOVIES_DATABASE, $LANGUAGE, $DELETED_WORDS, $ALWAYS_UPDATE, $USER_SQL,$PASSWORD_SQL,$DATABASE, $CLEAN_AND_RENAME_MOVIES, $HOST_SQL;
    //$$AL$$
    if ($CLEAN_AND_RENAME_MOVIES) {
        $link = cleanFilename($dir, $link);
    }
    
	connect($HOST_SQL, $USER_SQL,$PASSWORD_SQL,$DATABASE);

    $sql = mysql_query("SELECT link FROM movies WHERE link='".mysql_real_escape_string($link)."' AND dir='" . mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR)) . "'");
    $data=mysql_fetch_array($sql);
	if(!empty($data['link'])) {
		if ($ALWAYS_UPDATE == TRUE)
			$sql = mysql_query("DELETE FROM movies WHERE link='".mysql_real_escape_string($link)."' AND dir='" . mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR)) . "'");
		else {
			echo joinPath($dir,$link) . " Already indexed";
			return;
		}
	}
	
    $size = 0;
    try{
    	switch($MOVIES_DATABASE){
    		case 'Allocine':
    		$movies = new Allocine($LANGUAGE);
    		break;
    		
    		case 'TMDb':
    		$movies = new TMDb($LANGUAGE);
    		break;
    	}
    	$recherche = $movies->movieSearch(keywordsAdapt($link,$DELETED_WORDS,1));
    	if(empty($recherche['code'])) { //si aucun film trouve
    		$sql = "INSERT INTO movies VALUES(
    		'0',
    		\"".addslashes($link)."\",
    		\"".keywordsAdapt(addslashes($link), $DELETED_WORDS)."\",
    		'0',
    		'0',
    		'0',
    		'0',
    		'0',
    		'0',
    		'0',
    		'0',
    		'0',
    		'0',
    		'0',
        	'0',
    		\"".mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR))."\",
    		\"".$MOVIES_DATABASE."\")";
    		mysql_query ($sql) or die('5.Erreur SQL !'.$sql.'<br>'.mysql_error());
    	}
    	else { //si film trouve 
    		if(!empty($recherche['affiche'])){
    			//echo $recherche['affiche'];
    			if($MOVIES_DATABASE == 'Allocine'){
    			    $img = explode('/',$recherche['affiche']);
    			    $end_url = '';
    			    for($j=3;$j<count($img);$j++){
    				    $end_url = $end_url.'/'.$img[$j];
    			    }
    			    $img = $img[0].'//'.$img[2].'/r_150_204'.$end_url;
                    savePoster($dir.'/'.$link, $img, $recherche['code']);
    			    // copy($img,'../images/poster_small/'.$recherche['code'].'.jpg');
    			}
    			elseif($MOVIES_DATABASE == 'TMDb'){
    			    // copy($recherche['affiche'],'../images/poster_small/'.$recherche['code'].'.jpg');
                    savePoster($dir.'/'.$link, $recherche['affiche'], $recherche['code']);
    			}
    		}
    		$infos = $movies->movieInfos($recherche['code']);
    		if(empty($infos['bande-annonce']) and $MOVIES_DATABASE == 'TMDb'){
    			$movie_en = new TMDb('en');
    			$trailer = $movie_en->movieInfos($recherche['code']);
    			$infos['bande-annonce'] = $trailer['bande-annonce'];
    		}
    		$sql = sprintf("INSERT INTO movies VALUES ('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%f','%d','%s','%f','%s','%s')",
    		mysql_real_escape_string($recherche['code']),
    		mysql_real_escape_string($link),
    		mysql_real_escape_string($recherche['titre']),
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
    		mysql_real_escape_string($recherche['annee']),
    		$size,
    		mysql_real_escape_string(rtrim($dir, DIRECTORY_SEPARATOR)),
    		mysql_real_escape_string($MOVIES_DATABASE));
    		
    		if(!mysql_query($sql)){
    			throw new Exception('SQL Error');
    		} 		
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
    				$sql = "INSERT INTO genres VALUES ('','".addslashes($gnre)."')";
    				mysql_query($sql) or die('6.Erreur SQL !'.$sql.'<br>'.mysql_error());
    			}
    			if($gnre != ''){
    			$sql = "SELECT id_genre FROM genres WHERE name='".$gnre."'";
    			$req = mysql_query($sql) or die ('7.Erreur SQL : '.mysql_error());
    			$data = mysql_fetch_array($req);
    			$insert = "INSERT INTO movie_genre VALUES('','".$data['id_genre']."','".$recherche['code']."')";
    			mysql_query($insert) or die ('8.Erreur SQL : '.mysql_error());
    			}
    		}
    	}
    	mysql_query("DELETE FROM errors WHERE link='".mysql_real_escape_string($link)."'") or die ('9.Erreur SQL :'.mysql_error());
	    //echo $link.' indexe en tant que '.$recherche['titre'];
    }
    catch(Exception $e) {
    	echo 'Error during indexing '.$link.' :';
    	echo $e->getMessage();
    	$sql = "INSERT INTO errors VALUES('','".htmlspecialchars(addslashes($link))."','".addslashes($dir)."','".$e->getMessage()."','','movie')";
    	mysql_query($sql) or die('10.Erreur SQL : '.mysql_error());
    }    
}


function series($dir, $link) {
    global $SERIES_DATABASE, $LANGUAGE, $DELETED_WORDS, $USER_SQL,$PASSWORD_SQL,$DATABASE, $HOST_SQL;
	
	connect($HOST_SQL, $USER_SQL,$PASSWORD_SQL,$DATABASE);

	$sql = mysql_query("SELECT link FROM series WHERE link='".mysql_real_escape_string($link)."' AND dir='" . mysql_real_escape_string($dir) . "'");
	$data=mysql_fetch_array($sql);
	if(!empty($data['link'])) {
		if ($ALWAYS_UPDATE == TRUE)
			$sql = mysql_query("DELETE FROM series WHERE link='".mysql_real_escape_string($link)."' AND dir='" . mysql_real_escape_string($dir) . "'");
		else {
			echo $dir.DIRECTORY_SEPARATOR.$link . " Already indexed";
            logInfo($dir.DIRECTORY_SEPARATOR.$link . " Already indexed");
			return;
		}
	}
	
    switch($SERIES_DATABASE){
        case 'Allocine':
    	    $series = new Allocine($LANGUAGE);
    	    break;
    	case 'TheTvDb':
    	    $series = new TheTvDb();
    	    break;
    }
	$infos = explode('/',$dir);
	$infos = array_reverse($infos);
	$matches=array();
	preg_match('#[0-9]{1,2}#',$infos[0],$matches);
	$nbSeason = intval($matches[0]);
	$recherche = $series->serieSearch(keywordsAdapt($infos[1],$DELETED_WORDS,1));//recherche serie
	if (empty($recherche['code'])){
		$sql = "INSERT INTO series VALUES('0','0','0','".addslashes($link)."','','','','','','','','','','','','','".$dir."','".$SERIES_DATABASE."')";
		mysql_query ($sql) or die('1.Erreur SQL !'.$sql.'<br>'.mysql_error());
	}
	else{
		$id=$recherche['code'];
		$serie = $series->serieInfos($recherche['code']);//infos serie
		if (empty($serie['code'])){
			echo 'Erreur';
		}
		if(!empty($serie['affiche'])){
			//echo $recherche['affiche'];
			$img = explode('/',$serie['affiche']);
			$end_url = '';
			for($j=3;$j<count($img);$j++){
				$end_url = $end_url.'/'.$img[$j];
			}
			$img = $img[0].'//'.$img[2].'/r_150_204'.$end_url;
//			copy($img,'../images/poster_small/s-'.$serie['code'].'.jpg');
            savePoster($dir.'/'.$link, $img,$serie['code']);
		}
		$code_season = $serie['tabSaisons'][$nbSeason];
		$id.='-'.$code_season;
		$season = $series->seasonInfos($code_season);//infos saison
		if (empty($season['code'])) {
			$sql = "INSERT INTO series VALUES(
			'".$recherche['code']."',
			'0',
			'0',
			'".htmlspecialchars(addslashes($link))."',
			'".addslashes($link)."',
			'".addslashes($link)."',
			'0',
			'0',
			'0',
			'0',
			'0',
			'',
			'0',
			'0',
			'0',
			'',
			'".$dir."',
			'".$SERIES_DATABASE."'
			)";
			mysql_query ($sql) or die('2.Erreur SQL !'.$sql.'<br>'.mysql_error());
		}
		else {
			preg_match('#e[0-9]{1,2}#i',keywordsAdapt(addslashes($link), $DELETED_WORDS),$matches);
			if (empty($matches[0])){
				preg_match('#x[0-9]{1,2}#i',keywordsAdapt(addslashes($link), $DELETED_WORDS),$matches);
			}
			if (empty($matches[0])){
				preg_match('#[0-9]{4}#',keywordsAdapt(addslashes($link), $DELETED_WORDS),$matches);
                if (isset($matches[0]))
	    			$matches[0] = $matches[0]{2}.$matches[0]{3};
			}
			if (empty($matches[0])){
				preg_match('#[0-9]{3}#',keywordsAdapt(addslashes($link), $DELETED_WORDS),$matches);
                if (isset($matches[0]))
    				$matches[0] = $matches[0]{1}.$matches[0]{2};
			}
            if (empty($matches[0]))
                {
        		$sql = "INSERT INTO series VALUES(
    			'".$recherche['code']."',
    			'0',
    			'0',
    			'".htmlspecialchars(addslashes($link))."',
    			'".addslashes($link)."',
    			'".addslashes($link)."',
    			'0',
    			'0',
    			'0',
    			'0',
    			'0',
    			'',
    			'0',
    			'0',
    			'0',
    			'',
    			'".$dir."',
    			'".$SERIES_DATABASE."'
    			)";
    			mysql_query ($sql) or die('3.Erreur SQL !'.$sql.'<br>'.mysql_error());
                logError("Serie filename format error");
                die("Serie filename format error");
                }
                
			$matches[0] = str_replace('e','',$matches[0]);
			$matches[0] = str_replace('E','',$matches[0]);
			$matches[0] = str_replace('x','',$matches[0]);
			$matches[0] = str_replace('X','',$matches[0]);
			if((10-$matches[0])>0) $matches[0] = str_replace('0','',$matches[0]);
			$nbEpisode = $matches[0];
			$code_episode = $season['tabEpisode'][$nbEpisode];
			$id.='-'.$code_episode;
			$episode = $series->episodeInfos($code_episode);//infos episode
			if (empty($episode['code'])) {
				echo 'Error : no code for episode';
                logError('Error : no code for episode');
			}
			if (strlen($nbSeason) == 1) $nbSeason = '0'.$nbSeason;
			if (strlen($nbEpisode) == 1) $nbEpisode = '0'.$nbEpisode;
			$sql = sprintf("INSERT INTO series VALUES ('%d','%d','%d','%s','%s','%s','%s','%d','%d','%f','%s','%s','%s','%s','%s','%s','%s','%s')",
			$recherche['code'],
			$season['code'],
			$code_episode,
			mysql_real_escape_string($link),
			mysql_real_escape_string($recherche['titre']),
			mysql_real_escape_string('[S'.$nbSeason.'E'.$nbEpisode.']'.$episode['titre']),
			mysql_real_escape_string($episode['titre-original']),
			$nbSeason,
			$nbEpisode,
			$episode['note-public'],
			mysql_real_escape_string($recherche['acteurs']),
			'',
			empty($recherche['longueur']) ? 0 : $recherche['longueur'], mysql_real_escape_string(   $episode['resume']),
        			                            $recherche['affiche'], '', mysql_real_escape_string($dir),
			$SERIES_DATABASE);
			mysql_query ($sql) or die('4.Erreur SQL !'.$sql.'<br>'.mysql_error());
		//echo $link.' indexe en tant que serie';
		}
	}    
    
}    



?>