<?
$page['require_login'] = true; // Con esto hacemos que sea necesario iniciar sesión.
require '../Init.php';

## --------------------------------------------------
## Desarrolladores - Aplicaciones
## --------------------------------------------------
## Página para ver una lista de aplicaciones del usuario.
## --------------------------------------------------

# Obtenemos la lista de aplicaciones.
$apps = Apps::GetApps();

# Plantilla: apps.html
$page['id'] 		= 'apps';
# Carpeta: /dev/
$page['folder']		= 'dev';
# Nombre de la página: InfoSmart Cuentas - Aplicaciones
$page['title'] 		= $page['name']	= 'Mis aplicaciones';
# Subcabecera: dev.header.html
$page['subheader'] 	= 'dev.header';
# Con esto tenemos los estilos y scripts apropiados.
$page['dev']		= true;
