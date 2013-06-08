<?
require '../../Init.php';

## --------------------------------------------------
## Desarrolladores - Crear una aplicación
## --------------------------------------------------
## Crea una nueva aplicación.
## --------------------------------------------------

# Obtenemos la información de la aplicación a partir de su clave pública.
$app = Apps::GetPublic($G['public']);

# ¡La aplicación no existe!
if ( !$app )
	Core::Redirect('/dev/apps');

# El usuario que intenta eliminar la aplicación no es el creador de la misma :yaoming:
if ( $app['ownerId'] !== ME_ID )
	Core::Redirect('/dev/apps');

Apps::DeleteAppPublic($G['public']);
Core::Redirect('/dev/apps');
