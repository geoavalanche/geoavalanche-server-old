<?php defined('SYSPATH') or die('No direct script access.');
class Opengeosms_Install{
	
	public function __construct()
	{
		$this->db =  new Database();
	}

	private function table_name($name)
	{
		return Kohana::config('database.default.table_prefix').$name;
	}
	private function create_table($name, $cols)
	{
		$name = $this->table_name($name);
		$this->db->query("CREATE TABLE IF NOT EXISTS `".$name."`(".$cols.");");
	}

	private function drop_table($name)
	{
		$name = $this->table_name($name);
		$this->db->query("DROP TABLE ".$name.";");
	}

	private static $tables = array(
		"opengeosmsreport" => 
			"id bigint unsigned NOT NULL AUTO_INCREMENT,message_id bigint unsigned,PRIMARY KEY(`id`)"
	);

	public function run_install()
	{
		foreach( self::$tables as $name=>$cols)
		{
			$this->create_table($name, $cols);
		}
	}
	public function uninstall()
	{
		foreach( self::$tables as $name=>$cols)
		{
			$this->drop_table($name);
		}
	}
}
