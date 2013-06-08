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
 * Código de preparación
 * Utilice este archivo para definir el código que será
 * ejecutado al inicio del Framework.
 *
*/

# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

#############################################################
## MANTENIMIENTO
## Verificamos si el sitio esta en mantenimiento, si es así
## devolvemos la vista de mantenimiento y un error 503.
#############################################################

# Puedes evitar que una página muestre la vista de mantenimiento
# poniendo "$page['maintenance'] = false;" antes de "require Init.php"

if ( $site['site_status'] !== 'open' AND $page['maintenance'] !== false )
{
	header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
	header('Cache-Control: no-cache');

	header('HTTP/1.1 503 Service Temporarily Unavailable');
	header('Status: 503 Service Temporarily Unavailable');

	$maintenance = new View(KERNEL_VIEWS_BIT . 'Maintenance');
	$maintenance->AddPHP()->Build();
	exit($maintenance);
}

#############################################################
## DEFINICIONES GLOBALES
## Puede definir variables o ejecutar funciones de inicialización
## que podrían ser necesarios en toda la aplicación.
#############################################################

# Esto es una aplicación oficial de InfoSmart.
define('INFOSMART', true);
define('ACCOUNTS', true);

# Versión de la API.
define('API_VERSION', $site['api_version']);
define('API_AGENT', 'InfoSmartCuentas_APIv' . API_VERSION);

# Ubicación hacia la carpeta de guardado de fotos.
define('MEDIA', $site['media_path']);

# Nada de InfoSmart Cuentas desde un iframe
Tpl::Protect();

# Privacidad
$PRIVACY = array(
	'email' 		=> 'Correo electrónico',
	'gender'		=> 'Sexo',
	'birthday'		=> 'Fecha de nacimiento',
	'lastaccess'	=> 'Último acceso',
	'browser'		=> 'Navegador web',
	'os'			=> 'Sistema operativo',
	'country'		=> 'Ubicación'
);

# Métodos válidos para restaurar la identificación.
$FORGOT_ID = array(
	'remember_username', 	// Recuerdo parte de mi nombre de usuario.
	'remember_info', 		// Rellenare mi información.
	'remember_alt_email' 	// Recuerdo uno de mis correos alternativos.
);

# Claves para las API.
$connections = Accounts::GetConnections();

while ( $row = Assoc($connections) )
{
	if ( empty($row['secret']) )
		continue;

	Social::$data[$row['id']]['public'] = $row['public'];
	Social::$data[$row['id']]['secret'] = $row['secret'];
}

# Permisos necesarios para InfoSmart Cuentas.
# Correo electrónico, Biografía, Fecha de nacimiento, Ciudad de origen, Ubicación, Estado actual (¿Proximas redes sociales?), Sitio web.
Fb::$scope 		= array('email', 'user_about_me', 'user_birthday', 'user_hometown', 'user_location', 'user_status', 'user_website');

#####################################################
## INFOSMART CUENTAS
#####################################################

# Hemos iniciado sesión.
if ( LOG_IN )
{
	# Correos secundarios.
	$me_emails 	= json_decode($me['emails'], true);
	# Privacidad
	$me_privacy = json_decode($me['privacy'], true);

	# Hay un limite de sesión.
	if ( $me['sessionLimit'] > 0 )
	{
		# La sesión ha expirado, cerrar sesión y actualizar datos.
		if ( $me['sessionLimit'] <= time() )
		{
			AcUsers::Logout();
			Core::Redirect(PATH_NOW);
		}
	}

	# Uso de HTTPS (SSL)
	# @TODO: Cambiar $me['secure'] por $site['secure'] (Obligaremos a todos a usar HTTPS)
	if ( $me['secure'] == '1' AND SSL !== 'on' AND !Contains(PATH, 'localhost') )
	{
		Client::SavePost();
		Core::Redirect('https://' . URL);
	}

	# Algunos servicios sociales no proporcionan toda la información requerida para entrar a InfoSmart Cuentas.
	# Si ese es el caso le mostramos una página al usuario donde le pedimos estos datos manualmente.
	if ( $page['id'] !== 'required' AND $page['logout'] !== true )
	{
		# Campos obligatorios para entrar a InfoSmart Cuentas.
		$required_fields = array('email', 'firstname', 'lastname', 'name', 'gender', 'country');

		foreach ( $required_fields as $field )
		{
			# Este campo esta vacio. Redireccionamos a la página de solicitud de información.
			if ( empty($me[$field]) )
				Core::Redirect('/required');
		}
	}
}

if ( !DEVELOPMENT )
{
	# Es necesario que provenga de AJAX.
	if ( $page['is_ajax'] == true AND $_SERVER['HTTP_X_REQUESTED_WITH'] !== 'XMLHttpRequest' )
		exit;

	# Es necesario que provenga de infosmart.mx
	if ( $page['only_infosmart'] == true AND !Contains($_SERVER['HTTP_ORIGIN'], 'infosmart.mx') )
		exit;
}

#####################################################
## INFOSMART CUENTAS - API
#####################################################

# JSON!
if ( Contains(URL, array('connect.infosmart.mx', 'localhost/connect') ) )
	header('Content-Type: application/json');

# Solicitud desde nuestra API.
if ( $page['api'] == true )
{
	# Requerir clave secreta de app y código de autorización.
	$data = API::GetAPIAccess($R['private'], $R['authorize']);
	API::Error($data);
}

#############################################################
## CONFIGURACIÓN EXTRA
#############################################################

/*
	BBCode -> Core::BBCode(string, smilies)
	Desde aquí puedes editar los códigos BB.
*/

$kernel['bbcode_search'] = array(
	'/\[b\](.*?)\[\/b\]/is',
	'/\[i\](.*?)\[\/i\]/is',
	'/\[u\](.*?)\[\/u\]/is',
	'/\[s\](.*?)\[\/s\]/is',
	'/\[url\=(.*?)\](.*?)\[\/url\]/is',
	'/\[color\=(.*?)\](.*?)\[\/color\]/is',
	'/\[size=small\](.*?)\[\/size\]/is',
	'/\[size=large\](.*?)\[\/size\]/is',
	'/\[size\=(.*?)\](.*?)\[\/size\]/is',
	'/\[code\](.*?)\[\/code\]/is',

	'/\[youtube\=(.*?)x(.*?)\](.*?)\[\/youtube\]/is',
	'/\[vimeo\=(.*?)x(.*?)\](.*?)\[\/vimeo\]/is'
);

$kernel['bbcode_replace'] = array(
	'<strong>$1</strong>',
	'<i>$1</i>',
	'<u>$1</u>',
	'<s>$1</s>',
	'<a href="$1">$2</a>',
	'<label style="color: $1;">$2</label>',
	'<label style="font-size: 9px;">$1</label>',
	'<label style="font-size: 14px;">$1</label>',
	'<label style="font-size: $1px;">$2</label>',
	'<pre>$1</pre>',

	'<iframe title="YouTube" width="$1" height="$2" src="https://www.youtube.com/embed/$3" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>',
	'<iframe title="Vimeo" width="$1" height="$2" src="http://player.vimeo.com/video/$3?badge=0" frameborder="0" webkitAllowFullScreen mozallowfullscreen allowFullScreen></iframe>'
);

/*
	Smilies -> Core::Smilies(string, bbcode)
	Desde aquí puedes editar las imagenes usadas para los emoticones.
	Las imagenes se encuentran en: /Kernel/BitRock/Emoticons/ (Obligatorio ser PNG)
*/

$kernel['emoticons'] = array(
	':D' 	=> 'awesomes',
	':)' 	=> 'happy',
	'D:' 	=> 'ohnoes',
	':0' 	=> 'ohnoes',
	':O' 	=> 'ohnoes',
	'OMG' 	=> 'ohnoes',
	':3' 	=> 'meow',
	'.___.' => 'huh',
	':S' 	=> 'confused',
	':P' 	=> 'lick',
	'^^' 	=> 'laugh',
	':(' 	=> 'sad',
	';)' 	=> 'wink',
	':B' 	=> 'toofis',
	'jelly' => 'jelly',
	'jalea' => 'jelly'
);
