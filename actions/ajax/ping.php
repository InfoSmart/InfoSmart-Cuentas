<?
require '../../Init.php';

## --------------------------------------------------
## Verificación de inicio de sesión.
## --------------------------------------------------
## Cada 30 segundos se solicita a esta página para
## verificar el inicio de sesión.
## --------------------------------------------------

if ( !LOG_IN )
	exit('FAIL');