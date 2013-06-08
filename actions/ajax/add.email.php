<?
# Con esto hacemos que sea necesario iniciar sesión.
$page['require_login'] = true;
require '../../Init.php';

## --------------------------------------------------
## Agregar correo electrónico alternativo.
## --------------------------------------------------
## Solo eso.
## --------------------------------------------------

$result = array();

# Es necesario validar tu correo electrónico.
if ( $me['email_verified'] == '0' )
	$error[] = 'No has verificado tu correo electrónico primario.';

# Verificamos que el correo electronico sea válido.
if ( empty($P['email']) )
	$error = 'Por favor escribe la dirección de correo.';

else if ( !Core::Valid($P['email']) )
	$error = 'Debes escribir una cuenta de correo electrónico válida.';

# También que no sea ya un correo alternativo.
else if ( AcUsers::HaveAlternativeEmail($P['email'], $me, false) )
	$error = 'Esta dirección de correo ya se encuentra aliada a tu cuenta.';

# No puedes agregar tu propio correo primario como alternativo.
else if ( $P['email'] == $me['email'] )
	$error = 'No puedes usar tu correo primario como una dirección de correo alternativa.';

# Este correo no debe ser el primario de ninguna otra cuenta.
# O este correo ya es un correo alternativo de otra cuenta.
else if ( Users::Exist($P['email']) OR AcUsers::AlternativeEmailExist($P['email']) )
	$error = 'Esta dirección de correo ya esta siendo ocupado por otra cuenta.';

# Sin errores.
if ( empty($error) )
{
	# Generamos una llave para la verificación.
	$token = Core::Random(15);
	$email = $P['email'];

	# Agregamos el correo alternativo a la lista y lo convertimos a JSON.
	$me_emails[] = array('email' => $P['email'], 'verified' => '0', 'token' => $token);

	# Obtenemos la ID del correo.
	end($me_emails);
	$emailID = key($me_emails);

	$me_emails = json_encode($me_emails);

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

	$mail->Send();

	# Actualizamos la lista.
	Users::UpdateColumn('emails', $me_emails);

	$result['status'] 	= 'OK';
	$result['email_id'] = $emailID;
}
else
{
	# ¡Uy! Un error.
	$result['status'] 	= 'ERROR';
	$result['message'] 	= $error;
}

# Devolver el código JSON que será procesado por JavaScript.
echo json_encode($result);
