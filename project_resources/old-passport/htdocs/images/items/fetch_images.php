<?php
	/* Settings */
	define('CACHE_ROOT', '.');
	define('CACHE_LENGTH', 60 * 3600); // 60 minutes
	define('DB_USER', 'reportuser');
	define('DB_PASSWORD', 'report');
	define('DB_URL', '//flash.earthboundtrading.com/rproods');
	
	/* Profiles */
	$profiles = array(
		'unscaled' => (object)array(
			'size' 		=> false,
			'fillColor' => false),
		'thumb' => (object)array(
			'size' 		=> 96,
			'fillColor' => array(255, 255, 255)),
		'large' => (object)array(
			'size' 		=> 400,
			'fillColor' => false));
	
	$defaultProfileName = 'unscaled'; // Default Profile
	
	/* Parse request */
	$url = $_SERVER['REQUEST_URI'];
	$filename = basename($url);
	$itemNo = intval(substr($filename, 0, -4));
	
	/* Select Profile */
	$profileName = basename(dirname($url));
	
	if (!isset($profiles[$profileName]))
		$profileName = $defaultProfileName;
	
	$profile = $profiles[$profileName];
	
	if (!$itemNo )
		handleError('No item specified.');
		
	$cachePath = CACHE_ROOT . "/{$profileName}/$filename";
	
	if (!file_exists($cachePath) || time() - filemtime($cachePath) > CACHE_LENGTH) {
		if (!$imageData = createImageCache($itemNo, $profile->size, $profile->fillColor))
			handleError('Unable to cache image.');
		else
			file_put_contents($cachePath, $imageData);
	}
	
	/* Display Image */
	header('Content-Type: image/jpg');
	echo file_get_contents($cachePath);
	
	function handleError($message) {
		die($message);	
	}
	
	function createImageCache($itemNo, $size, $fillColor) {
		if ($imageData = getRawImage($itemNo)) {
			if ($size || $fillColor)
				return resizeImage($imageData, $size, $fillColor);
			else
				return $imageData;
		}
		
		return false;
	}
	
	function getRawImage($itemNo) {
		//Query SQL to get the image
		$getImageSql = 
			'SELECT i.image INTO :imageLob
			FROM 
				cms.inventory i LEFT JOIN 
				cms.invn_sbs sbs ON (i.item_sid = sbs.item_sid) 
			WHERE 
				sbs.sbs_no = 1 AND sbs.active = 1 AND sbs.item_no = :itemNo';
		
		// Connect to Oracle
		if (!$conn = oci_connect(DB_USER, DB_PASSWORD, DB_URL))
			return false;
		
		// Prepare the query
		if (!$stmt = oci_parse($conn, $getImageSql))
			return false;
			
		oci_bind_by_name($stmt, ':itemNo', $itemNo);
		oci_bind_by_name($stmt, ':imageLob', $imageLob, -1, SQLT_BLOB);
		
		// Read the image data
		if (!oci_execute($stmt))
			return false;
		
		$imageRow = oci_fetch_array($stmt, OCI_ASSOC);
		$imageData = $imageRow['IMAGE']->load();
		oci_free_statement($stmt);
		
		return $imageData;
	}
	
	function resizeImage($image_data, $new_size, $fillColor) {
		$square = $fillColor !== false;
		
		$gd_old_img = imagecreatefromstring($image_data);
		$old_width = imagesx($gd_old_img);
		$old_height = imagesy($gd_old_img);
		
		$new_width = $new_height = $new_size;
		$x_offset = $y_offset = 0;
		
		if ($old_height > $old_width) {
			$new_width = (int)((float)$new_width * ((float)$old_width / $old_height));
			if ($square)
				$x_offset = (int)(($new_size - $new_width) / 2);
		} else if ($old_height < $old_width) {
			$new_height = (int)((float)$new_height * ((float)$old_height / $old_width));		
			if ($square)
				$y_offset = (int)(($new_size - $new_height) / 2);
		}			
			
		$gd_new_img = ($square ? 
			imagecreatetruecolor($new_size, $new_size) : 
			imagecreatetruecolor($new_width, $new_height));
		
		if ($square) {
			$bg_color = imagecolorallocate($gd_new_img, $fillColor[0], $fillColor[1], $fillColor[2]);
			imagefill($gd_new_img, 0, 0, $bg_color);
			imagecolordeallocate($gd_new_img, $bg_color);
		}
		
		imagecopyresampled(
			$gd_new_img, $gd_old_img, 
			$x_offset, $y_offset, 
			0, 0, 
			$new_width, $new_height, 
			$old_width, $old_height);
		
		ob_start();
		imagejpeg($gd_new_img);
		$scaled_image_data = ob_get_contents();
		ob_end_clean();
		
		imagedestroy($gd_old_img);
		imagedestroy($gd_new_img);
		
		return $scaled_image_data;
	}