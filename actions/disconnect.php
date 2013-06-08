<?
$page['require_login'] = true; // Con esto hacemos que sea necesario iniciar sesión.
require '../Init.php';

# Obtenemos información del servicio.
$service = Services::Get($G['id'], $G['service']);

# ¡Uy! Al parecer no existe.
if ( !$service )
	$error = 'Al parecer este servicio ya no se encuentra conectado a tu cuenta.';

# ¿Alguien intento eliminar un servicio que no es de su cuenta?
if ( $service['service_hash'] !== $me['service_hash'] )
	Redirect('/security');

# No hubo problemas.
if ( empty($error) )
{
	# Eliminamos el servicio y regresamos.
	Services::Delete($G['id']);
}
else
{
	_SESSION('security_message', $error);
}

Redirect('/security');