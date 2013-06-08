<?
$page['take_cookies'] = true; // Restauramos las cookies recibidas.
require '../../Init.php';

## --------------------------------------------------
## Panel de perfil
## --------------------------------------------------
## La barra de perfil es una barra que se mostrará
## en todas las aplicaciones y productos web de InfoSmart.
## Su objetivo es el de notificar el usuario que ha iniciado
## sesión en el computador y además ofrecer la navegación
## de la aplicación.
## --------------------------------------------------

# Permitir carga desde cualquier dominio.
Tpl::AllowCross('*');

if ( !empty($PA['nav']) )
	$nav = json_decode(stripslashes($PA['nav']), true);

# Plantilla: panel.html (Si ha iniciado sesión) o panel.default.html (Si no)
$page['id'] 		= ( LOG_IN ) ? 'panel' : 'panel.default';

# Carpeta: /api/v1/
$page['folder'] 	= array('api', 'v1');
# Sin cabecera
$page['header'] 	= false;
# Sin pie de página
$page['footer']		= false;