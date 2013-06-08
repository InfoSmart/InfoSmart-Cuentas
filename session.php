<?
require 'Init.php';

# No hemos iniciado sesión.
if ( !LOG_IN )
	Core::Redirect();

$return = urldecode($GA['return']);

# Plantilla: session.html
$page['id'] 		= 'session';
# Nombre de la página: InfoSmart Cuentas - Sesión actual
$page['name']		= 'Sesión actual';
# Con esto tenemos los estilos y scripts apropiados.
$page['subheader'] 	= 'login.header';
$page['login']		= true;