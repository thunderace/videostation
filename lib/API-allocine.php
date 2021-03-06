<?php
/*****************************************************************************


				=================== class allocine ======================
				
00 Rechercher un film :

	$movie = new allocine();
	$recherche = $movie->movieSearch('titre du film'); (retourne seulement un film, le plus plausible en fonction du titre)
	$recherche['code'] 			: id du film
	$recherche['titre']			: titre du film
	$recherche['annee']			: année de production
	$recherche['acteurs']		: acteurs du film
	$recherche['directeur']		: réalisateur du film
	$recherche['note-presse']	: note de la presse
	$recherche['note-public']	: note du public
	$recherche['affiche']		: url de l'affiche du film

00 Récupérer les informations du film :

	$movie = new allocine();
	$infos = $movie->movieInfos('id allocine);
	$infos['titre-original']
	$infos['titre']
	$infos['affiche']
	$infos['annee']
	$infos['longueur']
	$infos['note-press']
	$infos['note-public']
	$infos['nb-note-press']
	$infos['nb-note-public']
	$infos['pays']
	$infos['genres']
	$infos['realisateur']
	$infos['acteurs']
	$infos['resume']
	$infos['bande-annonce']

******************************************************************************/

class AlloCine{

public $url, $content, $data, $result;
private $Options = array(
				'partner'=>'YW5kcm9pZC12M3M',
				'filter'=>'movie',
				'format'=>'json',
				'count'=>'1',
				'profile'=>'medium',
				'version'=>'2');
				
				
public function __construct($lang='fr') {
	$this->Lang = $lang;
	$this->url_site_lang = $this->url_site($lang);
}

const UrlSearch = '/rest/v3/search?';
const UrlMovie = '/rest/v3/movie?';
const UrlSerie = '/rest/v3/tvseries?';
const UrlSeason = '/rest/v3/season?';
const UrlEpisode = '/rest/v3/episode?';


public function movieSearch($keywords) {

		$keywords = urlencode($keywords);
		
		$this->content = @file_get_contents($this->get_url($keywords));
		debug("allocine movieSearch " . $this->get_url($keywords));
		//$this->content = false;
		if($this->content === false){
			throw new Exception('Failed to open stream : '.$this->get_url($keywords));
		}
		$this->data = json_decode($this->content,true);
		if ($this->data['feed']['totalResults'] == 0)
			return;
		
		if (empty($this->data['feed']['movie']['0']['originalTitle'])) 
			$originalTitle = "";
		else 
			$originalTitle = $this->data['feed']['movie']['0']['originalTitle'];			

		if (empty($this->data['feed']['movie']['0']['title'])) 
			$title = $originalTitle;
		else 
			$title = $this->data['feed']['movie']['0']['title'];

		$this->result = array(
			'code'=>$this->data['feed']['movie']['0']['code'],
			'titre'=>$title,
			'titre-original'=>$originalTitle,
			'annee'=>$this->data['feed']['movie']['0']['productionYear'],
			'acteurs'=>$this->data['feed']['movie']['0']['castingShort']['actors'],
			'realisateur'=>$this->data['feed']['movie']['0']['castingShort']['directors'],
			'note-presse'=>round($this->data['feed']['movie']['0']['statistics']['pressRating'],1),
			'note-public'=>round($this->data['feed']['movie']['0']['statistics']['userRating'],1),
			'affiche'=>$this->data['feed']['movie']['0']['poster']['href']);
		//$this->browseArray($this->data);
		return $this->result;		
	}

public function movieInfos($id){
		$this->url = $this->url_site_lang.$this::UrlMovie.'partner='.$this->Options['partner'].'&code='.$id.'&profile='.$this->Options['profile'].'&format='.$this->Options['format'];
		//$this->url = $this::UrlMovie.'code='.$id.'&json=1&partner='.$this->Options['partner'].'&profile='.$this->Options['profile'];
		//echo $this->url;
		$this->content = file_get_contents($this->url);
		if($this->content === false){
			throw new Exception('Failed to catch movie infos : '.$this->url);
		}
		$this->data = json_decode($this->content,true);
		unset($this->data['movie']['castMember']);
	
        $nb = -1;
        $nationality = "";
        if (!empty($this->data['movie']['nationality']))
		    $nb = count($this->data['movie']['nationality'])-1;

		for($i=0; $i<=$nb; $i++){
			$nationality = $nationality.$this->data['movie']['nationality'][$i]['$'];
				if ($i != $nb) $nationality = $nationality.', ';
		}	
		$this->data['movie']['nationality'] = $nationality;

        $nb = -1;
        $genre = "";

        if (!empty($this->data['movie']['genre']))
		    $nb = count($this->data['movie']['genre'])-1;

        for($i=0; $i<=$nb; $i++){
			$genre = $genre.$this->data['movie']['genre'][$i]['$'];
				if ($i != $nb) $genre = $genre.', ';
		}
		$this->data['movie']['genre'] = $genre;
	
        if (empty($this->data['movie']['runtime'])) {
            $time[0] = 0;
            $time[1] = 0;
        }
        else {
        	$time[0] = floor($this->data['movie']['runtime']/3600);//heure
		    $time[1] = (($this->data['movie']['runtime']/3600)-$time[0])*60;//minutes
        }
	
		$this->data['movie']['runtime'] = $time[0].'h'.$time[1].'min';
        
    	if (empty($this->data['movie']['castingShort']['actors'])) 
            $actors = "";
		else 
            $actors = $this->data['movie']['castingShort']['actors'];
        
    	if (empty($this->data['movie']['castingShort']['directors'])) 
            $directors = "";
        else 
            $directors = $this->data['movie']['castingShort']['directors'];

        if (empty($this->data['movie']['statistics']['pressRating'])) 
            $pressRating = "";
        else 
            $pressRating = $this->data['movie']['statistics']['pressRating'];

        if (empty($this->data['movie']['statistics']['userRating'])) 
            $userRating = "";
        else 
            $userRating = $this->data['movie']['statistics']['userRating'];

        if (empty($this->data['movie']['poster']['href'])) 
            $poster = "";
        else 
            $poster = $this->data['movie']['poster']['href'];

        if (empty($this->data['movie']['originalTitle'])) 
            $originalTitle = "";
        else 
            $originalTitle = $this->data['movie']['originalTitle'];

        if (empty($this->data['movie']['productionYear'])) 
            $productionYear = "";
        else 
            $productionYear = $this->data['movie']['productionYear'];
                
        if (empty($this->data['movie']['synopsis'])) 
            $synopsis = "";
        else 
            $synopsis = $this->data['movie']['synopsis'];

		if (empty($this->data['movie']['title'])) 
            $title = $this->data['movie']['originalTitle'];
		else 
            $title = $this->data['movie']['title'];

    	if (empty($this->data['movie']['statistics']['pressReviewCount'])) 
            $pressReviewCount = "";
		else 
            $pressReviewCount = $this->data['movie']['statistics']['pressReviewCount'];
        
        if (empty($this->data['movie']['statistics']['userReviewCount'])) 
            $userReviewCount = "";
		else 
            $userReviewCount = $this->data['movie']['statistics']['userReviewCount'];

        if (empty($this->data['movie']['trailer']['href'])) 
            $trailer = "";
    	else 
            $trailer = $this->data['movie']['trailer']['href'];


		$this->result = array(
			'titre-original'=>$originalTitle,
			'titre'=>$title,
			'affiche'=>$poster,
			'annee'=>$productionYear,
			'longueur'=>$this->data['movie']['runtime'],
			'note-press'=>$pressRating,
			'note-public'=>$userRating,
			'nb-note-press'=>$pressReviewCount,
			'nb-note-public'=>$userReviewCount,
			'pays'=>$this->data['movie']['nationality'],
			'genres'=>$this->data['movie']['genre'],
			'realisateur'=>$directors,
			'acteurs'=>$actors,
			'resume'=>$synopsis,
			'bande-annonce'=>$trailer);
					
		//$this->browseArray($this->result);
	
		return $this->result;
	}
	
public function serieSearch($keywords){
		$keywords = urlencode($keywords);
        $url = $this->get_url($keywords,true);
        debug("serieSearch " . $url);
		$this->content = file_get_contents($url);
		/**if($this->content === false){
			throw new Exception('Failed to open stream : '.$this->url);
		}**/
		$this->data = json_decode($this->content,true);
//        debug($this->content);

        if (empty($this->data['feed']['tvseries'])) // not found
            return "";

		if (empty($this->data['feed']['tvseries']['0']['title'])) 
            $title = $this->data['feed']['tvseries']['0']['originalTitle'];
		else 
            $title = $this->data['feed']['tvseries']['0']['title'];

    	if (empty($this->data['feed']['tvseries']['0']['castingShort']['creators'])) 
            $creators = "";
		else 
            $creators = $this->data['feed']['tvseries']['0']['castingShort']['creators'];


		$this->result = array(
			'code'=>$this->data['feed']['tvseries']['0']['code'],
			'titre'=>$title,
			'titre-original'=>$this->data['feed']['tvseries']['0']['originalTitle'],
			'annee'=>$this->data['feed']['tvseries']['0']['yearStart'],
			'acteurs'=>$this->data['feed']['tvseries']['0']['castingShort']['actors'],
			'directeur'=>$creators,
			'note-public'=>round($this->data['feed']['tvseries']['0']['statistics']['userRating'],1),
			'affiche'=>$this->data['feed']['tvseries']['0']['poster']['href']);
		//$this->browseArray($this->data);
		return $this->result;	
	}

public function seasonInfos($id){
	$this->url = $this->url_site_lang.$this::UrlSeason.'code='.$id.'&partner='.$this->Options['partner'].'&format=json&profile=large';
    debug("seasonInfos " . $this->url);
    
	$this->content = file_get_contents($this->url);
	$this->data = json_decode($this->content,true);
	$tabEpisode = $this->data['season']['episode'];
	for($i=0;$i<count($tabEpisode);$i++){
			$episodecode[$tabEpisode[$i]['episodeNumberSeason']] = $tabEpisode[$i]['code'];
		}
	$this->result = array(
	'code'=>$this->data['season']['code'],
	'tabEpisode'=>$episodecode
	);
	//$this->browseArray($this->data);
	return $this->result;
}

public function episodeInfos($id){
	$this->url = $this->url_site_lang.$this::UrlEpisode.'code='.$id.'&partner='.$this->Options['partner'].'&format=json&profile=large';
	$this->content = file_get_contents($this->url);
    debug("episodeInfos " . $this->url);
	$this->data = json_decode($this->content, true);
    if (empty($this->data['episode']['statistics']['userRating']))
        $userRating = "0";
    else
        $userRating = $this->data['episode']['statistics']['userRating'];
        
    if (empty($this->data['episode']['originalBroadcastDate']))
        $originalBroadcastDate = "";
    else
        $originalBroadcastDate = $this->data['episode']['originalBroadcastDate'];

    if (empty($this->data['episode']['title']))
        $title = $this->data['episode']['originalTitle'];
    else
        $title = $this->data['episode']['title'];

    if (empty($this->data['episode']['synopsis']))
        $synopsis = "";
    else
        $synopsis = $this->data['episode']['synopsis'];
        
        
	if (empty($this->data['episode']))  
        $this->result = array();
    else
    	$this->result = array(
    	'code'=>$this->data['episode']['code'],
    	'titre'=>$title,
    	'titre-original'=>$this->data['episode']['originalTitle'],
    	'date'>$originalBroadcastDate,
    	'resume'=>$synopsis,
    	'note-public'=>$userRating,
    	'saison'=>$this->data['episode']['parentSeason']['name'],
    	'episode'=>$this->data['episode']['episodeNumberSeason']
    	);
	//$this->browseArray($this->data);
	return $this->result;
}
	
public function serieInfos($id){
	$this->url = $this->url_site_lang.$this::UrlSerie.'code='.$id.'&partner='.$this->Options['partner'].'&format=json&profile=large';
    debug("serieInfos " . $this->url);
	$this->content = file_get_contents($this->url);
	$this->data = json_decode($this->content,true);
	$tabseason = $this->data['tvseries']['season'];
	for($i=0;$i<count($tabseason);$i++){
		$seasoncode[$tabseason[$i]['seasonNumber']] = $tabseason[$i]['code'];
	}
	if (empty($this->data['tvseries']['topBanner']['href'])) 
        $topBanner = "";
    else
        $topBanner = $this->data['tvseries']['topBanner']['href'];
    if (empty($this->data['tvseries']['title']))
        $title = $this->data['tvseries']['originalTitle'];
    else
        $title = $this->data['tvseries']['title'];
	$this->result = array(
		'code'=>$this->data['tvseries']['code'],
		'titre-original'=>$this->data['tvseries']['originalTitle'],
		'titre'=>$title,
		'affiche'=>$this->data['tvseries']['poster']['href'],
		'acteurs'=>$this->data['tvseries']['castingShort']['actors'],
		'resume'=>$this->data['tvseries']['synopsis'],
		'nb-saisons'=>$this->data['tvseries']['seasonCount'],
		'nb-episodes'=>$this->data['tvseries']['episodeCount'],
		'longueur'=>$this->data['tvseries']['formatTime'].'min',
		'tabSaisons'=>$seasoncode,
		'topBanner'=>$topBanner);
					
		//$this->browseArray($this->result);
	
		return $this->result;
}

public function movieMultipleSearch($keywords,$count) {
		$keywords = urlencode($keywords);
		$this->url = $this->url_site_lang.$this::UrlSearch.'q='.$keywords.'&partner='.$this->Options['partner'].'&filter=movie&format=json&count='.$count.'&profile='.$this->Options['profile'].'&version='.$this->Options['version'];
		$this->content = @file_get_contents($this->url); 
		if($this->content === false){
			throw new Exception('Failed to open stream : '.$this->url);
		}
		
		$this->data = json_decode($this->content,true);
		$this->result = array();
		for ($i=0;$i<$count;$i++){
			if (empty($this->data['feed']['movie'][$i]['code'])) 
                break;
			if (empty($this->data['feed']['movie'][$i]['title'])) 
                $title = $this->data['feed']['movie'][$i]['originalTitle'];
			else 
                $title = $this->data['feed']['movie'][$i]['title'];
    		if (empty($this->data['feed']['movie'][$i]['castingShort']['actors'])) 
                $actors = "";
    		else 
                $actors = $this->data['feed']['movie'][$i]['castingShort']['actors'];
            
        	if (empty($this->data['feed']['movie'][$i]['castingShort']['directors'])) 
                $directors = "";
            else 
                $directors = $this->data['feed']['movie'][$i]['castingShort']['directors'];

            if (empty($this->data['feed']['movie'][$i]['statistics']['pressRating'])) 
                $pressRating = "";
            else 
                $pressRating = $this->data['feed']['movie'][$i]['statistics']['pressRating'];

            if (empty($this->data['feed']['movie'][$i]['statistics']['userRating'])) 
                $userRating = "";
            else 
                $userRating = $this->data['feed']['movie'][$i]['statistics']['userRating'];

            if (empty($this->data['feed']['movie'][$i]['poster']['href'])) 
                $poster = "";
            else 
                $poster = $this->data['feed']['movie'][$i]['poster']['href'];

            if (empty($this->data['feed']['movie'][$i]['originalTitle'])) 
                $originalTitle = "";
            else 
                $originalTitle = $this->data['feed']['movie'][$i]['originalTitle'];

            if (empty($this->data['feed']['movie'][$i]['productionYear'])) 
                $productionYear = "";
            else 
                $productionYear = $this->data['feed']['movie'][$i]['productionYear'];


			$this->result[$i] = array(
				'code'=>$this->data['feed']['movie'][$i]['code'],
				'titre'=>$title,
				'titre-original'=>$originalTitle,
				'annee'=>$productionYear,
				'acteurs'=>$actors,
				'realisateur'=>$directors,
				'note-presse'=>round($pressRating,1),
				'note-public'=>round($userRating,1),
				'affiche'=>$poster);
			}
			//$this->browseArray($this->data);
		return $this->result;
		
	}
	
	private function get_url($keywords,$serie=false){
		if ($serie) $this->Options['filter']='tvseries';
		$this->url = $this->url_site_lang.$this::UrlSearch.'q='.$keywords;
		foreach($this->Options as $key => $value){
			$this->url = $this->url.'&'.$key.'='.$value;
		}
		//echo $this->url;
		return $this->url;
	}

	private function browseArray($array){
		foreach($array as $key => $value){
			if(is_array($value)){
				echo $key.' :<ul>';
				$this->browseArray($value);
				echo '</ul>';
			}
			else {
				echo $key.' = '.$value.'<br>';
			}
		}
	}
	
	private function url_site($lang){
		switch($lang){
		case 'fr':
		return 'http://api.allocine.fr';
		break;
	
		case 'en':
		return 'http://api.screenrush.co.uk';
		break;
		
		}
	
	}
}

?>
