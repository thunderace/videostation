<?php
	$APP_NAME = "Video Station";
	$VERSION = "1.0-020";
	$USER_SQL = "root";
	$PASSWORD_SQL = "rejane";
	$DATABASE = "video";
	$EXT = array("avi","mkv","mpg","mov","m2ts");
	$HIDDEN_FILES = array(".","..","index.php","index.php~","index.css",".htaccess","Thumbs.db","@eaDir",".DS_Store","images","css","js");
	$DELETED_WORDS = array("seq", "hd", "720", "1080", "720p", "1080p","m2ts","avi","mkv","mpg","mov","dvdrip","truefrench","french","xvid","divx","vostfr","hdtv","x264","bluray","dts","multi");
	$VIDEO_DIR = "/volume1/video";
	$SERIES_DIR = "/volume1/video/Series/";
	$MOVIES_DATABASE = "TMDb" ;
	$SERIES_DATABASE = "Allocine" ;
	$LANGUAGE = "fr";
	$SECURE = FALSE;
	$LOGIN = TRUE;
    $MODAL = TRUE;
    $ALWAYS_UPDATE = FALSE;
	$FTP = FALSE;
	$INDEXATION_AUTO = TRUE;
    $TRAILER = FALSE;
    $POSTER_WITH_VIDEO = TRUE;
    $CLEAN_AND_RENAME_MOVIES = TRUE;
    $SAFE_MODE=TRUE;
?>
