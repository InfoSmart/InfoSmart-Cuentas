<?
$page['require_login'] = true; // Con esto hacemos que sea necesario iniciar sesión.
require '../Init.php';

## --------------------------------------------------
## Desarrolladores - Crear nueva aplicación
## --------------------------------------------------
## Página para crear una nueva aplicación.
## --------------------------------------------------

# Errores ocurridos.
$error = _SESSION('new_app_error');

# Al parecer ocurrio un error.
if ( !empty($error) )
{
	# Ejecutar código JavaScript (Mostrar el cuadro oculto de error)
	Tpl::JSAction('Kernel.ShowBox("error");');
	# Eliminar la sesión.
	_DELSESSION('new_app_error');
}

# Plantilla: new.app.html
$page['id'] 		= 'new.app';
# Carpeta: /dev/
$page['folder']		= 'dev';
# Nombre de la página: InfoSmart Cuentas - Crear nueva aplicación
$page['title'] 		= $page['name']	= 'Crear nueva aplicación';
# Subcabecera: dev.header.html
$page['subheader'] 	= 'dev.header';
# Con esto tenemos los estilos y scripts apropiados.
$page['dev']		= true;
