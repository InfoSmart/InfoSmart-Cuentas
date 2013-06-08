<?
require 'Init.php';

## --------------------------------------------------
## Home - Aplicaciones
## --------------------------------------------------
## Página para
## --------------------------------------------------

$apps = Apps::GetUsedApps();

# Plantilla: home.apps.html
$page['id'] 	= 'home.apps';
# Nombre de la página: InfoSmart Cuentas - Aplicaciones
$page['name'] 	= 'Aplicaciones';
# Con esto tenemos los estilos y scripts apropiados.
$page['home'] 	= true;
