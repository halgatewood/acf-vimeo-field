<?php
/*
Plugin Name: Advanced Custom Fields: Vimeo Field
Plugin URI: http://halgatewood.com/downloads/acf-vimeo-field
Description: This premium Add-on adds a vimeo field type for the Advanced Custom Fields plugin
Version: 2.0
Author: Hal Gatewood
Author URI: http://halgatewood.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


// LANGS
load_plugin_textdomain( 'acf-vimeo', false, dirname( plugin_basename(__FILE__) ) . '/lang/' ); 


// GLOBAL FUNCTIONS
include_once('acf-vimeo-funcs.php');


// VERSION 5+
function include_field_types_vimeo( $version ) 
{
	include_once('acf-vimeo-v5.php');	
}

add_action('acf/include_field_types', 'include_field_types_vimeo');	


// VERSION 4
function register_fields_vimeo() 
{
	include_once('acf-vimeo-v4.php');
}

add_action('acf/register_fields', 'register_fields_vimeo');