<?
require 'Init.php';

## --------------------------------------------------
## Home - Perfil
## --------------------------------------------------
## Página para cambiar la información del perfil
## del usuario.
## --------------------------------------------------

$profileName = Core::FormatToUrl($me['username']);

# Plantilla: home.profile.html
$page['id'] 	= 'home.profile';
# Con esto tenemos los estilos y scripts apropiados.
$page['home']	= true;
