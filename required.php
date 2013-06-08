<?
# Plantilla: required.html
$page['id'] 	= 'required';
require 'Init.php';

## --------------------------------------------------
## Home - Información requerida
## --------------------------------------------------
## Cuando un usuario inicie con su cuenta de Facebook,
## Twitter o Steam y falte información requerida
## se redireccionará al usuario a esta página para que
## complete esa información.
## --------------------------------------------------

# No hemos iniciado sesión.
if ( !LOG_IN )
	Core::Redirect();

# Errores ocurridos.
$errors = _SESSION('required_errors');
$data 	= _SESSION('required_data');

# Al parecer ocurrio un error.
if ( !empty($errors) )
{
	# Ejecutar código JavaScript (Mostrar el cuadro oculto de error)
	Tpl::JSAction('Kernel.ShowBox("error")');

	# Eliminar la sesión.
	_DELSESSION('required_errors');
}

# Obtener una lista de meses en el idioma del usuario.
$months 	= Date::GetListMonths();
# Obtener una lista de los países.
$countrys 	= Site::Get();

# Nombre de la página: InfoSmart Cuentas - Información requerida
$page['name']		= 'Información requerida';
# Con esto tenemos los estilos y scripts apropiados.
$page['subheader'] 	= 'login.header';
$page['login']		= true;