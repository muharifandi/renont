<?php

defined('BASEPATH') OR exit('No direct script access allowed');

if ( ! function_exists('resize_image'))
{
	
	function resize_image($original_path,$new_path,$width,$height,$ratio)
	{
		$CI =& get_instance();		
		$config['image_library']='gd2';
		$config['source_image']= $original_path;
		
		if($ratio)
			$config['master_dim'] = 'width';
		
		$config['maintain_ratio']= $ratio;
		//$config['quality']= '50%';
		$config['width']= $width;
		$config['height']= $height;
		$config['new_image']= $new_path;
		$CI->load->library('image_lib');
		$CI->image_lib->clear();
		$CI->image_lib->initialize($config);
		$CI->image_lib->resize();
		return basename($new_path);
	}
	
	function thumb_image($original_path,$new_path,$dimension)
	{
		$CI =& get_instance();		
		$config['image_library'] = 'gd2';
		$config['source_image'] = $original_path;
		$config['new_image']= $new_path;
		$config['create_thumb'] = TRUE;
		$config['thumb_marker'] = '';
		$config['maintain_ratio'] = FALSE;

		$img = imagecreatefromjpeg($original_path);
		$_width = imagesx($img);
		$_height = imagesy($img);

		$img_type = '';
		$thumb_size = $dimension;

		if ($_width > $_height)
		{
			// wide image
			$config['width'] = intval(($_width / $_height) * $thumb_size);
			if ($config['width'] % 2 != 0)
			{
				$config['width']++;
			}
			$config['height'] = $thumb_size;
			$img_type = 'wide';
		}
		else if ($_width < $_height)
		{
			// landscape image
			$config['width'] = $thumb_size;
			$config['height'] = intval(($_height / $_width) * $thumb_size);
			if ($config['height'] % 2 != 0)
			{
				$config['height']++;
			}
			$img_type = 'landscape';
		}
		else
		{
			// square image
			$config['width'] = $thumb_size;
			$config['height'] = $thumb_size;
			$img_type = 'square';
		}

		$CI->load->library('image_lib');
		$CI->image_lib->initialize($config);
		$CI->image_lib->resize();

		// reconfigure the image lib for cropping
		$conf_new = array(
			'image_library' => 'gd2',
			'source_image' => $new_path,
			'create_thumb' => FALSE,
			'maintain_ratio' => FALSE,
			'width' => $thumb_size,
			'height' => $thumb_size
		);

		if ($img_type == 'wide')
		{
			$conf_new['x_axis'] = ($config['width'] - $thumb_size) / 2 ;
			$conf_new['y_axis'] = 0;
		}
		else if($img_type == 'landscape')
		{
			$conf_new['x_axis'] = 0;
			$conf_new['y_axis'] = ($config['height'] - $thumb_size) / 2;
		}
		else
		{
			$conf_new['x_axis'] = 0;
			$conf_new['y_axis'] = 0;
		}

		$CI->image_lib->initialize($conf_new);

		$CI->image_lib->crop();
		return basename($new_path);
	}
}

