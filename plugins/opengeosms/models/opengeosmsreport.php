<?php defined('SYSPATH') or die('No direct script access.');
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

class OpenGeoSmsReport_Model extends ORM
{
	protected $belongs_to = array("message");
	protected $table_name = 'opengeosmsreport';
}
