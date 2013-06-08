<?
require 'Init.php';

## --------------------------------------------------
## Foto de perfil
## --------------------------------------------------
## Página para mostrar la foto de perfil de algún
## usuario (es más seguro que mostrar directamente la url de la foto por ahora)
## Ejemplos:
## /photo/Kolesias123 -> photo.php?user=Kolesias123
## /photo/Kolesias123/big -> photo.php?user=Kolesias123&size=big
## --------------------------------------------------

# El usuario solicitado no existe o queremos la foto "default"
if ( !Users::UserExist($G['user']) OR $G['user'] == 'default' )
{
	$data = AcUsers::GetPhoto('default');
	goto ShowPhoto;
}

# ¿De que tamaño quieres la imagen?
$size = $G['size'];

# Por predeterminado de tamaño medio.
if( empty($size) )
	$size = 'medium';

# Obtenemos los "bits" de la foto del usuario.
$data = AcUsers::GetPhoto($G['user'], $size);

ShowPhoto:
{
	# Permitir carga desde cualquier dominio.
	Tpl::AllowCross('*');
	# Mostrar como imagen PNG
	Tpl::Image();

	# Lanzar "bits"
	echo $data;
}
