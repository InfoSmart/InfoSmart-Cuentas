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

# Obtenemos las llamadas.
$callbacks = json_decode($app['callbacks'], true);

$valid = false;

if ( DEVELOPMENT )
	$valid = !empty($callbacks['delete']);
else
	$valid = Core::Valid($callbacks['delete'], URL);

# Solo queremos saber si esta aplicación permite eliminar la información del usuario.
if ( empty($P['confirm']) )
{
	# Esta dirección es válida, todo bien.
	if ( $valid )
		exit('VALID');
	else
		exit('OK');
}

# ¡Confirmación! Eliminar la información del usuario en la aplicación.
else
{
	# Esta dirección no es válida ¿la cambio en el último segundo?
	if ( !$valid )
		exit('URL_NOT_VALID');

	# Solicitamos la eliminación...
	$result = Apps::RequestDeleteInfo($P['id']);
}

echo ( !$result ) ? 'FAIL' : 'OK';