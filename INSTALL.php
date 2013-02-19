<?php
session_start();
if($_GET['action'] == 'mod'){
$db = mysql_connect("localhost", "root", $_POST['pass']);
if (!$db) {
    die('<br>Mot de passe SQL erron&eacute; retournez &agrave; la page pr&eacute;c&eacute;dente.');
}
	$file = fopen('lib/config.php','w');
	ftruncate($file,0);
	
	$ext = $_POST['ext'];
	$ext = explode(',',$ext);
	$ext_array = 'array(';
	for($i=0;$i<count($ext);$i++){
	$ext_array .= '"'.$ext[$i].'"';
	if($i != (count($ext)-1)) $ext_array .= ',';
	}
	$ext_array .= ')';
	
	$del = $_POST['deletedwords'];
	$del = explode(',',$del);
	$del_array = 'array(';
	for($i=0;$i<count($del);$i++){
	$del_array .= '"'.$del[$i].'"';
	if($i != (count($del)-1)) $del_array .= ',';
	}
	$del_array .= ')';
	
	$hid = $_POST['hiddenfiles'];
	$hid = explode(',',$hid);
	$hid_array = 'array(';
	for($i=0;$i<count($hid);$i++){
	$hid_array .= '"'.$hid[$i].'"';
	if($i != (count($hid)-1)) $hid_array .= ',';
	}
	$hid_array .= ')';
	
	if(empty($_POST['login'])) $login='FALSE';
	else $login='TRUE';
	
	if(empty($_POST['secure'])) $secure='FALSE';
	else $secure='TRUE';
	
	if(empty($_POST['ftp'])) $ftp='FALSE';
	else $ftp='TRUE';
	
	if(empty($_POST['inauto'])) $inauto='FALSE';
	else $inauto='TRUE';
	
	if(empty($_POST['modal'])) $modal='FALSE';
	else $modal='TRUE';
	
	$content_config = '<?php
	$APP_NAME = "'.$_POST['title'].'";
	$VERSION = "'.$_POST['version'].'";
	$PASSWORD_SQL = "'.$_POST['pass'].'";
	$DATABASE = "'.$_POST['bdd'].'";
	$PORT_SYNO = "'.$_POST['port'].'";
	$EXT = '.$ext_array.';
	$HIDDEN_FILES = '.$hid_array.';
	$DELETED_WORDS = '.$del_array.';
	$SERIES_DIR = "'.$_POST['seriesdir'].'";
	$MOVIES_DATABASE = "'.$_POST['videobase'].'" ;
	$SERIES_DATABASE = "'.$_POST['seriebase'].'" ;
	$LANGUAGE = "'.$_POST['lang'].'";
	$SECURE = '.$secure.';
	$LOGIN = '.$login.';
	$MODAL = '.$modal.';
	$FTP = '.$ftp.';
	$INDEXATION_AUTO = '.$inauto.';
	$INSTALL = FALSE;
?>';
	
	if(fputs($file, $content_config)) $message = 'Configuration modifi&eacute;e avec succ&egrave;s!';
	else echo 'echec';
}

require('lib/config.php');
require('lib/functions.php');
require('lib/lang.php');
login_check($LOGIN,$PORT_SYNO,$SECURE);
$root = admin($root);
if(!$root) die (include('login.php'));

if($_GET['action'] == 'mod'){
$sql_database = "
CREATE DATABASE `".$DATABASE."` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
";
if (mysql_query($sql_database, $db)) {
    echo "Base de données créée correctement\n";
} else {
    echo 'Erreur lors de la création de la base de données : ' . mysql_error() . "\n";
}
mysql_select_db($DATABASE,$db);

$sql_movies = "
CREATE TABLE IF NOT EXISTS `movies` (
  `id_movie` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `original_name` varchar(255) NOT NULL,
  `length` varchar(255) NOT NULL,
  `countries` varchar(255) NOT NULL,
  `directors` varchar(255) NOT NULL,
  `actors` varchar(255) NOT NULL,
  `synopsis` text NOT NULL,
  `poster` varchar(255) NOT NULL,
  `trailer` varchar(255) NOT NULL,
  `note` float NOT NULL,
  `votes` int(11) NOT NULL,
  `year` varchar(10) NOT NULL,
  `size` float NOT NULL,
  `dir` varchar(255) NOT NULL,
  `api` varchar(255) NOT NULL,
  PRIMARY KEY (`link`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";

$sql_series = "
CREATE TABLE IF NOT EXISTS `series` (
  `id_serie` int(11) NOT NULL,
  `id_saison` int(11) NOT NULL,
  `id_episode` int(11) NOT NULL,
  `link` varchar(255) NOT NULL,
  `serie_name` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `episode_original_name` varchar(255) NOT NULL,
  `season_nb` int(22) NOT NULL,
  `episode_nb` int(22) NOT NULL,
  `note` float NOT NULL,
  `actors` varchar(255) NOT NULL,
  `directors` varchar(255) NOT NULL,
  `length` varchar(255) NOT NULL,
  `synopsis` text NOT NULL,
  `poster` varchar(255) NOT NULL,
  `broadcast_date` date NOT NULL,
  `dir` varchar(255) NOT NULL,
  `api` varchar(255) NOT NULL,
  PRIMARY KEY (`link`, `id_serie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
";

$sql_genres = "
CREATE TABLE IF NOT EXISTS `genres` (
  `id_genre` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  PRIMARY KEY (`id_genre`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";

$sql_movies_genres = "
CREATE TABLE IF NOT EXISTS `movie_genre` (
  `id_movie_genre` int(11) NOT NULL AUTO_INCREMENT,
  `fk_id_genre` int(11) NOT NULL,
  `fk_id_movie` int(11) NOT NULL,
  PRIMARY KEY (`id_movie_genre`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";

$sql_errors = "
CREATE TABLE IF NOT EXISTS `errors` (
  `id_error` int(11) NOT NULL AUTO_INCREMENT,
  `link` varchar(255) NOT NULL,
  `dir` varchar(255) NOT NULL,
  `error` varchar(255) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `type` varchar(255) NOT NULL,
  PRIMARY KEY (`id_error`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
";
mysql_query($sql_movies) or die ('Erreur SQL : '.mysql_error());
mysql_query($sql_series) or die ('Erreur SQL : '.mysql_error());
mysql_query($sql_genres) or die ('Erreur SQL : '.mysql_error());
mysql_query($sql_movies_genres) or die ('Erreur SQL : '.mysql_error());
mysql_query($sql_errors) or die ('Erreur SQL : '.mysql_error());

echo '<script>
     document.location.href="index.php" 
</script>';

}

if(empty($_GET['action'])){
?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
<link rel="stylesheet" href="
css/default.css">
<link rel="stylesheet" href="css/nyroModal.css">
<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.8.17.custom.css" />

<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>
<!--<script type="text/javascript" src="http://code.jquery.com/ui/jquery-ui-git.js"></script>-->
<script type="text/javascript" src="js/jquery.nyroModal.custom.min.js"></script>
</head>
<body>
<!-- HEADER -->
<header>
<div class="header_left logo"><b><?php echo $APP_NAME;?></b></div>

	<div id="logout" class="header_left" style="margin-left:8px;">
		<?php if($LOGIN) echo '['.$_SESSION['user'].'] <span><a href="index.php?action=logout">Logout</a>';
		if ($root) echo ' | <a href="index.php">Accueil</a>';
		echo '</span>';?>
	</div>
	
	<div id="empty" class="header_left" style="margin-left:30px;padding-top:3px;">
	</div></header>
<!-- /HEADER -->

<!-- NAVIGATION -->
<nav class="margin">
<div class=""><h2>INSTALLATION</h2></div>
</nav>
<!-- /NAVIGATION -->

<div id="content">
		<form method="POST" action="INSTALL.php?action=mod">
		<table style="font-size:small;">
			<?php if(isset($message)) echo '<tr><td colspan="2" style="text-align:center;color:green;">'.$message.'</td></tr>';?>
			<tr>
				<td><?php echo appname;?></td><td><input type="text" name="title" value=<?php echo "\"".$APP_NAME."\"";?>>
				<input type="hidden" name="version" value="<?php echo $VERSION;?>"></td>
			</tr>
			<tr>
				<td><?php echo login;?></td><td><input type="checkbox" name="login" value="login" <?php if($LOGIN) echo 'checked';?>></td>
			</tr>
			<tr>
				<td><?php echo secureconnexion;?></td><td><input type="checkbox" name="secure" value="secure" <?php if($SECURE) echo 'checked';?>></td>
			</tr>
			<tr>
				<td><?php echo modal;?></td><td><input type="checkbox" name="modal" value="modal" <?php if($MODAL) echo 'checked';?>></td>
			</tr>
			<tr>
				<td><?php echo ftp;?></td><td><input type="checkbox" name="ftp" value="ftp" <?php if($FTP) echo 'checked';?>></td>
			</tr>
			<tr>
				<td><?php echo autoindexing;?></td><td><input type="checkbox" name="inauto" value="inauto" <?php if($INDEXATION_AUTO) echo 'checked';?>></td>
			</tr>
			<tr>
				<td><?php echo dbmovies;?></td><td><input type="radio" name="videobase" value="Allocine" onchange="changeAlert()" <?php if($MOVIES_DATABASE == 'Allocine') echo 'checked';?>>Allocine<input type="radio" name="videobase" value="TMDb" onchange="changeAlert()" <?php if($MOVIES_DATABASE == 'TMDb') echo 'checked';?>>TMDb <span class="changeAlert" style="color:red;"></span>
				<input type="hidden" name="oldvideobase" value="<?php echo $MOVIES_DATABASE;?>">
				</td>
			</tr>
			<tr>
				<td><?php echo dbseries;?></td><td><input type="radio" name="seriebase" value="Allocine" <?php if($SERIES_DATABASE == 'Allocine') echo 'checked';?>>Allocine<input type="radio" name="seriebase" value="TheTvDb" <?php if($SERIES_DATABASE == 'TheTvDb') echo 'checked';?> disabled>TheTvDb</td>
			</tr>
			<tr>
				<td><?php echo lang;?></td><td>
				<select name="lang">
					<option value="fr" <?php if($LANGUAGE == 'fr') echo 'selected';?>>Francais</option>
					<option value="en" <?php if($LANGUAGE == 'en') echo 'selected';?>>English</option>
				</select>
				</td>
			</tr>
			<tr>
				<td><?php echo sqlpass;?></td><td><input type="password" name="pass" value=<?php echo "\"".$PASSWORD_SQL."\"";?>></td>
			</tr>
			<tr>
				<td><?php echo dbsql;?></td><td><input type="text" name="bdd" value=<?php echo "\"".$DATABASE."\"";?>></td>
			</tr>
			<tr>
				<td><?php echo confport;?></td><td><input type="text" name="port" value=<?php echo "\"".$PORT_SYNO."\"";?>></td>
			</tr>
			<tr>
				<td><?php echo seriesdir;?></td><td><input type="text" name="seriesdir" value=<?php echo "\"".$SERIES_DIR."\"";?>></td>
			</tr>
			<tr>
				<td><?php echo videoext;?></td><td><input type="text" name="ext" value="<?php for($i=0;$i<count($EXT);$i++){echo $EXT[$i];if($i != (count($EXT)-1)) echo ',';}?>"></td>
			</tr>
			<tr>
				<td><?php echo hidden_files;?></td><td><input type="text" size="70" name="hiddenfiles" value="<?php for($i=0;$i<count($HIDDEN_FILES);$i++){echo $HIDDEN_FILES[$i];if($i != (count($HIDDEN_FILES)-1)) echo ',';}?>"></td>
			</tr>
			<tr>
				<td><?php echo deleted_words;?></td><td><input type="text" size="70" name="deletedwords" value="<?php for($i=0;$i<count($DELETED_WORDS);$i++){echo $DELETED_WORDS[$i];if($i != (count($DELETED_WORDS)-1)) echo ',';}?>"></td>
			</tr>
			<tr>
				<td colspan="2"><input type="submit" value="<?php echo update;?>"></td>
			</tr>
		</table>
		</form>
		</div>

<footer>

</footer>
<script>

$( "#tabs" ).tabs({
	cache:true,
	load: function (e, ui) {
    	$(ui.panel).find(".tab-loading").remove();
   	},
   	select: function (e, ui) {
    	var $panel = $(ui.panel);
		if ($panel.is(":empty")) {
        	$panel.append("<div class='tab-loading' style='text-align:center;'><img src='images/ajaxLoader.gif'></div>")
     	}
    }
});

function changeAlert(){
	$('span.changeAlert').html('Ce changement va effacer votre base actuelle');
}
	
</script>
</body>
</html>
<?php
}
?>