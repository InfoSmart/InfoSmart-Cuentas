<?
$page['require_login'] = true; // Con esto hacemos que sea necesario iniciar sesión.
require '../Init.php';

## --------------------------------------------------
## Inicio de sesión con un servicio social.
## --------------------------------------------------
## Esta página conecta la cuenta actual con un servicio
## social.
## --------------------------------------------------

# Servicios permitidos.
$allowed = array('facebook', 'twitter', 'steam');

# ¿El usuario intento crear su propio servicio?
if ( !in_array($G['type'], $allowed) )
	Redirect('/security');

# !!!FIX
header('P3P: CP="CAO PSA OUR"');

# Obtener la información del usuario.
# Nota: Todo el sistema de las API ya esta programado en BeatRock.
$info 	= Social::Get($G['type']);

# Al parecer no se pudo obtener la información del servicio social.
if ( empty($info['id']) )
{
	_SESSION('security_message', '<li>No hemos podido obtener la información de tu servicio social. Vuelve a intentarlo más tarde.</li>');
	Redirect('/security');
}

# ¿El usuario ya se registro con este servicio?
$verify = Services::Exist($info['id'], $G['type']);

# Al parecer no, registrarlo.
if ( !$verify )
{
	# Debido a que los usuarios podrán "aliar" sus servicios a su cuenta, el hash sirve para identificar que servicios son de tal cuenta.
	$hash  = Services::Add($info['id'], $G['type'], $info['username'], json_encode($info), $me['service_hash']);
}
else
	_SESSION('security_message', '<li>Este servicio social ya esta conectada con otra cuenta de ' . SITE_NAME . '.</li>');

Redirect('/security');