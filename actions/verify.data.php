<?
require '../Init.php';

if ( LOG_IN )
	Redirect();

$user = Users::Get($P['id']);

# El usuario con esta identificación no existe.
if ( !$user )
	Redirect('/connect/forgot');

# La identificación es el correo electrónico, usarla para validar el mismo.
if ( Core::Valid($P['id']) )
	$P['email'] = $P['id'];

$error = false;

# El nombre no coincide.
if ( strtolower($P['name']) !== strtolower($user['name']) )
	$error = true;

# El correo electrónico primario no coincide.
if ( $P['email'] !== $user['email'] )
	$error = true;

$birth = explode('/', $user['birthday']);

# Día de nacimiento.
if ( $birth[0] !== $P['bday'] )
	$error = true;

# Mes de nacimiento.
if ( $birth[1] !== $P['bmonth'] )
	$error = true;

# Año de nacimiento.
if ( $birth[2] !== $P['byear'] )
	$error = true;

# El país no coincide.
if ( $P['country'] !== $user['country'] )
	$error = true;

# Palabra magica
if ( !empty($user['magic_word']) )
{
	if ( $P['magic'] !== $user['magic_word'] )
		$error = true;
}

# ¡No hubo errores!
if ( !$error )
{
	# Guardamos una sesión con la ID del usuario, será validado en la página siguiente.
	_SESSION('forgot_id', $user['id']);
	_DELSESSION('forgot_data', $PA);

	Redirect('/connect/forgot?type=recover_password');
}
else
{
	# Guardamos la información del formulario.
	_SESSION('forgot_data', $PA);

	_SESSION('forgot_message', 'Los datos que has escrito no coinciden con los establecidos en la cuenta. Revisalos detalladamente e intenta nuevamente.');
	Redirect('/connect/forgot?type=password&id=' . $P['id']);
}