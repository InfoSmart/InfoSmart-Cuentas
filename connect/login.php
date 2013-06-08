<?
require '../Init.php';

## --------------------------------------------------
##                   Confirmación
## --------------------------------------------------
## Página para darle la oportunidad al usuario
## de confirmar darle permisos a la aplicación en
## cuestión.
## --------------------------------------------------

# Dirección de regreso.
$return = urldecode($RA['return']);
$e 		= ( Contains($return, '?') ) ? '&' : '?';

_t('return', urlencode($RA['return']));
_t('return_reg', urlencode(PATH_NOW));

# Parametros a pasar por la dirección.
$params = array(
	'public'	=> $R['public'], 	// Llave pública.
	'return'	=> $RA['return'], 	// Dirección de regreso a la App.
	'scope' 	=> $R['scope'], 	// Permisos
);
$params = '?' . http_build_query($params);
_t('params', $params);

# Ya hemos iniciado sesión.
if ( LOG_IN )
	Core::Redirect('/connect/authorize' . $params);

# Sistema en caso de error al iniciar sesión.
$error['field'] 	= _SESSION('login_error_field');
$error['message']	= _SESSION('login_error_message');
$loginID			= _SESSION('login_id');

if ( empty($loginID) )
	$loginID = $G['id'];

if ( !empty($error['message']) )
{
	 _DELSESSION('login_error_field');
	 _DELSESSION('login_error_message');
	 _DELSESSION('login_id');
}

# Verificación de correo electrónico.
$emailValid = _SESSION('email_valid_ok');

if ( $emailValid )
	_DELSESSION('email_valid_ok');

# Error de bucle de redirección.
if ( $G['redirectloop']  == 'true' )
{
	Tpl::JSAction('Kernel.ShowBox("error");');
	$message = '¡Uy! Hemos detectado un bucle de redirección al intentar iniciar sesión con este servicio. Te recomendamos eliminar tus cookies e intentarlo nuevamente.';

	_DELSESSION('login_trys');
}

# Error de bucle de redirección.
if ( $G['failsocial']  == 'true' )
{
	Tpl::JSAction('Kernel.ShowBox("error");');
	$message = 'No hemos podido obtener la información de tu servicio social. Vuelve a intentarlo más tarde.';
}

# Error de identificación.
if ( $error['field'] == 'id' )
	$fieldID 		= 'class="error"';

# Error de contraseña.
if ( $error['field'] == 'password' )
	$fieldPassword 	= 'class="error"';

# Se trata de un inicio hacia una aplicación.
if ( !empty($G['public']) )
{
	# Obtenemos la información de la aplicación a partir de su clave pública.
	$app = Apps::GetPublic($G['public']);

	# ¡La aplicación no existe!
	if ( !$app )
	{
		$page['id'] = 'app.no.exists';
		goto ProcessPage;
	}

	# Permisos
	$AccScope = explode(',', $G['scope']);
	$AccScope = API::GetScope($AccScope);

	# Obtenemos las claves para las API (Si las hay)
	$connect 	= @json_decode($app['connect'], true);
	$authKey 	= Auth::NewKey($app['id'], ME_ID, KEY_AUTHORIZE);

	# Parametros a pasar por la dirección.
	$params = array(
		'public'	=> $R['public'], 	// Llave pública.
		'return'	=> $RA['return'], 	// Dirección de regreso a la App.
		'scope' 	=> $R['scope'], 	// Permisos
		'authorize'	=> $authKey 		// Llave de autorización.
	);
	$params = '?' . http_build_query($params);
	_t('params', $params);

	# Ponemos la informacion de la aplicación en variables de plantilla.
	_t($app, 'app_');

	# Esta es la página donde regresaremos al iniciar sesión.
	$return_login 	= PATH . '/connect/authorize' . $params;
	_t('return_login', urlencode($return_login));
	Tpl::AddVar('Return', $return_login);
	# Esta es la página donde regresaremos si ocurrio un error.
	$return_error 	= PATH . '/connect/login' . $params;
	_t('return_error', urlencode($return_error));
	# ¿Que tiene de especial esta aplicación?
	$features 		= json_decode($app['features'], true);
	# La plantilla especial para esta ocación.
	$page['id']		= 'login.app';
}
else
{
	Tpl::AddVar('Return', PATH);
	$page['id']	= 'login';
}

# Permisos de Facebook
$fbScope = Core::SplitArray(Fb::$scope, '', ',');
foreach ( $fbScope as $scope )
	$fbScopes .= $scope;
$fbScopes = substr($fbScopes, 0, strlen($fbScopes) - 1);
Tpl::AddVar('FacebookScope', $fbScopes);

ProcessPage:
{
	$page['folder']		= 'connect';
	$page['subheader'] 	= 'login.header';
	$page['login']		= true;
}
