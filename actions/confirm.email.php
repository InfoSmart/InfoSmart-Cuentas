<?
require '../Init.php';

## --------------------------------------------------
## Verificación de correo.
## --------------------------------------------------

# Llave de verificación vacia ¿entonces para que estas aquí?
if ( empty($G['key']) )
	Redirect();

# Obtenemos al usuario con esta llave de verificación.
$user = Users::GetSecure($G['key'], 'email_key');

# ¡El usuario no existe!
if ( !$user )
{
	# Redireccionar a una página con mensaje de llave inválida.
	_SESSION('email_valid_fail', true);
	Redirect('/validation');
}

# Actualizamos la información.
Users::Update(array(
	'email_verified' 	=> '1',
	'email_key' 		=> ''
), $user['id']);

_SESSION('email_valid_ok', true);
Redirect();