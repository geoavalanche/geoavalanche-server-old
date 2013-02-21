<?
/*
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @package	OpenGeoSMS - http://www.facebook.com/OpenGeoSMS
 * @module	OpenGeoSMS Controller
 * @author	GeoThings Tech <http://geothings.tw>	
 * @copyright	GeoThings Tech <http://geothings.tw>	
 * @license	http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
class OpenGeoSmsReport{

	private 
		$title, 
		$description, 
		$catetory, 
		$open_geosms,
		$date, 
		$hour, 
		$minute, 
		$ampm,
		$location_name;

	public function get_title()
	{
		return $this->title;
	}
	public function get_description()
	{
		return $this->description;
	}
	public function get_category()
	{
		return $this->category;
	}
	public function get_open_geosms()
	{
		return $this->open_geosms;
	}
	public function get_date()
	{
		return $this->date;
	}
	public function get_hour()
	{
		return $this->hour;
	}
	public function get_minute()
	{
		return $this->minute;
	}
	public function get_ampm()
	{
		return $this->ampm;
	}
	public function get_time_str()
	{
		return $this->get_date()." ".
			$this->get_hour().":".
			$this->get_minute()." ".
			$this->get_ampm();
	}
	public function get_location_name()
	{
		return $this->location_name;
	}
	private static function rexplode($delim, $str, $max)
	{
		$ret_r = explode($delim, strrev($str), $max);
		$ret = array();
		while( count($ret_r) > 0 )
		{
			$ret[] = strrev(array_pop($ret_r));
		}
		return $ret;
	}
	public function __construct($raw_str)
	{
		
		$ogs = new OpenGeoSms($raw_str);
		$this->open_geosms = $ogs;
		$body_parts = explode("\n", $ogs->get_body(), 3);

		if ( count($body_parts) != 3 )
		{
			throw new Exception("invalid report");
		}
	       
		$time_split = self::rexplode("@", $body_parts[0], 2);
		if ( count($time_split) != 2 )
		{
			throw new Exception("failed parsing time field");
		}

		$time = $time_split[1];
		$cat_split = self::rexplode("#", $time_split[0], 2);

		if ( count($cat_split) != 2 )
		{
			throw new Exception("failed parsing category field");
		}
		
		$this->title = $cat_split[0];
		$this->category = $cat_split[1];
		$location_name = $body_parts[1];
		$this->description = $body_parts[2];

		if ( $time == "" )
		{
			$time = date("m/d/Y h:i a");
		}

		if ( strlen($location_name) < 3 )
		{
			$location_name .= "+++";
		}
		$this->location_name = $location_name;

		$time_parts = explode(" ", $time);		
		if( count($time_parts) != 3)
		{
			throw new Exception("invalid time: ".$time);
		}	
		
		$this->date = $time_parts[0];
		$hhmm = $time_parts[1];
		$this->ampm = strtolower($time_parts[2]);

		$hhmm_parts = explode(":", $hhmm);
		if(count($hhmm_parts) != 2)
		{
			throw new Exception("invalid time: ".$hhmm);
		}

		$this->hour = $hhmm_parts[0];
		$this->minute = $hhmm_parts[1];
	
	}

	public function create($extra=array())
	{
		// create an empty entry in $_FILES for save_media to work	
		if ( !isset($_FILES['incident_photo']) )
		{
			$_FILES['incident_photo']=array(
				'name' => 'dummy',
				'tmp_name' => array()
			);
		}
		return self::save_report($this->to_post_array($extra));
	}

	private function to_post_array($extra=array())
	{
		
		$ogs = $this->get_open_geosms();
		
		$report = array(
			'task' => 'report',
			'incident_title' => $this->title,
			'incident_description' => $this->description,
			'incident_date' => $this->date,
			'incident_hour' => $this->hour,
			'incident_minute' => $this->minute,
			'incident_ampm' => $this->ampm,
			'incident_category' => $this->category,
			'latitude' => $ogs->get_lat(),
			'longitude' => $ogs->get_lng(),
			'location_name' => $this->location_name,
		);
		return array_merge(
			self::empty_array('',
				'person_first',
				'person_last',
				'person_email'
			),
			self::empty_array(array(),
				'incident_news',
				'incident_video'
			),
			$extra,
			$report
		);
	}
	private static function empty_array($val, $keys)
	{
		$ret = array();
		for ( $i = 1; $i < func_num_args(); $i++)
		{
			$ret[func_get_arg($i)]=$val;
		}
		return $ret;
	}

	private static function geocode()
	{
		$url = "http://maps.google.com/maps/api/geocode/xml?latlng=".
			$ogs->get_lat_str().",".
			$ogs->get_lng_str().
			"&sensor=false";

		$address_xml = simplexml_load_file( $url );
		if($address_xml && count($address_xml->result) > 0)
		{
			$location_name = $address_xml->result->formatted_address;
		}
	}

	public static function attach($incident_id)
	{
		self::add_photo(new Incident_Model($incident_id));
	}

	
	private static function add_photo($incident)
	{
		if ( empty($_FILES['incident_photo']))
		{
			throw new Exception("no photos uploaded");
		}
		$filenames = upload::save('incident_photo');
		$i = 1;

		foreach ($filenames as $filename)
		{
			$new_filename = $incident->id.'_'.$i.'_'.time();

			$file_type = strrev(substr(strrev($filename),0,4));

			// IMAGE SIZES: 800X600, 400X300, 89X59
			// Catch any errors from corrupt image files
			try
			{
				// Large size
				Image::factory($filename)->resize(800,600,Image::AUTO)
				->save(Kohana::config('upload.directory', TRUE).$new_filename.$file_type);

				// Medium size
				Image::factory($filename)->resize(400,300,Image::HEIGHT)
				->save(Kohana::config('upload.directory', TRUE).$new_filename.'_m'.$file_type);

				// Thumbnail
				Image::factory($filename)->resize(89,59,Image::HEIGHT)
				->save(Kohana::config('upload.directory', TRUE).$new_filename.'_t'.$file_type);
			}
			catch (Kohana_Exception $e)
			{
				// Do nothing. Too late to throw errors
			}

			// Name the files for the DB
			$media_link = $new_filename.$file_type;
			$media_medium = $new_filename.'_m'.$file_type;
			$media_thumb = $new_filename.'_t'.$file_type;
			// Remove the temporary file
			unlink($filename);

			// Save to DB
			$photo = new Media_Model();
			$photo->location_id = $incident->location_id;
			$photo->incident_id = $incident->id;
			$photo->media_type = 1; // Images
			$photo->media_link = $media_link;
			$photo->media_medium = $media_medium;
			$photo->media_thumb = $media_thumb;
			$photo->media_date = date("Y-m-d H:i:s",time());
			$photo->save();
			$i++;
		}
		return $incident;
	}

	public static function get_validation_err_str($errs)
	{
		$ret = "";
		foreach($errs as $item => $desc)
		{
			$ret .= $item." - ".$desc."\n";
		}
		return $ret;

	}
	private static function err_str($post){
		return "form validation failed:\n".
			self::get_validation_err_str($post->errors("report"));
	}
	private static function save_report($post)
	{
		$post['incident_category'] = explode(',', $post['incident_category']);
		if (!reports::validate($post))
		{
			throw new Exception(self::err_str($post));
		}
	
		// STEP 1: SAVE LOCATION
		$location = new Location_Model();
		reports::save_location($post, $location);

		// STEP 2: SAVE INCIDENT
		$incident = new Incident_Model();
		reports::save_report($post, $incident, $location->id);

		// STEP 2c: SAVE INCIDENT GEOMETRIES
		reports::save_report_geometry($post, $incident);

		// STEP 3: SAVE CATEGORIES
		reports::save_category($post, $incident);

		// STEP 4: SAVE MEDIA
		reports::save_media($post, $incident);

		// STEP 5: SAVE PERSONAL INFORMATION
		reports::save_personal_info($post, $incident);

		// Action::report_edit - Edited a Report
		Event::run('ushahidi_action.report_edit', $incident);

		return $incident;
	}
}
