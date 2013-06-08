<?
$page['require_login'] = true; // Con esto hacemos que sea necesario iniciar sesión.
require '../Init.php';

## --------------------------------------------------
## Desarrolladores - Edición de una aplicación.
## --------------------------------------------------
## Página para editar una aplicación.
## Ejemplo: /dev/apps/{llave publica}
## --------------------------------------------------

# Obtenemos la información de la aplicación a partir de su clave pública.
$app = Apps::GetPublic($G['public']);

# ¡La aplicación no existe!
if ( !$app )
	Core::Redirect('/dev/apps');

# El usuario que intenta editar la aplicación no es el creador de la misma :yaoming:
if ( $app['ownerId'] !== ME_ID )
	Core::Redirect('/dev/apps');

# Poner la informacion de la aplicación en variables de plantilla.
_t($app, 'app_');

# Descodificar JSON de las características.
$features 	= json_decode($app['features'], true);
# Descodificar JSON de las llamadas.
$callback 	= json_decode($app['callbacks'], true);
# Descodificar JSON de los datos de conexión personalizada.
$connect 	= json_decode($app['connect'], true);
# Errores ocurridos.
$error 		= _SESSION('app_error');

_t($callback, 'callback_');

# Al parecer ocurrio un error.
if( !empty($error) )
{
	# Ejecutar código JavaScript (Mostrar el cuadro oculto de error)
	Tpl::JSAction('Kernel.ShowBox("error");');
	# Eliminar la sesión.
	_DELSESSION('app_error');
}

# Plantilla: app.html
$page['id'] 		= 'app';
# Carpeta: /dev/
$page['folder']		= 'dev';
# Subcabecera: dev.header.html
$page['subheader'] 	= 'dev.header';
# Nombre de la página: InfoSmart Cuentas - Editando {APP}
$page['title'] 		= $page['name'] = 'Editando ' . $app['name'];
# Con esto tenemos los estilos y scripts apropiados.
$page['dev']		= true;
