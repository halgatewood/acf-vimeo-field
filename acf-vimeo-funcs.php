<?php

define('ACF_VIMEO_REG_EX', apply_filters( 'acf_youtube_reg_ex', "/(?:www\.)?vimeo.com\/([0-9a-z\-_]+)/" ));
define('ACF_VIMEO_TRANSIENT_TIME', apply_filters( 'acf_vimeo_transient_time', 28800 ));


function acf_vimeo_parse_vimeo_id( $data )
{
	// IF EMPTY, RETURN FALSE
	if( !trim($data) ) return false;
	
	// IF NUMBER JUST RETURN
	if ( is_numeric($data) )
	{
		return $data;
	}

	preg_match( ACF_VIMEO_REG_EX, $data, $matches);
	
	// IS MATCH REG EX
	if( isset($matches[1]) AND is_numeric($matches[1]))
	{
		return $matches[1];
	}
	
	// GET LAST OF URL
	$explode_data = explode("/", $data);
	$last_piece = $explode_data[ count($explode_data) - 1 ];
	if(!$last_piece) { $last_piece = $explode_data[ count($explode_data) - 2 ]; }
	
	if( is_numeric($last_piece) ) return $last_piece;
	
	return false;
}


// CACHE OR BUILD
function acf_vimeo_ping_api( $vimeo_id )
{
	// CHECK FOR TRANSIENT
	$value = false;
	$transient_name = "acf_vimeo_" . $vimeo_id;
	
	if( get_transient( $transient_name ) )
	{
		// SET TRANSIENT
		$value = get_transient( $transient_name );
	}
	else
	{
	    // GET DATA
	    $value = acf_vimeo_build_video_object( $vimeo_id );

		// SET TRANSIENT
		set_transient( $transient_name, $value, ACF_VIMEO_TRANSIENT_TIME );
	}
	
	return $value;
}


// MAKE A VIDEO OBJECT
function acf_vimeo_build_video_object( $vimeo_id )
{
	// GET VIMEO VIDEO FROM API
	$video = new stdClass();
	
	// IF WE HAVE DATA FROM API USE ADD IT TO THE OBJECT
	$request = wp_remote_get( "http://vimeo.com/api/v2/video/" . $vimeo_id . ".json" );
	if( !is_wp_error($request) || wp_remote_retrieve_response_code($request) === 200 )
	{
		$request = json_decode( wp_remote_retrieve_body( $request ) );
	
		// VIDEO DATA IS IN THE ENTRY OBJECT OF THE API
		if( isset($request[0]) )
		{
			$video = $request[0];
			$video->length 			= acf_vimeo_seconds_to_duration( $video->duration );
		}
	}
	
	// DEFAULTS
	$video->data_source = "acf";
	$video->vimeo_id = $vimeo_id;

	return $video;
}


// CONVERTS SECONDS TO UNIX TIME
function acf_vimeo_seconds_to_time($time)
{
	if(is_numeric($time))
	{
		$value = array("years" => 0,"days" => 0,"hours" => 0,"minutes" => 0,"seconds" => 0);
		if($time >= 31556926){ $value["years"] = floor($time/31556926); $time = ($time%31556926); }
		if($time >= 86400){ $value["days"] = floor($time/86400); $time = ($time%86400); }
		if($time >= 3600){ $value["hours"] = floor($time/3600); $time = ($time%3600); }
		if($time >= 60){ $value["minutes"] = floor($time/60); $time = ($time%60); }
		$value["seconds"] = floor($time);
		return (array) $value;
	}
	else
	{
		return (bool) FALSE;
	}
}


// CONVERTS SECONDS TO A NICELY FORMATED TIME LIKE 12:34:56
function acf_vimeo_seconds_to_duration($seconds)
{
	$length_array = acf_vimeo_seconds_to_time($seconds);
	
	$length = "";
	if($length_array['hours'] != "") { $length = $length_array['hours'] . ":"; }
	if($length_array['minutes'] < 1) $length_array['minutes'] = "0";
	$length .= $length_array['minutes'] . ":";
	if($length_array['seconds'] < 10) { $length_array['seconds'] = "0" . $length_array['seconds']; }
	$length .= $length_array['seconds'];
	
	return $length;
}