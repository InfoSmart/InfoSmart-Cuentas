<?
/**
 * BeatRock
 *
 * Framework para el desarrollo de aplicaciones web.
 *
 * @author 		Iván Bravo <webmaster@infosmart.mx> @Kolesias123
 * @copyright 	InfoSmart 2013. Todos los derechos reservados.
 * @license 	http://creativecommons.org/licenses/by-sa/2.5/mx/  Creative Commons "Atribución-Licenciamiento Recíproco"
 * @link 		http://beatrock.infosmart.mx/
 * @version 	3.0
 *
 * @package 	Tables
 * Permite definir los nombres de las tablas de tu base de datos, de esta forma si reenombras una
 * solo cambialo desde aquí :)
 *
 * !!!NOTA: DP/DA ya estan reservados ¡no los uses!
 *
*/

# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

# Tablas de tu aplicación:
$tables = array(
);

SQL::TableName($tables);

# Tablas de BeatRock.
$tables = array(
	'backups' 			=> 'site_backups_servers',
	'cache' 			=> 'site_cache',
	'config' 			=> 'site_config',
	'countrys' 			=> 'site_countrys',
	'errors' 			=> 'site_errors',
	'logs' 				=> 'site_logs',
	'maps' 				=> 'site_maps',
	'news' 				=> 'site_news',
	'timers' 			=> 'site_timers',
	'visits' 			=> 'site_visits',
	'visits_total' 		=> 'site_visits_total',
	'users' 			=> 'users',
	'users_services' 	=> 'users_services',
	'wordsfilter' 		=> 'wordsfilter'
);

SQL::TableName($tables);