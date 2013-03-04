<?php
require_once('config.php');
require_once('system_config.php');
require_once('functions.php');
require_once('TMDB4PHP/TMDB.php');

class TMDbnew{

private $tmdb = null;

//const ApiKey = '0a3956aaa5b4f99c5e2f2f1e044958db';
const ApiKey = '0d6040e3c69f939a3dd9b2cfdd9c8fa3';

public function __construct($lang='fr') {
    $this->tmdb = TMDBV3::getInstance($this::ApiKey);
    $this->tmdb->paged = false;
    $this->tmdb->language = $lang;
}

public function movieSearch($keywords) {
        $keywords = urlencode($keywords);
    
        $movies = $this->tmdb->search('movie', array('query'=>$keywords));   
        if (count($movies) == 0)
            return;

        $movie = reset($movies);
    	$annee = explode('-',$movie->release_date);

    	return array(
            	    'code'=>$movie->id,
    			    'titre'=>$movie->title,
    			    'titre-original'=>$movie->original_title,
    			    'annee'=>$annee[0],
    			    'note-public'=>$movie->vote_average,
    			    'affiche'=>$this->tmdb->image_url('poster', 'w185', $movie->poster())
    		    );
}
	
public function movieMultipleSearch($keywords,$count) {		
        $keywords = urlencode($keywords);
        $movies = $this->tmdb->search('movie', array('query'=>$keywords));   
        debug($movies);
        $result = array();
        $nbmovies = 0;
        while(($movie = array_shift($movies)) != NULL && $nbmovies < $count) {
            debug($movie);
            $nbmovies++;
            $annee = explode('-',$movie->release_date);
                
        	array_push($result, array(
            	'code'=>$movie->id,
    			'titre'=>$movie->title,
    			'titre-original'=>$movie->original_title,
    			'annee'=>$annee[0],
    			'note-public'=>$movie->vote_average,
    			'affiche'=>$this->tmdb->image_url('poster', 'w92', $movie->poster())
    		));
        }
    	return $result;		
}
	
public function movieInfos($id) {
    
        $infos = $this->tmdb->info('movie', $id);
        debug($infos);
        
        $movie = new Movie($id);
        $directors = $movie->director_list($id);
        $actors = $movie->cast_list($id);
        $trailer = $movie->trailer($id);
        debug($trailer);
        $annee = explode('-',$infos->release_date);
        $genres = "";
        
        foreach($infos->genres as $genre) {
            $genres .=   $genre->name . ",";  
        }
	    $result = array(
		    'titre-original'=>$infos->original_title,
		    'titre'=>$infos->title,
			'affiche'=>$this->tmdb->image_url('poster', 'w154', $infos->poster_path),
			'annee'=>$annee[0],
			'longueur'=>$infos->runtime,
			'note-public'=>$infos->vote_average,
			'nb-note-public'=>$infos->vote_count,
			'pays'=>'', //TODO
			'genres'=>$genres,
			'realisateur'=>$directors,
			'acteurs'=>$actors,
			'resume'=>$infos->overview,
			'bande-annonce'=>$trailer);
			
	
		return $result;

    
	}
}
