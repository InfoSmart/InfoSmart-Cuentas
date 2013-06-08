<?
require '../Init.php';

## --------------------------------------------------
## Inicio de sesión con un servicio social.
## --------------------------------------------------
## Esta página inicia sesión o registra al usuario con
## un servicio social (Facebook, Twitter o Steam)
## --------------------------------------------------

# Dirección de regreso.
$return = urldecode($GA['return']);

# Ya hemos iniciado sesión.
if ( LOG_IN )
	Core::Redirect($return);

# Servicios permitidos.
$allowed = array('facebook', 'twitter', 'steam');

# ¿El usuario intento crear su propio servicio?
if ( !in_array($G['type'], $allowed) )
	Core::Redirect($return);

# !!!FIX
header('P3P: CP="CAO PSA OUR"');

# Se trata de un inicio hacia una aplicación.
if ( !empty($G['public']) )
{
	# Obtenemos la información de la aplicación a partir de su clave pública.
	$app = Apps::GetPublic($G['public']);

	# ¡La aplicación no existe!
	if ( !$app )
		Core::Redirect();

	# Obtenemos las claves para las API (Si las hay)
	$connect = @json_decode($app['connect'], true);

	# FACEBOOK
	if ( !empty($connect['facebook']['public']) )
	{
		Social::$data['facebook'] = array(
			'appId'		=> $connect['facebook']['public'],
			'secret'	=> $connect['facebook']['secret']
		);
	}

	# TWITTER
	if ( !empty($connect['twitter']['public']) )
	{
		Social::$data['twitter'] = array(
			'key'		=> $connect['twitter']['public'],
			'secret'	=> $connect['twitter']['secret']
		);
	}
}

/*
# Obtenemos la variable de intentos.
$loginTrys = _SESSION('login_trys');

# No existe, establecerla en 0.
if ( !is_numeric($loginTrys) )
	_SESSION('login_trys', 0);

# Aumentamos un intento.
++$loginTrys;
_SESSION('login_trys', $loginTrys);

# ¡Más de 4 intentos! Algo anda mal con InfoSmart Cuentas y el servicio...
if ( $loginTrys > 4 )
{
	//exit;
}
*/

# Obtener la información del usuario.
# Nota: Todo el sistema de las API ya esta programado en BeatRock.
$info 	= Social::Get($G['type']);

# Al parecer no se pudo obtener la información del servicio social.
if ( empty($info['id']) )
	Core::Redirect($return);

# ¿El usuario ya se registro con este servicio?
$verify = Services::Exist($info['id'], $G['type']);

# Al parecer no, registrarlo.
if ( !$verify )
{
	# Aquí estara el nombre de usuario final.
	$username 		= $info['username'];
	$usernameTaken 	= true;

	# Al parecer ya hay un usuario que tiene el mismo nombre de usuario.
	# Poner el servicio al principio del nombre actual. Ejemplo: facebook_kolesias123
	while ( $usernameTaken )
	{
		if ( Users::Exist($username, 'username') )
			$username = $G['type'] . '_' . $username . Core::Random(3, false);
		else
			$usernameTaken = false;
	}

	if ( is_array($info['hometown']) )
		$info['location'] = $info['hometown']['name'];

	# Generamos una llave para la validación de correo.
	$emailKey 	= Core::Random(19);
	# Generamos una llave para el dispositivo actual.
	$deviceKey 	= Core::Random(20);

	# Debido a que los usuarios podrán "aliar" sus servicios a su cuenta, el hash sirve para identificar que servicios son de tal cuenta.
	$hash 	= Services::Add($info['id'], $G['type'], $info['username'], json_encode($info));
	# Registrar usuario.
	$userId = Users::Add($username, '', $info['name'], $info['email'], '', true, array(
		'firstname' 	=> $info['first_name'],
		'lastname' 		=> $info['last_name'],
		'email_key' 	=> $emailKey,
		'emails'		=> '[]',
		'country'		=> $info['country'],
		'gender'		=> substr($info['gender'], 0, 1),
		'privacy'		=> '{"email":"private","birthday":"private","lastaccess":"public","os":"private","country":"private"}',
		'service_hash'	=> $hash,
		'location' 		=> $info['location'],
		'lastdevice' 	=> $deviceKey,
		'birthday' 		=> $info['birthday']
	));

	_COOKIE('last_device', $deviceKey);

	# Variables utiles para el correo de validación.
	$user['name'] 		= $info['name'];
	$user['email'] 		= $info['email'];
	$user['email_key'] 	= $emailKey;

	# Correo electrónico para validar correo.
	$message = new View(APP_VIEWS . 'mail' . DS . 'Confirm.Email');
	$message->AddPHP()->Build();

	if ( Core::Valid($info['email']) )
	{
		$mail = new Email(array(
			'method' 	=> 'mail',
			'to' 		=> $P['email'],
			'subject' 	=> 'Bienvenido a ' . SITE_NAME,
			'message' 	=> $message,
			'from' 		=> 'support@infosmart.mx'
		));

		$mail->Send();
	}

	# Obtenemos el 'service_hash' del servicio registrado.
	$data = Services::Get($info['id'], $G['type']);
	# Definimos la información del servicio.
	_SESSION('service_info', $data);

	# Al parecer la foto de perfil es una url, subirla a nuestro servidor.
	if ( Core::Valid($info['profile_image_url'], URL) )
		AcUsers::SaveUrlPhoto($info['profile_image_url'], $userId);
}
else
{
	# Ya esta registrado, solo iniciar sesión.
	$device = Social::Login($G['type'], $info);

	# ¡Un dispositivo nuevo!
	if ( !$device )
		$return = '/session?return=' . $return;
}

# Reiniciamos el contador.
_SESSION('login_trys', 0);

# Redireccionar a la Home.
Core::Redirect($return);
