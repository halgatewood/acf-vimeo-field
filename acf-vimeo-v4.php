<?php

class acf_field_vimeo extends acf_field
{
	var $settings, $defaults;
		
	function __construct()
	{
		$this->name 							= 'vimeo';
		$this->label 							= __('Vimeo Video', 'acf-vimeo');
		$this->category 						= apply_filters( 'acf_vimeo_category', __("Basic",'acf') );
		$this->transient_time 					= ACF_VIMEO_TRANSIENT_TIME;
		$this->reg_ex 							= ACF_VIMEO_REG_EX;
		$this->defaults 						= array( 'return_format' => 'embed' );

    	parent::__construct(); // do not delete!
    	
		$this->settings = array(
			'path' => apply_filters('acf/helpers/get_path', __FILE__),
			'dir' => apply_filters('acf/helpers/get_dir', __FILE__),
			'version' => '1.0.0'
		);

	}
	
	function create_options( $field ) 
	{ 
		$key = $field['name'];
	?>
		<tr id="acf-vimeo-return-value-<?php echo $key; ?>" class="field_option field_option_<?php echo $this->name; ?>">
			<td class="label">
				<label><?php _e("Return Value",'acf-vimeo'); ?></label>
				<p><?php _e("Type of data returned when using the_field()",'acf-vimeo') ?></p>
			</td>
			<td>
				<?php 
				do_action('acf/create_field', array(
					'type' 		=> 'radio',
					'name'		=>	'fields['.$key.'][return_format]',
					'value'		=>	$field['return_format'],
					'layout' 	=>	'horizontal',
					'choices' 	=> array(
						'id'				=>	__("Vimeo ID",'acf-vimeo'),
						'embed'				=>	__("Embed",'acf-vimeo'),
						'object'			=>	__("Video Object",'acf-vimeo'),
						'thumbnail-object'	=>	__("Thumbnail Object",'acf-vimeo')
					)
				));
				?>
			</td>
		</tr>
	<?php 
	}
	
	function create_field( $field )
	{
		// VARS
		$e = '';
		$o = array( 'id', 'class', 'name', 'value' );
	
		$e .= '<div class="acf-input-wrap">';
		$e .= '<input type="text"';
		
		foreach( $o as $k )
		{
			$e .= ' ' . $k . '="' . esc_attr( $field[ $k ] ) . '"';	
		}
		
		$e .= ' />';
		$e .= '</div>';
		
		$e .= '<div id="acf-vimeo-preview-' . $field['id'] . '" style="padding-top: 10px;">';

		if( $field['value'] )
		{
			$current_vimeo_id = acf_vimeo_parse_vimeo_id( $field['value'] );
			if($current_vimeo_id)
			{
				$e .= '<iframe src="//player.vimeo.com/video/' . $current_vimeo_id . '" width="300" height="169" style="max-width: 100%;" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
			}
		}
		
		$e .= '</div>';
		
		$e .= "
		
			<script>	
			
				function vimeo_parser(url)
				{
					if( jQuery.isNumeric(url) ) return url;
				
					var regExp = {$this->reg_ex};
					var match = url.match(regExp);

					// IF MATCH REG EX
					if (match && jQuery.isNumeric(match[1]))
					{
						return match[1];
					}
					
					// EXPLODE URL AND GET LAST, CHECK FOR NUMERIC
					var ex_url = url.split('/');
					if( jQuery.isNumeric( ex_url[ex_url.length-1] ) )
					{
						return ex_url[ex_url.length-1];
					}
					
					if( !ex_url[ex_url.length-1] && jQuery.isNumeric( ex_url[ex_url.length-2] ))
					{
						return ex_url[ex_url.length-2];
					}
					
					return false;
				}

				jQuery('input#" . $field['id'] . "').on('input', function() 
				{
					var acf_vimeo_field_input = jQuery(this).val();
					var acf_check_for_video = vimeo_parser( acf_vimeo_field_input );
		
					if( acf_check_for_video )
					{
						 jQuery('#acf-vimeo-preview-" . $field['id'] . "').html('<iframe src=\"//player.vimeo.com/video/' + acf_check_for_video + '\" width=\"300\" height=\"169\" style=\"max-width: 100%;\" frameborder=\"0\" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>');
					}
					else
					{
						jQuery('#acf-vimeo-preview-" . $field['id'] . "').html('');
					}			
				
				});
		
			</script>
		
		";
		
		echo $e;
	}
	
	function format_value_for_api( $value, $post_id, $field )
	{
		$vimeo_id = acf_vimeo_parse_vimeo_id( $value );
	
	
		// IF NO VIDEO ID RETURN NOTHING
		if( !$vimeo_id ) { return false; }
		
		
		// RETURN EMBED
		if( $field['return_format'] == "embed" )
		{
			return apply_filters('acf_vimeo_embed', '<iframe src="//player.vimeo.com/video/' . $vimeo_id . '" width="500" height="281" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>', $vimeo_id); 
		}
		
		
		// RETURN THUMBNAIL
		if( $field['return_format'] == "thumbnail-object" )
		{
			$thumbnails = new stdClass();
			
			$video_object = acf_vimeo_ping_api( $vimeo_id );

			if($video_object)
			{
				$thumbnails->small 		= $video_object->thumbnail_small;
				$thumbnails->medium 	= $video_object->thumbnail_medium;
				$thumbnails->large 		= $video_object->thumbnail_large;
			}
			
			return $thumbnails;
		}


		// RETURN JUST ID
		if( $field['return_format'] == "id" )
		{
			return $vimeo_id; 
		}
		
		
		// RETURN A VIMEO OBJECT
		if( $field['return_format'] == "object" )
		{
			$value = acf_vimeo_ping_api( $vimeo_id );
		}
		
		return $value;
	}

}


// CREATE FIELD
new acf_field_vimeo();
?>