<?
# Con esto hacemos que sea necesario iniciar sesión.
$page['require_login'] = true;
require '../../Init.php';

## --------------------------------------------------
## Guardado de la privacidad en tiempo real.
## --------------------------------------------------
## Esta página es solicitada por AJAX cuando el usuario
## cambie la privacidad de su información.
## --------------------------------------------------

# El prefijo "privacy_" es solo para el formulario. Aquí no lo necesitamos.
$P['type'] = str_ireplace('privacy_', '', $P['type']);

# $PRIVACY contiene la información que puede controlarse por privacidad.
# si este tipo no esta en la lista es porque es inválido.
if ( !array_key_exists($P['type'], $PRIVACY) )
	$error = '¡Vaya! Al parecer has roto algo, recarga la página y vuelve a intentarlo.';

# Por ahora solo esta disponible "Publico" o "Privado"
# @TODO: Más opciones: "Solo amigos", "Solo Gabe Newell", etc...
if ( $P['value'] != 'public' AND $P['value'] != 'private' )
	$error = '¡Vaya! Al parecer has roto algo, recarga la página y vuelve a intentarlo. 2';

# Sin errores.
if( empty($error) )
{
	$type 				= $P['type'];
	$me_privacy[$type] 	= $P['value'];
	$me_privacy 		= json_encode($me_privacy);

	Users::UpdateColumn('privacy', $me_privacy);
	$result['status'] = 'OK';
}
else
{
	# ¡Uy! Un error.
	$result['status'] 	= 'ERROR';
	$result['message'] 	= $error;
}

# Devolver el código JSON que será procesado por JavaScript.
echo json_encode($result);
