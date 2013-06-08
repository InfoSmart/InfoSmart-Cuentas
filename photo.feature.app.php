<?
require 'Init.php';

## --------------------------------------------------
## Foto de característica de aplicación
## --------------------------------------------------
## Página para mostrar la foto de perfil de algún
## usuario (es más seguro que mostrar directamente la url de la foto por ahora)
## Ejemplos:
## /photo.feature.app/00001 -> photo.feature.app.php?md5=00001
## --------------------------------------------------

# La foto solicitada no existe o queremos la foto "default"
if ( $G['md5'] == 'default' OR _empty($G['md5']) )
{
	$data = Apps::GetPhoto('default');
	goto ShowPhoto;
}

# Obtenemos los "bits" de la foto del usuario.
$data = Apps::GetPhoto($G['md5']);

if ( $data === NO_EXIST )
	$data = Apps::GetPhoto('default');

ShowPhoto:
{
	# Permitir carga desde cualquier dominio.
	Tpl::AllowCross('*');
	# Mostrar como imagen PNG
	Tpl::Image();

	# Lanzar "bits"
	echo $data;
}
