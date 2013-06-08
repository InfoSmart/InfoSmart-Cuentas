<?
# Con esto hacemos que sea necesario iniciar sesión.
$page['require_login'] = true;
require '../../Init.php';

## --------------------------------------------------
## Eliminar correo electrónico alternativo.
## --------------------------------------------------
## Solo eso.
## --------------------------------------------------

$continue 	= false;
$result 	= array();

# Es necesario validar tu correo electrónico.
if ( $me['email_verified'] == '0' )
	$error = 'No has verificado tu correo electrónico primario.';

# Esto no es una ID.
if ( !is_numeric($P['email']) )
	$error = '¡Vaya! Al parecer has roto algo, recarga la página y vuelve a intentarlo.';

# Verificamos que el correo aún este en la lista de correos alternativos.
if ( !is_array(AcUsers::GetAlternativeEmail($P['email'])) )
{
	$continue 	= true;
	$error 		= 'Esta dirección de correo ya no se encuentra aliada a tu cuenta.';
}

# Sin errores.
if ( empty($error) )
{
	# Eliminar el correo de la lista y actualizarla.
	unset($me_emails[$P['email']]);
	$me_emails = json_encode($me_emails);

	# Guardar los cambios.
	Users::UpdateColumn('emails', $me_emails);
	$result['status'] = 'OK';
}
else
{
	# ¡Uy! Un error.
	$result['status'] 	= 'ERROR';
	$result['message'] 	= $error;
	$result['continue'] = ( $continue ) ? 'true' : 'false';
}

# Devolver el código JSON que será procesado por JavaScript.
echo json_encode($result);
