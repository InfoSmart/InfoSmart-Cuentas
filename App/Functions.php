<?
# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

function array_delete($value, $array)
{
	foreach ( $array as $key => $val )
	{
		if ( $val == $value )
			unset($array[$key]);
	}

	return $array;
}