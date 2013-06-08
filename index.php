<?
require 'Init.php';

## --------------------------------------------------
## Home
## --------------------------------------------------
## Home del usuario.
## --------------------------------------------------

# No hemos iniciado sesión.
if ( !LOG_IN )
	Redirect('/connect/login');

# Obtenemos una lista de los meses en el idioma del usuario.
$months 	= Date::GetListMonths();
# Obtenemos una lista de los países.
$countrys 	= Site::Get();
# Separar la fecha de nacimiento en día, mes y año.
$birth 		= explode('/', $me['birthday']);
# Obtenemos el historial de acceso.
$loginRecords = AcUsers::GetLoginRecords();

# Verificación de correo electrónico.
$emailValid = _SESSION('email_valid_ok');
if ( $emailValid )
	_DELSESSION('email_valid_ok');

# Plantilla: home.html
$page['id'] 	= 'home';
# Con esto tenemos los estilos y scripts apropiados.
$page['home']	= true;