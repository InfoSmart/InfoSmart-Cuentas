<?
$page['require_login'] = true; 	// Con esto hacemos que sea necesario iniciar sesión.
require '../Init.php';

$error = array();

# Esta cuenta ya tiene una contraseña, pedirla.
# Tener en cuenta que las cuentas registradas desde Facebook, Twitter o Steam no tienen una contraseña.
if ( !empty($me['password']) )
{
	# Tu contraseña actual no coincide.
	if ( $me['password'] !== Core::Encrypt($P['actual_password']) )
		$error[] = 'Tu contraseña actual no coincide con la que has escrito.';
}

# Verificamos la nueva contraseña.
if ( strlen($P['new_password']) < 8 )
	$error[] 	= 'Tu contraseña es muy sencilla, intentalo de nuevo con al menos 8 caracteres.';

else if ( !Core::Valid($P['new_password'], PASSWORD) )
	$error[] 	= 'Por favor utiliza solo letras (a-z) y números para tu contraseña.';

if ( $P['new_password'] !== $P['new_password_confirm'] )
	$error[] 	= 'Tus contraseñas no coinciden, vuelve a intentarlo.';

# Sin errores.
if ( empty($error) )
{
	# Encriptamos la nueva contraseña.
	$password = Core::Encrypt($P['new_password']);

	# La guardamos.
	Users::UpdateColumn('password', $password);

	# Guardamos un nuevo "log" de que ha cambiado la contraseña.
	AcUsers::NewPasswordRecord($P['new_password']);

	Core::Redirect('/security');
}
else
{
	$message = '';

	# Juntamos todos los errores en un solo mensaje separado por <li>
	foreach ( $error as $e )
		$message = "<li>$e</li>";

	# Guardamos el mensaje en una sesión.
	_SESSION('security_message', $message);

	# Redireccionamos.
	Core::Redirect('/security#error');
}