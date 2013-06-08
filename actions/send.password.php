<?
require '../Init.php';

if ( LOG_IN )
	Redirect();

$user = Users::Get(_SESSION('forgot_id'));

# El usuario con esta identificación no existe.
if ( !$user )
	Redirect('/connect/forgot');

$error = false;

# Selecciono recuperar con un correo electrónico alternativo.
if ( $P['email'] !== 'main' )
{
	# Verificamos que este correo alternativo pertenece al usuario y además esta verificado.
	if ( !AcUsers::HaveAlternativeEmail($P['email'], $user) )
		$error = true;
}
else
	$P['email'] = $user['email'];

if ( !$error )
{
	# Generamos una nueva llave para el cambio de contraseña.
	$passwordToken = Core::Random(19);
	Users::UpdateColumn('password_token', $passwordToken, $user['id']);

	$user['password_token'] = $passwordToken;

	# Correo electrónico para validar correo.
	$message = new View(APP_VIEWS . 'mail' . DS . 'Forgot.Password');
	$message->AddPHP()->Build();

	# Ubicación de la persona que lo solicita.
	$country = Client::GetLocation($user['ip']);

	$mail = new Email(array(
		'method' 	=> 'mail',
		'to' 		=> $P['email'],
		'subject' 	=> 'Cambio de contraseña.',
		'message' 	=> $message,
		'from' 		=> 'support@infosmart.mx'
	));

	$result = $mail->Send();

	# Hubo un problema al enviar el correo.
	if ( !$result )
	{
		_SESSION('forgot_message', 'Hubo un problema al intentar enviarte el correo electrónico. Vuelve a intentarlo.');
		Redirect('/connect/forgot?type=recover_password');
	}

	Redirect('/connect/forgot?type=email_password');
}
else
{
	_SESSION('forgot_message', 'Selecciona un correo electrónico válido.');
	Redirect('/connect/forgot?type=recover_password');
}