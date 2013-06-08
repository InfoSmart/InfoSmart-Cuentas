<?
require '../Init.php';

# Obtenemos los errores e información pasada.
$errors 	= _SESSION('register_errors');
$data 		= _SESSION('register_data');

# Al parecer hubo errores.
if ( !empty($errors) )
{
	Tpl::JSAction('Kernel.ShowBox("error")');
	_DELSESSION('register_errors');
}

# Obtenemos una lista de los meses en el idioma del usuario.
$months 	= Date::GetListMonths();
# Obtenemos una lista de los países.
$countrys 	= Site::Get();

$page['id']	 		= 'register';
$page['folder']		= 'connect';
$page['name']		= 'Registro';
$page['subheader'] 	= 'login.header';
$page['login']		= true;