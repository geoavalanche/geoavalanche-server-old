<?
/*
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @package	OpenGeoSMS - http://www.facebook.com/OpenGeoSMS
 * @author	GeoThings Tech <http://geothings.tw>	
 * @copyright	GeoThings Tech <http://geothings.tw>	
 * @license	http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
 */
class OpenGeoSms
{

	private 
		$lat, 
		$lng, 
		$queries, 
		$raw_str, 
		$url, 
		$body;

	public function __construct($str)
	{

		$raw_parts = explode("\n", $str, 2);
		$url_parts = parse_url($raw_parts[0]);

		if ( $url_parts == FALSE )
		       	throw new Exception("invalid URL");		

		if ( !isset($url_parts["query"]) )
		{
			throw new Exception("URL has no query part");
		}

		$query_parts = explode("&", $url_parts["query"]);
		$queries = array();

		foreach ($query_parts as $q)
		{
			$queries[] = explode("=", $q);
		}

		$qlen = count($queries);		
		
		if ( $qlen < 2 )
			throw new Exception("not enough number of querie parameters");

		if ( $queries[$qlen-1][0] != "GeoSMS" )
			throw new Exception("last query paramenter name is not GeoSMS");

		if ( count($queries[0]) != 2 )
			throw new Exception("first query parameter has no value");

		$latlng = explode(",", $queries[0][1]);
		
		if ( count($latlng) != 2 )	 
			throw new Exception("first query paramter contains invalid value");

		$this->lat = self::double_or_throw($latlng[0]);
		$this->lng = self::double_or_throw($latlng[1]);
		$this->queries = $queries;
		$this->raw_str = $str;
		$this->url = $raw_parts[0];

		if ( count($raw_parts) > 1 )
		{
			$this->body = $raw_parts[1];
		}else
		{
			$this->body = "";
		}
	}

	public function get_lat()
	{
		return $this->lat;
	}
	
	public function get_lng()
	{
		return $this->lng;
	}

	public function get_raw_str()
	{
		return $this->raw_str;
	}

	public function get_body()
	{
		return $this->body;
	}

	public function get_url()
	{
		return $this->url;
	}

	
	private static function double_or_throw($str)
	{
		if ( self::is_double_str($str) )
		{
			return $str;
		}
		throw new Exception($str." is not a double value");
	}	
	private static function is_double_str($str)
	{
		return preg_match( "#^([\+\-]|)([0-9]*)(\.([0-9]*?)|)(0*)$#", $str) == 1;
	}
}
