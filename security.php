<?
require 'Init.php';

## --------------------------------------------------
## Home - Seguridad
## --------------------------------------------------
## Página para cambiar la seguridad de la cuenta
## del usuario, ya sabes, conexión segura (SSL), imagen
## de acceso, palabra mágica, etc...
## --------------------------------------------------

# Obtenemos los servicios sociales.
$tmpServices 	= Services::GetServices($me['service_hash']);
$services 		= array();

# Los ponemos en una variable más comoda.
while ( $row = Assoc($tmpServices) )
	$services[$row['service']] = $row;

# Obtenemos los errores.
$message = _SESSION('security_message');

# Al parecer si hubo errores.
if ( !empty($message) )
{
	Tpl::JSAction('Kernel.ShowBox("error");');
	_DELSESSION('security_message');
}

# Plantilla: home.profile.html
$page['id'] 	= 'home.security';
# Nombre de la página: InfoSmart Cuentas - Seguridad
$page['name'] 	= 'Seguridad';
# Con esto tenemos los estilos y scripts apropiados.
$page['home'] 	= true;
