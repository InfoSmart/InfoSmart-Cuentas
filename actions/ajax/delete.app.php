<?
$page['require_login'] 	= true; // Con esto hacemos que sea necesario iniciar sesión.
$page['is_ajax'] 		= true; // Bloquear todo acceso que no sea por AJAX.

require '../../Init.php';

# Obtenemos información de la aplicación.
$app 	= Apps::Get($P['id']);
$auth 	= Apps::Authorized($P['id']);

# Al parecer esta aplicación no existe.
if ( !$app OR !$auth )
	exit('NO_EXIST');

Apps::DeleteUsedApp($P['id']);
echo 'OK';