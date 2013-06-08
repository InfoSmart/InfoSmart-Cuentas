<?
if ( !DEVELOPMENT )
	require '../../Init.php';

# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

## --------------------------------------------------
## Timer - Actualización de información.
## --------------------------------------------------
## Cronometro para la actualización de la información
## de todos los usuarios en cada una de las aplicaciones.
##
## Este cronometro debe ser ejecutado desde consola.
## --------------------------------------------------

# Obtenemos todos los usuarios.
$users = Users::GetAll();

# Examinamos usuario por usuario.
while ( $user = Assoc($users) )
{
	# Obtenemos una lista de las aplicaciones que ha usado.
	$apps = Apps::GetUsedApps($user['id']);

	# No ha usado ninguna.
	if ( !$apps )
		continue;

	# Examinamos aplicación por aplicación.
	while ( $app = Assoc($apps) )
	{
		# Obtenemos la información real de la aplicación.
		$app 		= Apps::Get($app['appId']);
		# Obtenemos las llamadas.
		$callbacks 	= json_decode($app['callbacks'], true);

		# Al parecer la llamada de actualización no es válida.
		if ( !Core::Valid($callbacks['updates'], URL) AND !DEVELOPMENT OR empty($callbacks['updates']) )
			continue;

		# Enviamos la petición de actualización de información.
		$res = Apps::RequestUpdateInfo($app['id'], $user['id']);
	}
}