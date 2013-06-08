<?
$page['require_login'] 	= true; 	// Con esto hacemos que sea necesario iniciar sesión.
$kernel['gzip'] 		= false; 	// A veces GZIP hace que las fotos se suban mal.

require '../../Init.php';

## --------------------------------------------------
## Verificación de la foto de acceso.
## --------------------------------------------------
## Página para verificar si la foto de acceso subida
## coincide con la de algún usuario e iniciar sesión.
## --------------------------------------------------

$result = array();
# Esta función hace el trabajo por nosotros :)
$upload = AcUsers::VerifyPhotoAccess($_FILES['photo']);

# ¿Se te cayó el internet en media subida?
if ( $upload == INVALID )
	$error = '¡Uy! Al parecer no pudimos recibir tu foto de acceso, reinicia la página y vuelve a intentarlo.';

# ¿Quisiste subir un .bat?
if ( $upload == TYPE_INVALID )
	$error = 'Solo puedes subir archivos de tipo PNG, JPEG o GIF';

# No, no aceptamos el GTA como una foto de perfil...
if ( $upload == TOO_HEAVY )
	$error = 'La foto de acceso es muy grande ¿Estás segur@ que es esta?';

# Nadie ha subido esta foto como foto de acceso.
if ( $upload == NO_EXIST )
	$error = 'No pudimos identificar ningún usuario con esta foto de acceso.';

# Sin errores.
if ( empty($error) )
{
	# Iniciar sesión, $upload en caso de éxito devolvería la ID del usuario.
	Users::Login($upload, false);
	$result['status'] 	= 'OK';
}
else
{
	# ¡Uy! Un error.
	$result['status'] 	= 'ERROR';
	$result['message']	= '<li>' . $error . '</li>';
}

# Devolver el código JSON que será procesado por JavaScript.
echo json_encode($result);
