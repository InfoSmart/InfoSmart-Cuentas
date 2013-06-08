<?
# Con esto hacemos que sea necesario iniciar sesión.
$page['require_login'] = true;
require '../../Init.php';

## --------------------------------------------------
## Eliminar foto de acceso.
## --------------------------------------------------
## Solo eso.
## --------------------------------------------------

$result = array();

# Esta función hace el trabajo por nosotros :)
AcUsers::DeletePhotoAccess();
$result['status'] = 'OK';

# Devolver el código JSON que será procesado por JavaScript.
echo json_encode($result);