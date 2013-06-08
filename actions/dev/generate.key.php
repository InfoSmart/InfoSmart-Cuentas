<?
require '../../Init.php';

## --------------------------------------------------
## Desarrolladores - Genera una nueva llave privada.
## --------------------------------------------------
##
## --------------------------------------------------

# Obtenemos la información de la aplicación a partir de su clave pública.
$app = Apps::GetPublic($P['public']);

# ¡La aplicación no existe!
if ( !$app )
	exit('APP_NO_EXIST');

# El usuario que intenta eliminar la aplicación no es el creador de la misma :yaoming:
if ( $app['ownerId'] !== ME_ID )
	exit('APP_NOT_OWNER');

echo Apps::GenerateKey($app['id']);