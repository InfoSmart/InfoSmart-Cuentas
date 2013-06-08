<?
require '../Init.php';

## --------------------------------------------------
## Verificación de correo alternativo.
## --------------------------------------------------

if ( LOG_IN )
	$G['user'] = ME_ID;

# Llave de verificación vacia ¿entonces para que estas aquí?
if ( empty($G['token']) OR !is_numeric($G['user']) )
	Redirect();

# Obtenemos al usuario.
$user = Users::Get($G['user']);

# ¡El usuario no existe!
if ( !$user )
	Redirect();

# Obtenemos los correos alternativos.
$emails = @json_decode($user['emails'], true);

# Esto no es un array, algo salio mal.
if ( !is_array($emails) )
	Redirect();

foreach ( $emails as $key => $row )
{
	# Esta llave es identica a la que recibimos.
	if ( $row['token'] == $G['token'] )
	{
		$emails[$key]['verified'] 	= '1'; 	# Verificado
		$emails[$key]['token'] 		= ''; 	# Eliminamos la llave de verificación.
		break;
	}
}

# Actualizamos...
$emails = json_encode($emails);
Users::UpdateColumn('emails', $emails);

Redirect();