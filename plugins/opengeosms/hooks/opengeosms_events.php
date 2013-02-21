<?php defined('SYSPATH') or die('No direct script access.');
/**
 * OpenGeoSMS Hook
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	Harry C.W. Li <harryli@itri.org.tw>
 * @author	GeoThings Tech <http://geothings.tw>	
 * @package    	OpenGeoSMS - http://www.facebook.com/OpenGeoSMS
 * @module	OpenGeoSMS Hook	
 * @copyright  	Industrial Technology Research Institute - http://www.itri.org.tw
 * @license    	http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
* 
*/


class opengeosms_events {
	
	/**
	 * Registers the main event add method
	 */
	public function __construct()
	{	
		// Hook into routing
		$this->db = Database::instance();
		Event::add('system.pre_controller', array($this, 'add'));
	}
	
	/**
	 * Adds all the events to the main Ushahidi application
	 */
	public function add()
	{
		Event::add('ushahidi_action.message_sms_add', array($this, 'process_sms'));
		Event::add('ushahidi_action.report_extra', array($this, 'send_opengeosms'));
	}
	
	public function send_opengeosms()
	{
		$incident_id = Event::$data;
		
		$incident = ORM::factory('incident')
					->where('id', $incident_id)
					->where('incident_active', 1)
					->find();
		
		$defeult_text = "";
		if ( $incident->id != 0 )
		{
			$defeult_text = $incident->incident_title;
		}
		
		$view = View::factory('send_opengeosms_form');
		$view->action_url = Kohana::config('core.site_domain') . "opengeosms/send";
		$view->incident_id = $incident_id;
		$view->text = $defeult_text;
		$view->render(TRUE);
	}

	public function process_sms()
	{
		$sms = Event::$data;
		try
		{
			$ogsr = new OpenGeoSmsReport($sms->message);			
			$_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
			$sms->incident_id = $ogsr->create();
			$sms->save();
			$model = new OpenGeoSmsReport_Model();
			$model->message_id = $sms->id;
			$model->save();
		}
		catch(Exception $e)
		{
			Kohana::log("error", "Open GeoSMS report:".$e->getMessage());
		}
		
	}
	
	
}

new opengeosms_events;
