<?
require '../Init.php';

## --------------------------------------------------
## Inicio de sesión
## --------------------------------------------------
## Solo eso.
## --------------------------------------------------

# Ya hemos iniciado sesión.
# Redireccionar a la página de "retorno", si esta vacio pues a la home.
if ( LOG_IN )
	Core::Redirect($R['return']);

$error = array();

## Verificar que los datos sean correctos.

if ( _empty($P['id']) )
{
	$error['field']		= 'id';
	$error['message'] 	= 'Escribe tu dirección de correo, nombre de usuario o número de teléfono.';
}

else if ( _empty($P['password']) )
{
	$error['field']		= 'password';
	$error['message'] 	= 'Escribe tu contraseña.';
}

else if ( !Core::Valid($P['password'], PASSWORD) )
{
	$error['field']		= 'password';
	$error['message'] 	= 'Esta no parece una cotraseña correcta.';
}

else if ( !Users::Verify($P['id'], $P['password']) )
{
	$error['field']		= 'password';
	$error['message'] 	= 'Tu identificación o tu contraseña no son correctas.';
}

# Sin errores.
if ( empty($error) )
{
	# Obtener la ID del usuario e iniciar sesión.
	$userId = Users::GetColumn('id', $P['id']);
	$device = AcUsers::Login($userId);

	# ¡Un dispositivo nuevo!
	if ( !$device )
		$R['return'] = '/session?return=' . $R['return'];

	# Redireccionar a la página indicada o sencillamente a nuestro panel.
	Core::Redirect($R['return']);
}
else
{
	## ¡Uy! Un error.

	# Guardamos los errores y los datos necesarios en sesiones.
	_SESSION('login_error_field', 	$error['field']);
	_SESSION('login_error_message', $error['message']);
	_SESSION('login_id',			$PA['id']);

	# Si no hay una dirección de "Retornar al ocurrir un error"
	# poner la predeterminada.
	if ( _empty($R['return_error']) )
		$R['return_error'] = '/connect/login';

	# Redireccionar a la página al ocurrir un error.
	Core::Redirect($R['return_error']);
}
