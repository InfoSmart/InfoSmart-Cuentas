<?
$page['require_login'] 	= true; // Con esto hacemos que sea necesario iniciar sesión.
$page['is_ajax'] 		= true; // Bloquear todo acceso que no sea por AJAX.

require '../../Init.php';

## --------------------------------------------------
## Guardado de información en tiempo real
## --------------------------------------------------
## Esta página es solicitada por AJAX cuando el usuario
## cambia su información.
## --------------------------------------------------

$type 	= $P['type'];
$value 	= $P['value'];

## Nota: $value contiene el valor nuevo al campo, si es un array se tomara como intento de cambiar varios campos a la vez.

# Nombre
if ( $P['type'] == 'firstname' )
{
	if ( _empty($P['value']) )
		$error = 'Por favor escribe tu nombre.';

	$value 	= array(
		'name'		=> $P['value'] . ' ' . $me['lastname'],
		'firstname'	=> $P['value']
	);
}

# Apellidos
if ( $P['type'] == 'lastname' )
{
	if ( _empty($P['value']) )
		$error = 'Por favor escribe tus apellidos.';

	$value = array(
		'name'		=> $me['firstname'] . ' ' . $P['value'],
		'lastname'	=> $P['value']
	);
}

# Perfil
# ¿El perfil será publico?
if ( $P['type'] == 'profile' )
{
	$value = '1';

	if ( _empty($P['value']) )
		$value = '0';
}

# Información pública desde la API pública
# ¿Permitir mostrarlo?
if ( $P['type'] == 'public_api' )
{
	$value = '1';

	if ( _empty($P['value']) )
		$value = '0';
}

# País de origen.
if ( $P['type'] == 'country' )
{
	if ( _empty($P['value']) )
		$error = 'Selecciona una ubicación de la lista.';
}

# Sexo (Genero)
if ( $P['type'] == 'gender' )
{
	if ( $P['value'] != 'm' AND $P['value'] != 'f' )
		$error = 'Por favor selecciona un sexo válido.';
}

# Seguridad
# ¿El usuario quiere usar el protocolo seguro HTTPS?
if ( $P['type'] == 'secure' )
{
	$value = '1';

	if ( _empty($P['value']) )
		$value = '0';
}

# Palabra magica.
if ( $P['type'] == 'magic_word' )
{
	if ( strlen($P['value']) > 50 )
		$error = 'La palabra mágica debe tener menos de 50 caracteres.';
}

# Sin errores.
if ( empty($error) )
{
	# $value es un array, guardar varias columnas a la vez.
	if ( is_array($value) )
		Users::Update($value);
	else
		Users::UpdateColumn($type, $value);

	$result['status'] = 'OK';
}
else
{
	$result['status'] 	= 'ERROR';
	$result['message'] 	= $error;
}

# Devolver el código JSON que será procesado por JavaScript.
echo json_encode($result);
