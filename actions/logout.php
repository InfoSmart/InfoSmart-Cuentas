<?
$page['logout'] = true;
require '../Init.php';

## --------------------------------------------------
## Cierre de sesión
## --------------------------------------------------
## Solo eso.
## --------------------------------------------------

$return = urldecode($GA['return']);

# No hemos iniciado sesión, evitemos cualquier cosa innecesaria...
if ( !LOG_IN )
	Core::Redirect($return);

# Cerrar sesión y actualizar datos.
AcUsers::Logout();

# Redireccionar a la página de inicio.
Core::Redirect($return);
