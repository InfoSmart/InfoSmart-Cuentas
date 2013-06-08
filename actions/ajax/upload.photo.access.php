<?
$page['require_login'] 	= true; 	// Con esto hacemos que sea necesario iniciar sesión.
$kernel['gzip'] 		= false; 	// A veces GZIP hace que las fotos se suban mal.

require '../../Init.php';

$result = array();
# Esta función hace el trabajo por nosotros :)
$upload = AcUsers::SaveFilePhotoAccess($_FILES['photo']);

# ¿Se te cayó el internet en media subida?
if ( $upload == INVALID )
	$error = '¡Uy! Al parecer no pudimos recibir tu foto de perfil, reinicia la página y vuelve a intentarlo.';

# ¿Quisiste subir un .bat?
if ( $upload == TYPE_INVALID )
	$error = 'Solo puedes subir archivos de tipo PNG, JPEG o GIF';

# No, no aceptamos el GTA como una foto de perfil...
if ( $upload == TOO_HEAVY )
	$error = 'La foto de perfil es muy grande, por favor sube una foto con un peso menor a 5 MB.';

# Sin errores
if ( empty($error) )
	$result['status'] = 'OK';
else
{
	# ¡Uy! Un error.
	$result['status'] 	= 'ERROR';
	$result['message']	= $error;
}

# Devolver el código JSON que será procesado por JavaScript.
echo json_encode($result);
