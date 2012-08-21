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
		//$this->content = false;
		if($this->content === false){
			throw new Exception('Failed to open stream : '.$this->get_url($keywords));
		}
		$this->data = json_decode($this->content,true);
		if (empty($this->data['feed']['movie']['0']['title'])) $title = $this->data['feed']['movie']['0']['originalTitle'];
		else $title = $this->data['feed']['movie']['0']['title'];
		$this->result = array(
			'code'=>$this->data['feed']['movie']['0']['code'],
			'titre'=>$title,
			'titre-original'=>$this->data['feed']['movie']['0']['originalTitle'],
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
	
		$nb = count($this->data['movie']['nationality'])-1;
		for($i=0; $i<=$nb; $i++){
			$nationality = $nationality.$this->data['movie']['nationality'][$i]['$'];
				if ($i != $nb) $nationality = $nationality.', ';
		}	
		$this->data['movie']['nationality'] = $nationality;
	
		$nb = count($this->data['movie']['genre'])-1;
		for($i=0; $i<=$nb; $i++){
			$genre = $genre.$this->data['movie']['genre'][$i]['$'];
				if ($i != $nb) $genre = $genre.', ';
		}
		$this->data['movie']['genre'] = $genre;
	
		$time[0] = floor($this->data['movie']['runtime']/3600);//heure
		$time[1] = (($this->data['movie']['runtime']/3600)-$time[0])*60;//minutes
	
		$this->data['movie']['runtime'] = $time[0].'h'.$time[1].'min';
	
		$this->result = array(
			'titre-original'=>$this->data['movie']['originalTitle'],
			'titre'=>$this->data['movie']['title'],
			'affiche'=>$this->data['movie']['poster']['href'],
			'annee'=>$this->data['movie']['productionYear'],
			'longueur'=>$this->data['movie']['runtime'],
			'note-press'=>$this->data['movie']['statistics']['pressRating'],
			'note-public'=>$this->data['movie']['statistics']['userRating'],
			'nb-note-press'=>$this->data['movie']['statistics']['pressReviewCount'],
			'nb-note-public'=>$this->data['movie']['statistics']['userReviewCount'],
			'pays'=>$this->data['movie']['nationality'],
			'genres'=>$this->data['movie']['genre'],
			'realisateur'=>$this->data['movie']['castingShort']['directors'],
			'acteurs'=>$this->data['movie']['castingShort']['actors'],
			'resume'=>$this->data['movie']['synopsis'],
			'bande-annonce'=>$this->data['movie']['trailer']['href']);
					
		//$this->browseArray($this->result);
	
		return $this->result;
	}
	
public function serieSearch($keywords){

		$keywords = urlencode($keywords);
		//echo $keywords;
		$this->content = file_get_contents($this->get_url($keywords,true));
		/**if($this->content === false){
			throw new Exception('Failed to open stream : '.$this->url);
		}**/
		$this->data = json_decode($this->content,true);
		if (empty($this->data['feed']['tvseries']['0']['title'])) $title = $this->data['feed']['tvseries']['0']['originalTitle'];
		else $title = $this->data['feed']['tvseries']['0']['title'];
		$this->result = array(
			'code'=>$this->data['feed']['tvseries']['0']['code'],
			'titre'=>$title,
			'titre-original'=>$this->data['feed']['tvseries']['0']['originalTitle'],
			'annee'=>$this->data['feed']['tvseries']['0']['yearStart'],
			'acteurs'=>$this->data['feed']['tvseries']['0']['castingShort']['actors'],
			'directeur'=>$this->data['feed']['tvseries']['0']['castingShort']['creators'],
			'note-public'=>round($this->data['feed']['tvseries']['0']['statistics']['userRating'],1),
			'affiche'=>$this->data['feed']['tvseries']['0']['poster']['href']);
		//$this->browseArray($this->data);
		return $this->result;	
	}

public function seasonInfos($id){
	$this->url = $this->url_site_lang.$this::UrlSeason.'code='.$id.'&partner='.$this->Options['partner'].'&format=json&profile=large';
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
	$this->data = json_decode($this->content, true);
	$this->result = array(
	'code'=>$this->data['episode']['code'],
	'titre'=>$this->data['episode']['title'],
	'titre-original'=>$this->data['episode']['originalTitle'],
	'date'>$this->data['episode']['originalBroadcastDate'],
	'resume'=>$this->data['episode']['synopsis'],
	'note-public'=>$this->data['episode']['statistics']['userRating'],
	'saison'=>$this->data['episode']['parentSeason']['name'],
	'episode'=>$this->data['episode']['episodeNumberSeason']
	);
	//$this->browseArray($this->data);
	//echo $this->url;
	return $this->result;

}
	
public function serieInfos($id){
	$this->url = $this->url_site_lang.$this::UrlSerie.'code='.$id.'&partner='.$this->Options['partner'].'&format=json&profile=large';
		//echo $this->url;
		$this->content = file_get_contents($this->url);
		$this->data = json_decode($this->content,true);
		$tabseason = $this->data['tvseries']['season'];
		for($i=0;$i<count($tabseason);$i++){
			$seasoncode[$tabseason[$i]['seasonNumber']] = $tabseason[$i]['code'];
		}
		$this->result = array(
			'code'=>$this->data['tvseries']['code'],
			'titre-original'=>$this->data['tvseries']['originalTitle'],
			'titre'=>$this->data['tvseries']['originalTitle'],
			'affiche'=>$this->data['tvseries']['poster']['href'],
			'acteurs'=>$this->data['tvseries']['castingShort']['actors'],
			'resume'=>$this->data['tvseries']['synopsis'],
			'nb-saisons'=>$this->data['tvseries']['seasonCount'],
			'nb-episodes'=>$this->data['tvseries']['episodeCount'],
			'longueur'=>$this->data['tvseries']['formatTime'].'min',
			'tabSaisons'=>$seasoncode,
			'topBanner'=>$this->data['tvseries']['topBanner']['href']);
					
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
			if (empty($this->data['feed']['movie'][$i]['code'])) break;
			if (empty($this->data['feed']['movie'][$i]['title'])) $title = $this->data['feed']['movie'][$i]['originalTitle'];
			else $title = $this->data['feed']['movie'][$i]['title'];
			$this->result[$i] = array(
				'code'=>$this->data['feed']['movie'][$i]['code'],
				'titre'=>$title,
				'titre-original'=>$this->data['feed']['movie'][$i]['originalTitle'],
				'annee'=>$this->data['feed']['movie'][$i]['productionYear'],
				'acteurs'=>$this->data['feed']['movie'][$i]['castingShort']['actors'],
				'realisateur'=>$this->data['feed']['movie'][$i]['castingShort']['directors'],
				'note-presse'=>round($this->data['feed']['movie'][$i]['statistics']['pressRating'],1),
				'note-public'=>round($this->data['feed']['movie'][$i]['statistics']['userRating'],1),
				'affiche'=>$this->data['feed']['movie'][$i]['poster']['href']);
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
