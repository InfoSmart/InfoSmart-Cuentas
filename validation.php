<?
require 'Init.php';

if ( _SESSION('email_valid_fail') !== true )
	Core::Redirect();

_DELSESSION('email_valid_fail');

$page['id'] 		= 'validation';
# Nombre de la página: InfoSmart Cuentas - Verificación
$page['name']		= 'Verificación';
# Con esto tenemos los estilos y scripts apropiados.
$page['subheader'] 	= 'login.header';
$page['login']		= true;