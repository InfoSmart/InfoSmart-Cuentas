<?
# Con esto hacemos que sea necesario iniciar sesión.
$page['require_login'] = true;
require '../../Init.php';

## --------------------------------------------------
## Verificación de información del registro
## --------------------------------------------------
## Página que verifica la información del formulario
## de registro al momento que el usuario la escribe.
## --------------------------------------------------

$error = array();

# Nombre de usuario
if ( !empty($P['username']) )
{
	if ( strlen($P['username']) < 5 )
		$error['username'] 	= 'Tu nombre es muy corto, intentalo de nuevo con al menos 5 caracteres.';

	else if ( !Core::Valid($P['username'], USERNAME) )
		$error['username'] 	= 'Por favor utiliza solo letras (a-z) y números.';
}

# Contraseña
if ( !empty($P['password']) )
{
	if ( strlen($P['password']) < 8 )
		$error['password'] 	= 'Tu contraseña es muy sencilla, intentalo de nuevo con al menos 8 caracteres.';

	else if ( !Core::Valid($P['password'], PASSWORD) )
		$error['password'] 	= 'Por favor utiliza solo letras (a-z) y números.';

	if ( $P['password'] !== $P['confirm_password'] )
		$error['confirm_password'] = 'Tu contraseña no coincide con la que has escrito.';
}

# Fecha de nacimiento: día.
if ( !empty($P['bday']) )
{
	if ( $P['bday'] < 1 OR $P['bday'] > 31 OR !is_numeric($P['bday']) )
		$error['bday'] 		= 'El día es inválido, debes escribir un número de dos cifras.';
}

# Fecha de nacimiento: mes.
if ( !empty($P['bmonth']) )
{
	if ( $P['bmonth'] < 1 OR $P['bmonth'] > 12 OR !is_numeric($P['bmonth']) )
		$error['bmonth'] 	= 'El mes es inválido, debes seleccionar uno de la lista.';
}

# Fecha de nacimiento: año.
if ( !empty($P['byear']) )
{
	if ( $P['byear'] > (date('Y') - 10) )
		$error['byear'] 	= 'Lo sentimos, eres muy joven para registrarte.';

	# Solo admitimos personas menores de 120 años. (No nos vaya a pasar como a Facebook)
	# Aumentar en caso de demanda.
	if ( $P['byear'] < (date('Y') - 120) OR !is_numeric($P['byear']) )
		$error['byear'] 	= 'El año es inválido, debes escribir un número de cuatro cifras.';
}

# Correo electrónico
if ( !Core::Valid($P['email']) AND !empty($P['email']) )
	$error['email']			= 'Por favor escribe un correo electrónico válido.';

# Devolver el código JSON que será procesado por jQuery.
echo json_encode($error);
