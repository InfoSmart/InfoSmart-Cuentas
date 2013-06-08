<?
require '../Init.php';

if ( LOG_IN )
	Redirect();

# Iniciamos el proceso de recuperación.
if ( empty($G['type']) )
	$page['id'] = 'forgot';

# Paso 2
else
{
	$message = _SESSION('forgot_message');

	# Al parecer hay un mensaje por mostrar.
	if ( !empty($message) )
	{
		Tpl::JSAction('Kernel.ShowBox("error")');
		_DELSESSION('forgot_message');
	}

	# Restauración de identificación.
	if ( $G['type'] == 'id' )
	{
		# El método de recuperación no es válido.
		if ( !in_array($G['recover'], $FORGOT_ID) )
			Redirect('/connect/forgot');

		Tpl::AddVar('Method', $G['recover']);

		$page['id'] = 'forgot.id';
	}

	# Restauración de contraseña.
	if ( $G['type'] == 'password' )
	{
		# El usuario que has escrito no es válido.
		if ( !Users::UserExist($G['id']) )
			Redirect('/connect/forgot');

		# Obtenemos una lista de los meses en el idioma del usuario.
		$months 	= Date::GetListMonths();
		# Obtenemos una lista de los países.
		$countrys 	= Site::Get();

		$data 	= _SESSION('forgot_data');

		$user = Users::Get($G['id']);
		$page['id'] = 'forgot.password';
	}

	# Selección de correo para la recuperación de contraseña.
	if ( $G['type'] == 'recover_password' )
	{
		$user = Users::Get(_SESSION('forgot_id'));

		# El usuario no es válido.
		if ( !$user )
			Redirect('/connect/forgot');

		# Obtenemos los correos alternativos.
		$emails = @json_decode($user['emails'], true);

		$page['id'] = 'forgot.select.email';
	}

	# Te hemos enviado el correo.
	if ( $G['type'] == 'email_password' )
		$page['id'] = 'forgot.success';

	# Cambio de contraseña.
	if ( $G['type'] == 'change' )
	{
		$user = Users::GetSecure($G['token'], 'password_token');

		# El usuario no es válido.
		if ( !$user OR empty($G['token']) )
			Redirect('/connect/forgot');

		AcUsers::Login($user['id']);
		Redirect('/security#password');
	}
}

if ( empty($page['id']) )
	Redirect('/connect/forgot');

$page['class'] 		= 'forgot';
$page['folder'] 	= 'connect';
$page['subheader'] 	= 'login.header';