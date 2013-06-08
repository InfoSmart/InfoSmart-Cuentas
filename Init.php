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
 * @package 	Init
 * Preparación del Kernel, encargado de iniciar y administrar
 * los procesos, módulos y controladores del sistema.
 *
*/

#############################################################
## PREPARACIÓN DE CONSTANTES Y OPCIONES INTERNAS
#############################################################

# Reporte de errores recomendado para comenzar.
error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);

# Permitir acciones internas.
define('BEATROCK', 	true);
define('START', 	microtime(true));

#define('DEBUG', 		true); // Descomente esta línea para imprimir mensajes de procesamiento.
#define('DEBUG_PHP', 	true); // Descomente esta línea para imprimir el código PHP de una vista.

# Información esencial del visitante.
define('IP', 	 	$_SERVER['REMOTE_ADDR']);
define('AGENT',  	$_SERVER['HTTP_USER_AGENT']);
define('FROM',   	$_SERVER['HTTP_REFERER']);
define('LANG', 	 	substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));

# No se ha podido obtener la codificación predeterminada.
if ( ini_get('default_charset') == '' )
	define('CHARSET',	'UTF-8');
# Usar la codificación del servidor.
else
	define('CHARSET',	strtoupper(ini_get('default_charset')));

# Dirección actual y uso del protocolo seguro.
define('URL', 	$_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI']);
define('QUERY', $_SERVER['QUERY_STRING']);
define('SSL',	$_SERVER['HTTPS']);

# Ruta de la aplicación.
define('DS', 	DIRECTORY_SEPARATOR);
define('ROOT', 	dirname(__FILE__) . DS);

# Necesario para la función "htmlentities"
if ( !defined('ENT_SUBSTITUTE') )
	define('ENT_SUBSTITUTE', 8);

# Necesario para la función "json_encode"
if ( !defined('JSON_PRETTY_PRINT') )
	define('JSON_PRETTY_PRINT', 0);

# Sin compresión ZLIB
ini_set('zlib.output_compression', 	'Off');

# Activando el colector de referencia circular.
gc_enable();

# Empezar sesión.
if ( !isset($kernel['nosession']) )
	session_start();

#############################################################
## ESTABLECIMIENTO DE RUTAS
#############################################################

# Rutas donde buscar el Kernel.
$search_kernel = array(
	ROOT,
	dirname(ROOT),
	dirname(dirname(ROOT)),
	dirname(dirname(dirname(ROOT)))
);

# Buscamos...
foreach ( $search_kernel as $path )
{
	# Lo hemos encontrado.
	if ( is_dir($path . DS . 'Kernel') )
	{
		define('KERNEL', $path . DS . 'Kernel' . DS);
		break;
	}
}

# Kernel: Ruta de las vistas.
define('KERNEL_VIEWS', 		KERNEL . 		'Views' . DS);
# Kernel: Ruta de las vistas relacionadas a BitRock.
define('KERNEL_VIEWS_BIT', 	KERNEL_VIEWS . 	'bitrock' . DS);

# App: Ruta de la carpeta interna.
define('APP', 					ROOT . 		'App' . DS);
# App: Ruta de las vistas.
define('APP_VIEWS', 			APP . 		'Views' . DS);
# App: Ruta de las cabeceras.
define('APP_VIEWS_HEADERS',		APP_VIEWS . 'headers' . DS);
# App: Ruta de las cabeceras.
define('BIT',				APP . 'BitRock' . DS);
# App: Ruta de las traducciones.
define('LANGUAGES', 		APP . 'Languages' . DS);

#############################################################
## INICIANDO BitRock: Administrador de procesos iniciales.
#############################################################

# Información del Kernel.
include KERNEL . 'Info.php';

# Iniciando BitRock (Ayudante)...
require KERNEL . 'Helpers' . DS . 'Bit.php';
new Bit;

#############################################################
## INICIANDO INSTANCIAS DEL SISTEMA
#############################################################

# Preparamos los códigos de error.
new Codes;
# Preparamos el sistema de lenguajes.
new Lang;
# Preparamos y verificamos el archivo de configuración.
new Setup;

# Realizamos la conexión al servidor Memcache (Si la hay)
new Mem;
# Realizamos la conexión al servidor SQL (MySQL o SQLite 3)
new BaseSQL;

# Establecemos la configuración del sitio en $site
new Site;
# Verificamos si el visitante es nuevo.
Site::Visit();

# Restauramos la información de un $_POST perdido (¿por un error?)
Client::GetPost();
# Verificamos la carga del servidor.
Bit::CheckLoad();

#############################################################
## FUNCIONES DE ACCESO DIRECTO
#############################################################

/**
 * Redirecciona a una página local o externa.
 * @param string  $url        Dirección web o página local.
 * @param boolean $javascript ¿Usar JavaScript?
 */
function Redirect($url = '', $javascript = false)
{
	Core::Redirect($url, $javascript);
}

/**
 * Reemplaza constantes en una cadena.
 * @param string $str   Cadena
 * @param string $other Otras constantes/valores a reemplazar.
 */
function Keys($str, $other = '')
{
	# Obtenemos las constantes.
	$params = get_defined_constants(true);
	$params = $params['user'];

	# Agregar más valores a la lista si los hay.
	if ( is_array($other) )
	{
		foreach ( $other as $param => $value )
			$params[$param] = $value;
	}

	# Remplazar cada una de los accesos directos encontrados.
	foreach ( $params as $param => $value )
		$str = str_ireplace('{' . $param . '}', $value, $str);

	return $str;
}

/**
 * [Query description]
 * @param string $table 	Tabla
 * @param method $callback 	Función de regreso.
 */
function Query($table)
{
	$query = new Query($table);
	return $query;
}

/**
 * Ejecutar una consulta.
 * @param  string  $query Consulta.
 * @param  boolean $cache ¿Guardar en caché?
 * @param  boolean $free  ¿Liberar memoria al finalizar?
 * @return resource       Recurso de la consulta.
 */
function q($query, $cache = false, $free = false)
{
	return SQL::query($query, $cache, $free);
}

/**
 * Insertar datos en una tabla.
 * @param string $table Tabla
 * @param array $data  	Datos
 * @return resource 	Recurso de la consulta.
 */
function Insert($table, $data)
{
	return SQL::Insert($table, $data);
}

/**
 * Actualizar los datos de una tabla.
 * @param string  $table   	Tabla
 * @param array  $updates 	Datos a actualizar.
 * @param array  $where   	Condiciones a cumplir.
 * @param integer $limit   	Limite de columnas a actualizar.
 * @return resource 		Recurso de la consulta.
 */
function Update($table, $updates, $where = '', $limit = 1)
{
	return SQL::Update($table, $updates, $where, $limit);
}

/**
 * Obtener el numero de filas de una consulta.
 * @param string $query Consulta O recurso de la consulta. Si
 * se deja vacio se usará el recurso de la última consulta hecha.
 */
function Rows($query = '')
{
	return SQL::Rows($query);
}

/**
 * Obtener los valores de una consulta.
 * @param string $query Consulta O recurso de la consulta. Si
 * se deja vacio se usará el recurso de la última consulta hecha.
 */
function Assoc($query = '')
{
	return SQL::Assoc($query);
}

/**
 * Obtener un valor especifico de una consulta.
 * @param string $query Consulta.
 * @return string Valor o array con los valores.
 */
function Get($query)
{
	return SQL::Get($query);
}

/**
 * Obtener los valores de una consulta.
 * @param string $query Consulta O recurso de la consulta. Si
 * se deja vacio se usará el recurso de la última consulta hecha.
 */
function Object($query = '')
{
	return SQL::Object($query);
}

/**
 * Obtener los valores de una consulta.
 * @param string $query Consulta O recurso de la consulta. Si
 * se deja vacio se usará el recurso de la última consulta hecha.
 */
function GetArray($query = '')
{
	return SQL::GetArray($query);
}

/**
 * Libera la memoria de la última consulta realizada.
 * @param resource $query Recurso de la última consulta.
 */
function Free($query = '')
{
	return SQL::Free($query);
}

/**
 * Devuelve la última ID insertada en la base de datos.
 */
function LastID()
{
	return SQL::LastID();
}

/**
 * Limpia una cadena contra SQL Inyection.
 * @param string  $str  Cadena
 * @param boolean $html ¿Limpiar contra XSS Inyection?
 * @param string  $from Codificación original de la cadena.
 * @param string  $to   Codificación deseada para la cadena.
 */
function Filter($str, $html = true, $from = '', $to = '')
{
	return Core::Filter($str, $html, $from, $to);
}
function _f($str, $html = true, $from = '', $to = '')
{
	return Core::Filter($str, $html, $from, $to);
}

/**
 * Limpia una cadena contra XSS Inyection.
 * @param string $str  Cadena
 * @param string $from Codificación original de la cadena.
 * @param string $to   Codificación deseada para la cadena.
 */
function Clean($str, $from = '', $to = '')
{
	return Core::Clean($str, $from, $to);
}
function _c($str, $from = '', $to = '')
{
	return Core::Clean($str, $from, $to);
}

/**
  * Encuentra si una cadena contiene las palabras indicadas.
  * @param string  $str   Cadena.
  * @param string  $words Palabra o array de palabras a encontrar.
  * @param boolean $lower ¿Convertir a minusculas?
  * @return boolean Devuelve true si alguna de las palabras fue encontrada dentro de la
  * cadena a buscar, false si no.
  */
function Contains($str, $words, $lower = false)
{
	return Core::Contains($str, $words, $lower);
}

/**
 * Obtiene la fecha actual con formato.
 * @param boolean $hour ¿Incluir hora?
 * @param integer $type
 */
function NormalDate($hour = true, $type = 1)
{
	if ( !is_numeric($type) OR $type < 1 OR $type > 3 )
		$type = 1;

	if ( $type == 1 )
		$date = date('d') . '-' . Date::GetMonth(date('m')) . '-' . date('Y');
	if ( $type == 2 )
		$date = date('d') . '/' . Date::GetMonth(date('m')) . '/' . date('Y');
	if ( $type == 3 )
		$date = date('d') . ' de ' . Date::GetMonth(date('m')) . ' de ' . date('Y');

	if ( $hour )
		$date .= ' ' . date('H:i:s');

	return $date;
}

/**
 * Calcula el tiempo restante/faltante.
 * @param mixed  $date  Tiempo Unix o cadena de tiempo.
 * @param boolean $onlyNum  Devolver solo el numero y tipo.
 */
function CalcTime($date, $onlyNum = false)
{
	return Date::CalculateTime($date, $onlyNum);
}

/**
 * Imprime de una manera más comoda un array o un objeto.
 * @param  mixed $data  Array u Objeto.
 */
function _r($data)
{
	if ( !is_array($data) AND !is_object($data) )
		return false;

	echo '<pre>';
	print_r($data);
	echo '</pre>';
}

/**
 * Traduce una cadena.
 * @param string  $data    Cadena
 * @param string  $lang    Lenguaje a traducir.
 * @param string  $section Sección de traducción.
 * @param boolean $live    ¿Preparado para traducción en tiempo real?
 */
function _l($data, $lang = '', $section = '', $live = false)
{
	return Lang::Translate($data, $lang, $section, $live);
}

/**
 * Establece una variable de plantilla.
 * @param  string $param Nombre de la variable o array con variables.
 * @param  string $value Valor (Si $param es un array, $value será un prefijo a poner)
 */
function _t($param, $value = '')
{
	return Tpl::Set($param, $value);
}

/**
 * Crea una nueva cadena inteligente a partir de una cadena.
 * @param  string $str Cadena
 * @return Str Cadena inteligente.
 */
function __($str)
{
	# Se quiere convertir varias cadenas a la vez.
	if ( is_array($str) )
	{
		# Convertimos cada una de ellas.
		foreach ( $str as $key => $value )
			$str[$key] = new Str($value);

		return $str;
	}

	# Solo convertir una simple cadena.
	if ( is_string($str) )
		return new Str($str);

	return $str;
}

/**
 * Limpia una cadena contra SQL Inyection y la convierte en cadena inteligente.
 * @param  Str  $str   Cadena inteligente.
 * @param  boolean $clean  ¿Solo aplicar filtro XSS?
 */
function __f($str, $clean = false)
{
	# Se quiere limpiar varias cadenas a la vez.
	if ( is_array($str) )
	{
		# Limpiamos cada una de ellas.
		foreach ( $str as $key => $value )
			$str[$key] = __f($value, $clean);

		return $str;
	}

	# Solo convertir una simple cadena.
	if ( is_string($str) )
	{
		$str = new Str($str);

		# Solo queremos filtrar contra XSS.
		if ( !$clean )
			$str->filter();

		# Filtro SQL
		else
			$str->clean();

		return $str;
	}

	return $str;
}

/**
 * Verifica si una cadena inteligente esta vacia.
 * @param  Str $str 	Cadena inteligente
 * @return boolean      ¿Esta vacio?
 */
function _empty($str)
{
	# Por si acaso...
	if ( is_string($str) )
		return empty($str);

	# Para comenzar ni siquiera es una instancia de Str
	# Utilizado mayormente para verificar $G y $P
	if ( !($str instanceof Str) )
		return true;

	# Al parecer si esta vacia.
	if ( $str->empty )
		return true;

	return false;
}

/**
 * Guardar un log.
 * @param string $message Mensaje a guardar.
 * @param string $type    Tipo del log
 */
function Reg($message, $type = LOG_INFO)
{
	Bit::Log($message, $type);
}

/**
 * Elimina todas las cookie
 */
function cookie_destroy()
{
	foreach ( $_COOKIE as $param )
	{
		setcookie($param, '', -1000);
		unset($_COOKIE[$param]);
	}
}

/**
 * Define una sesión con el prefijo de sesiones.
 * @param string $key 		Llave
 * @param string $value 	Valor. Si se deja vacio se retornará su valor actual (si la hay).
 */
function _SESSION($key, $value = '')
{
	return Core::SESSION($key, $value);
}

/**
 * Elimina una sesión con el prefijo de sesiones.
 * @param string $key Llave
 */
function _DELSESSION($param)
{
	return Core::DELSESSION($param);
}

/**
 * Define una cookie con el prefijo de cookies.
 * @param string  $key    	Llave
 * @param string  $value    Valor. Si se deja vacio se retornará su valor actual (si la hay).
 * @param string  $duration Duración en segundos.
 * @param string  $path     Ruta donde será válida.
 * @param string  $domain   Dominio donde será válida.
 * @param boolean $secure   ¿Solo válida en conexiones HTTPS?
 * @param boolean $imgod    Si esta en true no podrá ser usada/modificada por el navegador (JavaScript)
 */
function _COOKIE($key, $value = '', $duration = '', $path = '', $domain = '', $secure = false, $imgod = false)
{
	return Core::COOKIE($key, $value, $duration, $path, $domain, $secure, $imgod);
}

/**
 * Elimina una cookie.
 * @param string $key 	 Llave
 */
function _DELCOOKIE($key, $path = '', $domain = '')
{
	return Core::DELCOOKIE($key, $path, $domain);
}

/**
 * Guarda un objeto en Memcache o en $_SESSION
 * @param string $key 	Llave
 * @param string $value Valor. Si se deja vacio se retornará su valor actual (si la hay).
 */
function _CACHE($key, $value = '')
{
	return Core::CACHE($key, $value);
}

/**
 * Elimina un objeto en Memcache o en $_SESSION
 * @param string $key Llave
 */
function _DELCACHE($param)
{
	return Core::DELCACHE($param);
}

#############################################################
## RECUPERACIÓN INTELIGENTE
#############################################################

Bit::SmartBackup();

#############################################################
## DEFINICIÓN DE GLOBALES
#############################################################

# Nombre de la aplicación.
define('SITE_NAME', $site['site_name']);

# Ruta local del Logo.
if ( !empty($site['site_logo']) )
	define('LOGO', RESOURCES . '/images/'. $site['site_logo']);

# Motor del navegador web del visitante.
define('ENGINE', 	Client::Get('engine'));
# Navegador web del visitante.
define('BROWSER', 	Client::Get('browser'));
# Sistema operativo del visitante.
define('OS', 		Client::Get('os'));
# Host/DNS del visitante.
define('HOST', 		Client::Get('host'));
# País del visitante.
define('COUNTRY', 	Client::Get('country'));
# Zona horaria del visitante.
define('TIMEZONE', 	Client::Get('timezone'));
# Dominio actual.
define('DOMAIN', 	Core::GetHost(PATH));
# Ruta del RSS
define('RSS', 		PATH . '/rss');

# Configurar la zona horaria del visitante como zona horaria a utilizar en PHP.
if( $config['server']['timezone'] == true AND TIMEZONE !== '' )
	date_default_timezone_set(TIMEZONE);

$constants = get_defined_constants(true);
$constants = $constants['user'];

# Establecemos variables de plantilla para las constantes creadas. %PATH%, %RESOURCES%, etc..
Tpl::Set($constants);

# Establecemos variables de configuración de sitio.
Tpl::Set($site);

if ( $page['take_cookies'] == true )
{
	if ( !empty($_POST['cookies']) )
	{
		$tmpCookies = explode(';', $_POST['cookies']);
		foreach ( $tmpCookies as $cookie )
		{
			$tmpExplode 	= explode('=', $cookie);
			$tmpExplode[0] 	= trim($tmpExplode[0]);
			$tmpExplode[1] 	= trim($tmpExplode[1]);

			$_COOKIE[$tmpExplode[0]] = $tmpExplode[1];
		}
	}
}

#############################################################
## SEGURIDAD
#############################################################

# Filtramos los datos de $_GET, $_POST y $_REQUEST (Anti SQL/XSS Inyection)
# y las ponemos en variables más cortas.

$G 	= _f($_GET);
$GC = _f($_GET, true);

$P 	= _f($_POST);
$PC = _f($_POST, true);

$R 	= _f($_REQUEST);
$RC = _f($_REQUEST, true);

$PA = $_POST;
$GA = $_GET;
$RA = $_REQUEST;

# Sospechas de inyección.
# @TODO: Mejorar.
foreach ( $_REQUEST as $key => $value )
{
	$value = strtoupper(urldecode($value));

	# Mmmm, al parecer alguien o algo esta intentando poner una consulta en las variables de entrada.
	preg_match("/SELECT ([^<]+) FROM/is", $value, $verify);
	preg_match("/DELETE ([^<]+) FROM/is", $value, $verify2);
	preg_match("/UPDATE FROM/is", $value, $verify3);

	# Si es así, enviarle un correo electrónico al webmaster.
	if ( count($verify) !== 0 OR count($verify2) !== 0 OR count($verify3) !== 0 )
		Core::SendWait();
}

# Si el modo seguro esta activado filtrar toda
# información proveniente del usuario y las sesiones.
# Además de eliminar información delicada.
if ( $config['security']['enabled'] OR $Kernel['secure'] == true AND $Kernel['secure'] !== false )
{
	$_POST 		= $P;
	$_GET 		= $G;
	$_SESSION 	= _f($_SESSION);

	unset($config['sql']['user'], $config['sql']['password'], $config['security']['hash']);
}

#############################################################
## VERIFICACIÓN DE CONEXIÓN ACTIVA DEL USUARIO
#############################################################

$my = null;
$ms = null;

Users::Session();

#############################################################
## FUNCIONES PERSONALIZADAS
#############################################################

require APP . 'Functions.php';

#############################################################
## HEMOS TERMINADO
#############################################################

include APP . 'Setup.php';
Reg('BeatRock se ha cargado correctamente.');
