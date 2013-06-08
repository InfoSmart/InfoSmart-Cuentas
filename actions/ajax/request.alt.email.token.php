<?
$page['require_login'] 	= true; // Con esto hacemos que sea necesario iniciar sesión.
$page['is_ajax'] 		= true; // Bloquear todo acceso que no sea por AJAX.
require '../../Init.php';

$email = AcUsers::GetAlternativeEmail($P['email']);

# Es necesario validar tu correo electrónico.
if ( $me['email_verified'] == '0' )
	$error = 'No has verificado tu correo electrónico primario.';

# Ya ha sido verificado.
if ( $email['verified'] == '1' )
	$error = 'El correo ya ha sido verificado.';

# Esto no es una ID.
if ( !is_numeric($P['email']) )
	$error = '¡Vaya! Al parecer has roto algo, recarga la página y vuelve a intentarlo.';

# Verificamos que el correo aún este en la lista de correos alternativos.
if ( !is_array($email) )
	$error 	= 'Esta dirección de correo ya no se encuentra aliada a tu cuenta.';

# Sin errores.
if ( empty($error) )
{
	# Generamos una llave para la verificación.
	$token = Core::Random(15);
	$email = $email['email'];

	# Actualizamos la llave del correo.
	AcUsers::UpdateAlternativeEmail('token', $token, $P['email']);

	# Correo electrónico para validar correo.
	$message = new View(APP_VIEWS . 'mail' . DS . 'Confirm.AltEmail');
	$message->AddPHP()->Build();

	$mail = new Email(array(
		'method' 	=> 'mail',
		'to' 		=> $email,
		'subject' 	=> 'Verificación de correo electrónico alternativo.',
		'message' 	=> $message,
		'from' 		=> 'support@infosmart.mx'
	));

	$send = $mail->Send();

	# Hubo un problema al enviar el correo.
	if ( !$send )
	{
		$result['status']  = 'ERROR';
		$result['message'] = 'Hubo un problema al intentar enviarte el correo electrónico. Vuelve a intentarlo.';

	}
	else
		$result['status'] = 'OK';
}
else
{
	# ¡Uy! Un error.
	$result['status'] 	= 'ERROR';
	$result['message'] 	= $error;
}

# Devolver el código JSON que será procesado por JavaScript.
echo json_encode($result);