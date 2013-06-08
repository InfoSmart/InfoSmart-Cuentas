<?
# Con esto hacemos que sea necesario iniciar sesión.
$page['require_login'] = true;
require '../../Init.php';

## --------------------------------------------------
## Desarrolladores - Crear una aplicación
## --------------------------------------------------
## Crea una nueva aplicación.
## --------------------------------------------------

$error = array();

# Nombre
if ( _empty($P['name']) )
	$error[] = 'El nombre de la aplicación no puede estar vacio.';

# Descripción
if ( _empty($P['description']) )
	$error[] = 'La descripción de la aplicación no puede estar vacia.';

# Sitio web
if ( !Core::Valid($P['website'], URL) )
	$error[] = 'El sitio web de la aplicación es inválida.';

# Correo electrónico
if ( !Core::Valid($P['webmaster']) )
	$error[] = 'Por favor escribe un correo electrónico válido.';

# Sin errores.
if ( empty($error) )
{
	$features = array();

	# Pasamos cada uno de los campos de las características y las ponemos en un array más arreglado...

	foreach ( $P['feature_title'] as $key => $value )
	{
		# Titulo vacio, ignorar.
		if ( empty($value) )
		{
			unset($P['feature_content'][$key], $P['feature_image'][$key]);
			continue;
		}

		$features[$key]['title'] = $value;
	}

	foreach ( $P['feature_content'] as $key => $value )
	{
		# Contenido vacio, ignorar.
		if ( empty($value) )
		{
			unset($features[$key], $P['feature_image'][$key]);
			continue;
		}

		$features[$key]['content'] = $value;
	}

	$photos = array();

	# Al parecer $_FILES no me ayuda, ahí que reordenar la forma en que me envia el array.
	foreach ( $_FILES['feature_image'] as $key => $value )
	{
		foreach ( $value as $vkey => $vvalue )
			$photos[$vkey][$key] = $vvalue;
	}

	$i = 0;
	foreach ( $features as $key => $value )
	{
		++$i;

		# Más de 5, ocurrio un problema en el JS o intento poner más de la cuenta...
		if ( $i > 5 )
			unset($features[$key], $photos[$key]);
	}

	# Se han subido imagenes.
	if ( !empty($photos) )
	{
		foreach ( $photos as $key => $file )
		{
			# Intentamos subir la imagen.
			$md5 = Apps::SaveFilePhoto($file);

			# Uy, al parecer la imagen no se subio.
			if ( !is_string($md5) )
			{
				$features[$key]['image'] = '';
				continue;
			}

			$features[$key]['image'] = $md5;
		}
	}

	# Hay un logotipo para la aplicación.
	if ( !empty($_FILES['logo']['tmp_name']) )
		$logo = Apps::SaveFilePhoto($_FILES['logo'], APP_LOGO);

	# Personalización de la conexión a Facebook
	if ( $P['connect_facebook'] == 'true' )
	{
		$connect['facebook']['public'] = $PN['connect_facebook_id'];
		$connect['facebook']['secret'] = $PN['connect_facebook_secret'];
	}

	# Personalización de la conexión a Twitter
	if ( $P['connect_twitter'] == 'true' )
	{
		$connect['twitter']['public'] = $PN['connect_twitter_id'];
		$connect['twitter']['secret'] = $PN['connect_twitter_secret'];
	}

	# Llamadas
	$callbacks = array();

	if ( Core::Valid($P['callback_updates'], URL) )
		$callbacks['updates'] 	= $P['callback_updates'];
	if ( Core::Valid($P['callback_delete'], URL) )
		$callbacks['delete'] 	= $P['callback_delete'];

	# Creamos la aplicación.
	$appId = Apps::NewApp($P['name'], $P['description'], $P['website'], $P['webmaster'], $logo, $callbacks, $features, $connect);

	# Vamonos a la lista de nuestras aplicaciones.
	Core::Redirect('/dev/apps');
}
else
{
	# ¡Uy! Un error.
	$message = '';

	# Juntamos todos los errores en un solo mensaje separado por <li>
	foreach ( $error as $e )
		$message .= "<li>$e</li>";

	# Guardamos los errores en una sesión.
	_SESSION('new_app_error', $message);

	# Redireccionar a la página de antes.
	Core::Redirect('/dev/apps/new');
}
