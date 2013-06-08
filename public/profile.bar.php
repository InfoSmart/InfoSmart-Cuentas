<?
require '../Init.php';

## --------------------------------------------------
## Barra de perfil
## --------------------------------------------------
## La barra de perfil es una barra que se mostrará
## en todas las aplicaciones y productos web de InfoSmart.
## Su objetivo es el de notificar el usuario que ha iniciado
## sesión en el computador y además ofrecer la navegación
## de la aplicación.
## --------------------------------------------------

# Permitir carga desde cualquier dominio.
Tpl::AllowCross('*');

# Plantilla: profile.bar.html (Si ha iniciado sesión) o profile.bar.default.html (Si no)
$page['id'] 		= (LOG_IN) ? 'profile.bar' : 'profile.bar.default';

# Carpeta: /api/
$page['folder'] 	= 'api';
# Sin cabecera
$page['header'] 	= false;
# Sin pie de página
$page['footer']		= false;
?>