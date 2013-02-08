<?php
$time_start = microtime(true);
session_start();
require_once('lib/config.php');
require_once('lib/API-allocine.php');
require_once('lib/functions.php');
connect($USER_SQL,$PASSWORD_SQL,$DATABASE);
//login_check($LOGIN,$PORT_SYNO,$SECURE);
$root = true ; //= admin($root);
if(!$root) die (include('login.php'));
?>
<?php //echo round((microtime(true)-$time_start),3);?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title></title>
<link rel="stylesheet" href="css/default.css">
<link rel="stylesheet" href="css/nyroModal.css">
<link rel="stylesheet" type="text/css" href="css/jquery-ui-1.8.17.custom.css" />

<script type="text/javascript" src="http://code.jquery.com/jquery-1.7.1.min.js"></script>
<!--<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.8.17/jquery-ui.min.js"></script>-->
<script type="text/javascript" src="http://code.jquery.com/ui/jquery-ui-git.js"></script>
<script type="text/javascript" src="js/jquery.nyroModal.custom.min.js"></script>
<script type="text/javascript" src="js/jquery.tools.min.js"></script>
<script type="text/javascript" src="js/jquery.ui.popup.js"></script>
</head>
<body>
<!-- HEADER -->
<header>

	<div class="header_left logo"><img src="images/logo.png"></div>


	
	<div id="empty" class="header_left" style="margin-left:30px;padding-top:3px;">
	</div>
	
	<div class="header_right demo" style="margin-right:8px;padding-top:1px;">
	<a href="#param"><button id="parameters" value="Infos">Infos</button></a>
	<div class="ui-widget-content" id="param" aria-label="Login options" style="float:right;">
		<?php if(isset($_SESSION['user'])) echo '['.$_SESSION['user'].'] | <a href="index.php?action=logout">Logout</a>';
		else echo '| <a href="login.php">Login</a>';
		if ($root) echo ' | <a href="index.php">'.home.'</a>';
		echo ' |';
		$stats = countVideos();?>
		<hr>
		<table border="0">
		<tr><td>Films :</td><td><?php echo $stats['movies'];?></td></tr>
		<tr><td>Series :</td><td><?php echo $stats['series'];?></td></tr>
		<tr><td>Mal indexees :</td><td><?php echo $stats['wrong'];?></td></tr>
		<tr><td>Erreurs :</td><td><?php echo $stats['errors'];?></td></tr>
		</table>
		<hr>
	</div>
	</div>		
			
</header>
<!-- /HEADER -->

<!-- NAVIGATION -->
<nav class="margin">
<div class=""><h2><?php echo administration;?></h2></div>
</nav>
<!-- /NAVIGATION -->

<div id="content">
<div id="tabs" style="width:85%;margin-left:auto;margin-right:auto;min-height:400px;max-height:600px;overflow:auto;">
	<ul>
		<li><a href="#tabs-1"><?php echo basicparameters;?></a></li>
		<li><a href="list_non_indexed.php"><?php echo nonindexedvideos;?></a></li>
		<li><a href="#tabs-4"><?php echo wrongindexedvideos;?></a></li>
		<li><a href="#tabs-3">Erreurs</a></li>
		<li><a href="#tabs-2"><?php echo donate;?></a></li>
	</ul>
	<!-- PARAMETRES DE BASES -->
	<div id="tabs-1">
		<p>
		<form method="POST" action="admin.php?action=mod">
		<table>
			<?php if(isset($message)) echo '<tr><td colspan="2" style="text-align:center;color:green;">'.$message.'</td></tr>';?>
			<tr>
				<td><?php echo version;?></td><td><?php echo $VERSION;?></td>
			</tr>
			<tr>
				<td><?php echo appname;?></td><td><input type="text" name="title" value=<?php echo "\"".$APP_NAME."\"";?>></td>
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
				<td><?php echo dbmovies;?></td><td><input type="radio" name="videobase" value="Allocine"  <?php if($MOVIES_DATABASE == 'Allocine') echo 'checked';?>>Allocine<input type="radio" name="videobase" value="TMDb" <?php if($MOVIES_DATABASE == 'TMDb') echo 'checked';?>>TMDb	<input type="hidden" name="oldvideobase" value="<?php echo $MOVIES_DATABASE;?>">
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
		</p>
	</div>
	<!-- VIDEOS MAL INDEXEES -->
	<div id="tabs-4">
	<?php
	echo '<div id="wrongind"></div>';
	$sql = "SELECT * FROM movies WHERE id_movie=0";
	$req = mysql_query($sql) or die ('Erreur SQL '.mysql_error());
	while($data = mysql_fetch_array($req)){
		if(!mb_ereg($SERIES_DIR,$data['dir'])){
			echo '<p rel="'.$data['id_movie'].'"><span class="link">'.$data['link'].'</span> <a href="update.php?link='.urlencode($data['link']).'&oldcode='.$data['id_movie'].'" class="nyroModal">
			<button value="Modifier">Modifier</button></a></p>';
		}
	}
	?>
	</div>
	<!-- ERREURS -->
	<div id="tabs-3">
	<div id="errors">
	<?php
	$sql = "SELECT * FROM errors";
	$req = mysql_query($sql) or die ('Erreur SQL :'.mysql_error());
	$totalerrors = mysql_num_rows($req);
	if($totalerrors > 0){
	echo '<button id="indexerrors">Reindexer</button>';
	echo '<div id="patienterror"></div><div id="progressbarerror"></div>';
	echo '<table border="0">
			<tr><td style="text-align:center;color:#4183c4;">Fichiers</td><td style="text-align:center;color:#4183c4;">Repertoire</td><td style="text-align:center;color:#4183c4;">Erreur</td></tr>';
	}
	while($data = mysql_fetch_array($req)){
	echo '<tr class="errorslist" id="e'.$data['id_error'].'"><td class="link" rel="'.urlencode($data['link']).'">'.$data['link'].'</td><td class="dir" rel="'.urlencode($data['dir']).'">'.$data['dir'].'</td><td>'.$data['error'].'</td></tr>';
	}
	if(mysql_num_rows($req) > 0) echo '</table>';
	else echo '<img src="images/check.png">Aucune erreur detectee';
	?>
	</div>
	</div>
	
	<!-- FAIRE UN DON -->
	<div id="tabs-2">
	<p style="width:100%;">
	<?php echo donatetext;?>
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----
">
<input type="image" align="center" src="https://www.paypalobjects.com/fr_FR/CH/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - la solution de paiement en ligne la plus simple et la plus sécurisée !">
<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
</form>
	</p>
	</div>
</div>

</div>

<footer>
<div class="license">
<a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/3.0/"><img alt="Licence Creative Commons" style="border-width:0" src="images/cc88x31.png" /></a><br /><span style="display:none;" class="licensetext">Cette application est mise à disposition selon les termes de la <a rel="license" href="http://creativecommons.org/licenses/by-nc-nd/3.0/">Licence CC BY-NC-ND 3.0</a>.</span></div>
<div class="generation"><?php echo pagegeneration.' '.round((microtime(true)-$time_start),3);?> s.<br><br>Version : <?php echo $VERSION;?></div>
<div class="hosted">Last version available on <br><a href="https://github.com/teebo/VideoStation" target="_blank"><img src="images/github.png" style="height:30px;"></a></div>
<div style="clear:both;"></div>
</footer>
<script>
$(document).ready(function(){
$('#indexerrors').click(function(){
	$('#indexerrors').hide();
	var i = 1;
	$('tr.errorslist').each(function(){
		var rep = $(this).children('.dir').html();
		var link = $(this).children('.link').attr("rel");
		var key = $(this).attr('id');
		//document.write(key+'-'+rep+'-'+link);
		$.ajax({
  				type: "GET",
  				url: "lib/index_movie.php",
   				data: "rep="+rep+"&link="+link,
   				error:function(msg){
     				alert( "Error ! : " + msg );
   				},
   				success:function(data){
   					//insere le resultat dans le textarea 'descy'
					//$('#'+key).children('span.complete').html('complete');
					$('#'+key).fadeOut('fast');
					$('#patienterror').html(Math.round((((i)/<?php echo $totalerrors;?>)*100))+' %');
					$( "#progressbarerror" ).progressbar({
						value: (((i)/<?php echo $totalerrors;?>)*100)
					});
					//document.write(data);
					i++;
				}
		});
	});
});
$('button').button();
$('.nyroModal').nyroModal();
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

/***
	HEADER
	***/
   $('#parameters').button({
	icons: {
                primary: "ui-icon-info",
                secondary: "ui-icon-triangle-1-s"
            },
            text:false
            });   
	
	$('#param').hide();
	$('#parameters').click(function(){
	if($('#param').is(':hidden')){
	$('#param').slideDown();
	}
	else {
	$('#param').slideUp();
	}
	});
	/**
    FOOTER
    **/
    $('footer div.license').hover(function(){
    $('footer div.license span.licensetext').fadeIn();
    },function(){
    $('footer div.license span.licensetext').delay(1200).fadeOut();
    });
    $('footer').hide();
  	window.onload=function(){  
    var w = $(window).height();
    var h = ($('header').height()+22);
    var c = ($('#content').height()+$('nav').height()+20);
    var foo = ($('footer').height()+2);
    if((h+c+foo+10)<w){
    $('footer').attr('style','margin-top:'+(w-(h+c+foo))+'px;').show();
    }
    else $('footer').show();
     };
});
</script>
</body>
</html>