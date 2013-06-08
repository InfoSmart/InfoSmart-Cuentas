<?
require '../Init.php';

## --------------------------------------------------
##               Autorización
## --------------------------------------------------
## Verifica la información del usuario y lo
## redirecciona a la página de inicio de sesión o
## confirmación.
## Si ya ha iniciado sesión y ha confirmado solo genera
## y retorna la llave de autorización.
## --------------------------------------------------

# Parametros a pasar por la dirección.
$params = array(
	'public'	=> $R['public'],
	'return'	=> $RA['return'],
	'scope' 	=> $R['scope']
);
$params = '?' . http_build_query($params);

# Obtenemos la información de la aplicación a partir de su clave pública.
$app = Apps::GetPublic($R['public']);

# ¡La aplicación no existe!
if ( !$app )
	Core::Redirect();

# No se ha iniciado sesión, redireccionar a la página de inicio de sesión.
if ( !LOG_IN )
	Core::Redirect(PATH . '/connect/login' . $params);

# Se ha autorizado, agregar a la lista blanca del usuario.
if ( !empty($R['authorize']) )
{
	# Obtenemos la información de la llave de confirmación.
	$auth 			= Auth::GetKey($R['authorize'], KEY_AUTHORIZE);
	# Obtenemos los permisos que se solicitan actualmente.
	$actualScope 	= explode(',', $R['scope']);

	# Llave válida, autorizar.
	if ( $auth !== false )
	{
		Apps::Authorize($app['id'], $actualScope);
		Auth::DeleteKey($auth['id']);
	}
}

# ¿La aplicación ya esta autorizada/confirmada para usar la info del usuario?
$ready 	= Apps::Authorized($app['id']);

if ( $ready )
{
	# Obtenemos los permisos que ocupa esta aplicación.
	$scope 			= Apps::GetScopeUsedApp($app['id']);
	# Obtenemos los permisos que se solicitan actualmente.
	$actualScope 	= explode(',', $R['scope']);

	foreach ( $actualScope as $val )
	{
		# Al parecer este permiso no existe en los ya confirmados (Esta solicitando un nuevo permiso)
		if ( !in_array($val, $scope) )
			$ready = false;
	}
}

# No estamos listos, redireccionar a la página de confirmación.
if ( !$ready )
	Core::Redirect(PATH . '/connect/confirm' . $params);

# TODO BIEN
# Generar la llave de autorización y regresarla.
$key 	= Auth::NewKey($app['id']);
$return = urldecode($RA['return']);
$e 		= ( Contains($return, '?') ) ? '&' : '?';

Core::Redirect($return . $e . 'authorize=' . $key);
