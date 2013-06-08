<?
$page['require_login'] 	= true; // Con esto hacemos que sea necesario iniciar sesión.
$page['is_ajax'] 		= true; // Bloquear todo acceso que no sea por AJAX.

require '../../Init.php';

# Tu correo ya ha sido verificado.
if ( $me['email_verified']  == '1' )
	exit('VERIFIED');

# Generamos una llave para la verificación de correo.
$emailKey = Core::Random(19);
Users::UpdateColumn('email_key', $emailKey);

$me['email_key'] 	= $emailKey;
$user 				= $me;

# Correo electrónico para validar correo.
$message = new View(APP_VIEWS . 'mail' . DS . 'Validate.Email');
$message->AddPHP()->Build();

$mail = new Email(array(
	'method' 	=> 'mail',
	'to' 		=> $me['email'],
	'subject' 	=> 'Verificación de correo electrónico.',
	'message' 	=> $message,
	'from' 		=> 'support@infosmart.mx'
));

$result = $mail->Send();

if ( !$result )
	exit('FAIL');
else
	exit('OK');