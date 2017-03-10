<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
* @Method createScriptOrLinkTag
* @param file absolute/relative path
* @param Tag type script/stylesheet(Default css)
* @param media type
* @return html tag
*/

function createScriptOrLinkTag( $filePath , $type = 'stylesheet' , $mediaScreen = null){

	$mediaScreen    = (!empty($mediaScreen))?"media='screen'":'';
	switch($type){
		case 'script':
			$htmlTag = "<script src='{$filePath}?ver=".filemtime($filePath)."' type='text/javascript'></script>";
			break;
		case 'stylesheet':
			$htmlTag = "<link href='{$filePath}?ver=".filemtime($filePath)."' rel='stylesheet' type='text/css' {$mediaScreen}>";
			break;
	}
	echo $htmlTag;
}

function checkNegativeValue( $value = null ) 
{
	if ( $value < 0 ) {
		$value = '-$'.abs($value);
	} else {
		$value = '$'.$value;
	}

	echo $value;
}