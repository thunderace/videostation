<?php 
require_once('lib/config.php');
require_once('lib/system_config.php');
require_once('lib/API-allocine.php');
require_once('lib/functions.php');
connect($HOST_SQL, $USER_SQL,$PASSWORD_SQL,$DATABASE);
echo '<div id="test"></div>';
$sql = "SELECT * FROM movies WHERE id_movie=0";
$req = mysql_query($sql) or die ('Erreur SQL '.mysql_error());
while($data = mysql_fetch_array($req)){
if(!mb_ereg($SERIES_DIR,$data['dir'])){
echo '<p rel="'.$data['id_movie'].'"><span class="link">'.$data['link'].'</span> <a href="update.php?link='.urlencode($data['link']).'&oldcode='.$data['id_movie'].'" class="nyroModal">
<button value="Modifier">Modifier</button></a></p>';
}
}
?>

<script>
$('button').button();
$('.nyroModal').nyroModal();
$('a.opener').click(function(){
		$( "#tabs" ).tabs();

			var id = $(this).parents('p').attr('rel');
			var link = $(this).prev().text();
			$('#test').html(link+' - '+id);
			$.ajax({
  				type: "GET",
  				url: "update.php",
   				data: "link="+link+"&oldcode="+id,
   				error:function(msg){
     				alert( "Error !: " + msg );
   				},
   				success:function(data){
   					//affiche le contenu du fichier dans le conteneur d&eacute;di&eacute;
					$('<div></div>').html(data).dialog({
					title: "Update",
					modal:true,
					height:400,
					width : 920
					});
				}
			});
			
			});
</script>