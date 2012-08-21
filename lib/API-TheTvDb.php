<?php
/*****************************************************************************


				=================== class TheTvDb ======================
				
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
$s = new TheTvDb();
$infos = $s->serieSearch('the');

class TheTvDb{

public $url, $content, $data, $result;
private $Options = array();

const ApiKey = 'C0966BAAAA39F085';
const UrlSearch = 'http://www.thetvdb.com/api/';
const UrlSerie = '';
const UrlSeason = '';
const UrlEpisode = '';

public function __construct($lang='fr') {
	$this->Lang = $lang;
}

public function serieSearch($keywords){
$url = 'http://www.thetvdb.com/api/GetSeries.php?seriesname='.$keywords;
	$xml = simplexml_load_file($url);
	print_r($xml);
	echo '<br>'.$xml->Series->seriesid;
	$this->result = array(
			'code'=>$xml->Series->seriesid,
			'titre'=>$xml->Series->SeriesName,
			'annee'=>$xml->Series->FirstAired,
			'affiche'=>$xml->Series->banner);
		$this->browseArray($this->result);
		return $this->result;
	return $xml;

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