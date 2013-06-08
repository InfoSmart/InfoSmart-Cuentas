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
_t('scope', $RA['scope']);

# Parametros a pasar por la dirección.
$params = array(
	'public'	=> $R['public'], 	// Llave pública.
	'return'	=> $RA['return'], 	// Dirección de regreso a la App.
	'scope' 	=> $R['scope'], 	// Permisos
);
$params = '?' . http_build_query($params);
//_t('params', $params);

# No ha iniciado sesión.
if ( !LOG_IN )
	Core::Redirect('/connect/login' . $params);

# Obtenemos la información de la aplicación a partir de su clave pública.
$app = Apps::GetPublic($G['public']);

# ¡La aplicación no existe!
if ( !$app )
{
	$page['id'] = 'app.no.exists';
	goto ProcessPage;
}

# Permisos
$scope = explode(',', $G['scope']);
$scope = API::GetScope($scope);

# ¿Que tiene de especial esta aplicación?
$features 	= json_decode($app['features'], true);
$authKey 	= Auth::NewKey($app['id'], ME_ID, KEY_AUTHORIZE);

# Poner la informacion de la aplicación en variables de plantilla.
_t($app, 'app_');
_t('return_reg', urlencode(PATH_NOW));

$page['id'] = 'confirm.app';

ProcessPage:
{
	$page['folder']		= 'connect';
	$page['subheader'] 	= 'login.header';
	$page['login']		= true;
}
