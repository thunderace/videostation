<?php
require('config.php');
/*****************************************************************************


				=================== class TMDb ======================
				
00 Rechercher un film :

	$movie = new TMDb('langue');
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

	$movie = new TMDb('langue');
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
class TMDb{

public $url, $content, $data, $result;
private $Options = array();


const ApiKey = '0a3956aaa5b4f99c5e2f2f1e044958db';
const UrlSearch = 'http://api.themoviedb.org/2.1/Movie.search/';
const UrlMovie = 'http://api.themoviedb.org/2.1/Movie.getInfo/';

public function __construct($lang='fr') {
	$this->Lang = $lang;
}

public function movieSearch($keywords) {		
		$this->content = @file_get_contents($this->get_url_search($keywords));
		//if($this->content == FALSE) $this->content = file_get_contents($this->get_url_search($keywords));
		$i=1;
		while($this->content === FALSE){
			$this->content = @file_get_contents($this->get_url_search($keywords));
			$i++;
			if($i==3) break;
		}
		if($this->content === false){
			throw new Exception('Failed to open stream');
		}
		$this->data = json_decode($this->content,true);
		if($this->data['0'] == 'Nothing found.') $this->result = array();
		else{
		$annee = explode('-',$this->data['0']['released']);
		$this->result = array(
			'code'=>$this->data['0']['id'],
			'titre'=>$this->data['0']['name'],
			'titre-original'=>$this->data['0']['original_name'],
			'annee'=>$annee[0],
			'note-public'=>round(($this->data['0']['rating'])/2,1),
			'affiche'=>$this->data['0']['posters']['1']['image']['url']
		);
		
		}
		//$this->browseArray($this->result);

		return $this->result;		
	}
	
public function movieMultipleSearch($keywords,$count) {		
		$this->content = file_get_contents($this->get_url_search($keywords));
		if($this->content == FALSE) $this->content = file_get_contents($this->get_url_search($keywords));
		$this->data = json_decode($this->content,true);
		if($this->data['0'] == 'Nothing found.') $this->result = array();
		else{
		for($i=0;$i<$count;$i++){
		if(empty($this->data[$i]['id'])) break;
		$annee = explode('-',$this->data[$i]['released']);
		$this->result[$i] = array(
			'code'=>$this->data[$i]['id'],
			'titre'=>$this->data[$i]['name'],
			'titre-original'=>$this->data[$i]['original_name'],
			'annee'=>$annee[0],
			'note-public'=>round(($this->data[$i]['rating'])/2,1),
			'affiche'=>$this->data[$i]['posters']['1']['image']['url']
		);
		}
		}
		//$this->browseArray($this->result);

		return $this->result;		
	}
	
public function movieInfos($id){
		$this->content = @file_get_contents($this->get_url_movie($id));
		//if($this->content == FALSE) $this->content = file_get_contents($this->get_url_movie($id));
		$i=1;
		while($this->content === FALSE){
			$this->content = @file_get_contents($this->get_url_movie($id));
			$i++;
			if($i==3) break;
		}
		if($this->content === FALSE){
			throw new Exception('Failed to catch infos');
		}
		$this->data = json_decode($this->content,true);
		$annee = explode('-',$this->data['0']['released']);
		
		$time[0] = floor($this->data['0']['runtime']/60);//heure
		$time[1] = (($this->data['0']['runtime']/60)-$time[0])*60;//minutes
		$this->data['0']['runtime'] = $time[0].'h'.$time[1].'min';
		
		$nb = count($this->data['0']['countries'])-1;
		for($i=0; $i<=$nb; $i++){
			$nationality = $nationality.$this->data['0']['countries'][$i]['name'];
				if ($i != $nb) $nationality = $nationality.', ';
		}	
		$this->data['0']['countries'] = $nationality;
		
		$nb = count($this->data['0']['genres'])-1;
		for($i=0; $i<=$nb; $i++){
			$genre = $genre.$this->data['0']['genres'][$i]['name'];
				if ($i != $nb) $genre = $genre.', ';
		}
		$this->data['0']['genres'] = $genre;
		
		for($i=0;$i<count($this->data[0]['cast'])-1;$i++){
			if($this->data[0]['cast'][$i]['job'] == 'Director'){
			$director .= $this->data[0]['cast'][$i]['name'].', ';
			}
		}
		$director = rtrim($director,', ');
		
		for($i=0;$i<count($this->data[0]['cast'])-1;$i++){
			if($this->data[0]['cast'][$i]['job'] == 'Actor'){
			$actors .= $this->data[0]['cast'][$i]['name'].', ';
			}
		}
		$actors = rtrim($actors,', ');
		
		$this->result = array(
			'titre-original'=>$this->data['0']['original_name'],
			'titre'=>$this->data['0']['name'],
			'affiche'=>$this->data['0']['posters']['2']['image']['url'],
			'annee'=>$annee[0],
			'longueur'=>$this->data['0']['runtime'],
			'note-public'=>($this->data['0']['rating']/2),
			'nb-note-public'=>$this->data['0']['votes'],
			'pays'=>$this->data['0']['countries'],
			'genres'=>$this->data['0']['genres'],
			'realisateur'=>$director,
			'acteurs'=>$actors,
			'resume'=>$this->data['0']['overview'],
			'bande-annonce'=>$this->data['0']['trailer']);
					
		//$this->browseArray($this->data);
	
		return $this->result;
	}
	
public function get_lang($lang){

}
	
private function get_url_search($keywords){
		$keywords = urlencode($keywords);
		$this->url = $this::UrlSearch.$this->Lang.'/json/'.$this::ApiKey.'/'.$keywords;
		
		return $this->url;
	}
	
private function get_url_movie($keywords){
		$keywords = urlencode($keywords);
		$this->url = $this::UrlMovie.$this->Lang.'/json/'.$this::ApiKey.'/'.$keywords;
		
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

}