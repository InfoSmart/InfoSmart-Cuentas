<?
$page['id'] = 'required'; // !!HACKHACK: Esto hará que BeatRock cargue la plantilla required.html, sin embargo también evitará el redireccionamiento por falta de información.
require '../Init.php';

## --------------------------------------------------
## Guardar información requerdia
## --------------------------------------------------
## Página para procesar la información enviada del
## formulario de "Información requerida"
## Es decir: /required.php
## --------------------------------------------------

# No hemos iniciado sesión.
# Redireccionar a la página principal.
if ( !LOG_IN )
	Core::Redirect();

$error 	= array();
$data 	= array();

## Nota: $me contiene la información del usuario actual.

# Si faltaba el nombre ¿Wtf?
if ( empty($me['name']) OR empty($me['firstname']) OR empty($me['lastname']) )
{
	$data['name'] = true;

	# Nombre
	if ( _empty($P['firstname']) )
		$error[] 	= 'Por favor escribe tu nombre.';

	# Apellidos
	if ( _empty($P['lastname']) )
		$error[] 	= 'Por favor escribe tus apellidos.';
}

# Si faltaba la fecha de nacimiento...
if ( empty($me['birthday']) )
{
	$data['birthday'] = true;

	# Fecha de nacimiento
	if ( $P['bday'] < 1 OR $P['bday'] > 31 OR !is_numeric($P['bday']) )
		$error[] 	= 'El día es inválido, debes escribir un número de dos cifras.';

	if ( $P['bmonth'] < 1 OR $P['bmonth'] > 12 OR !is_numeric($P['bmonth']) )
		$error[] 	= 'El mes es inválido, debes seleccionar uno de la lista.';

	if ( $P['byear'] > (date('Y') - 10) )
		$error[] 	= 'Lo sentimos, eres muy joven para registrarte.';

	if ( $P['byear'] < (date('Y') - 120) OR !is_numeric($P['byear']) )
		$error[] 	= 'El año es inválido, debes escribir un número de cuatro cifras.';
}

# Si faltaba el correo electrónico...
if ( empty($me['email']) )
{
	$data['email'] = $P['email'];

	# Correo electrónico
	if ( !Core::Valid($P['email']) )
		$error[]	= 'Por favor escribe un correo electrónico válido.';

	else if ( Users::Exist($P['email']) )
		$error[] 	= 'Tu correo electrónico ya esta siendo ocupado por otra cuenta.';
}

# Si faltaba el sexo (genero)...
if ( empty($me['gender']) )
{
	$data['gender'] = $P['gender'];

	# Sexo (Genero)
	if ( $P['gender'] !== 'm' AND $P['gender'] !== 'f' )
		$error[] 	= 'Por favor selecciona un sexo válido.';
}

# Si faltaba el país de residencia...
if ( empty($me['country']) )
{
	$data['country'] = $P['country'];

	# Ubicación
	if ( _empty($P['country']) OR strlen($P['country']) > 2 )
		$error[] 	= 'Por favor selecciona tu ubicación';
}

# No hay nada nuevo...
if ( empty($data) )
	Core::Redirect();

# Sin errores.
if ( empty($error) )
{
	# Al parecer el nombre si faltaba, hacer los cambios necesarios.
	if ( $data['name'] == true )
	{
		$data['name'] 		= $P['firstname'] . ' ' . $P['lastname'];
		$data['firstname']	= $P['firstname'];
		$data['lastname']	= $P['lastname'];
	}

	# Al parecer la fecha de nacimiento si faltaba, hacer los cambios necesarios.
	if ( $data['birthday'] == true )
		$data['birthday'] = $P['bday'] . '/' . $P['bmonth'] . '/' . $P['byear'];

	# Actualizar la información.
	Users::Update($data);
	# Redireccionar a la Home.
	Core::Redirect();
}
else
{
	# ¡Uy! Un error.
	$message = '';

	# Juntamos todos los errores en un solo mensaje separado por <li>
	foreach ( $error as $e )
		$message = "<li>$e</li>";

	# Guardamos los errores y la información del formulario en sesiones.
	_SESSION('required_errors', $message);
	_SESSION('required_data', $P);

	# Redireccionamos a la página de nuevo.
	Core::Redirect('/required');
}
