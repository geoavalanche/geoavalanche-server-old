<?php defined('SYSPATH') or die('No direct script access.');
/**
 * OpenGeoSMS Controller
 *
 * PHP version 5
 * LICENSE: This source file is subject to LGPL license 
 * that is available through the world-wide-web at the following URI:
 * http://www.gnu.org/copyleft/lesser.html
 * @author	   Harry C.W. Li <harryli@itri.org.tw>
 * @author	GeoThings Tech <http://geothings.tw> 
 * @package    OpenGeoSMS - http://www.facebook.com/OpenGeoSMS
 * @module	   OpenGeoSMS Controller	
 * @copyright  Industrial Technology Research Institute - http://www.itri.org.tw
 * @copyright 	GeoThings Tech <http://geothings.tw> 
 * @license    http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License (LGPL) 
* 
*/


class OpenGeoSMS_Controller extends Template_Controller
{
	public $template = '';
	
	public function index()
	{
		url::redirect('reports');
	}
	
	public function send()
	{
		$post = new Validation($_POST);
		$post->pre_filter('trim', TRUE);

		$post->add_rules('incident_id', 'required');
		$post->add_rules('phone','required');
		$post->add_rules('text','required');
		
		$result_message = "";
		
		if( $post->validate() )
		{
			$incident_id =$post->incident_id;
			$phone = $post->phone;
			$text = $post->text;
			
			$incident = ORM::factory('incident')
						->where('id', $incident_id)
						->where('incident_active', 1)
						->find();
			
			if ( $incident->id != 0 )
			{
				//get incident location.
				$lat = round($incident->location->latitude, 6);
				$lon = round($incident->location->longitude, 6);
				
				//send the sms
				$sms_message = "http://maps.google.com.tw/?q=$lat,$lon&GeoSMS\n$text";
				$r = sms::send($phone, "", $sms_message);
				if($r == '1')
				{
					$result_message = "SMS sent success!";
				}
				else
				{
					$result_message = $r;
				}
			}
			else
			{
				$result_message = "Check input fields.";
			}
		}
		else
		{
			$result_message = "Check input fields.";
		}
		
		$this->template = new View('send_action');
		$this->template->result_message = $result_message;
	}

	const RESP_CODE_SUCCESS=0;
	const RESP_CODE_MISSING_PARAM=1;
	const RESP_CODE_INVALID_PARAM=2;
	const RESP_CODE_FORM_POST_FAILED=3;
	const RESP_CODE_NOT_POST=4;
	const RESP_CODE_NO_DATA=7;
	const RESP_CODE_UNKNOWN=11;

	private function respond_raw($msg)
	{
		$this->auto_render = FALSE;
		echo $msg;
	}
	private function respond($code, $msg, $extra=array())
	{
		$payload = array_merge(
			$extra,
			array(
				"domain" => url::base(),
				"success" => $code?"false":"true"
			)
		);
		$resp = array(
			"payload" => $payload,
			"error" => array(
				"code" => $code, // /api?version=task produces a string...
				"message" => $msg
			)
		);
		$this->respond_raw(json_encode($resp));

	}
	private function respond_missing_param($msg)
	{
		$this->respond(
			self::RESP_CODE_MISSING_PARAM,
			"post failed:\n".$msg
		);
	}

	private function respond_invalid_param($msg)
	{
		$this->respond(self::RESP_CODE_INVALID_PARAM, $msg);
	}

	private function respond_post_failed($msg)
	{
		$this->respond(self::RESP_CODE_FORM_POST_FAILED, $msg);
	}

	private function respond_success($msg)
	{
		$this->respond(
			self::RESP_CODE_SUCCESS, 
			$msg
		);
	}
	private function respond_not_post()
	{
		$this->respond(self::RESP_CODE_NOT_POST, "POST expected");
			
	}
	public function version()
	{
		$meta = plugin::meta("opengeosms");
		$this->respond_raw($meta["plugin_version"]);
	}
	public function attach()
	{

		
		if ($_SERVER['REQUEST_METHOD'] != "POST") 
		{
			$this->respond_not_post();
			return;
		}
		
		if ( !isset($_POST["m"]))
		{
			$this->respond_missing_param("missing post field m");
			return;
		}
		try
		{
			$report = ORM::factory("opengeosmsreport")
				->with("message")
				->where("message.message", $_POST["m"])
				->where("message.incident_id >", 0)
				->find();
			if ( !$report->loaded )
			{
				throw new Exception("message not found");	
			}
			OpenGeoSmsReport::attach($report->message->incident_id);
			$this->respond_success("photo attached");

		}
		catch(Exception $e)
		{
			$this->respond_invalid_param("photo attach failed:\n".$e->getMessage());
		}

	}

}
