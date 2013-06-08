<?
if ( !defined('BEATROCK') )
	exit;

class Accounts
{
	static function NameBlocked($name)
	{
		$name = strtolower($name);
		$count = Rows("SELECT null FROM {DP}namesblocked WHERE name = '$name' LIMIT 1");
		return ( $count > 0 ) ? true : false;
	}

	static function GetConnections()
	{
		return q("SELECT * FROM {DP}site_connections");
	}
}