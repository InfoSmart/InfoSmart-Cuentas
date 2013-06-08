/**
 * JavaScript - Recuperación de cuenta.
 * InfoSmart. Todos los derechos reservados.
 *
 * Copyright 2013 - Iván Bravo and jQuery Technology.
 * http://accounts.infosmart.mx - http://www.jquery.com/
**/

Forgot_Password_ID = false;

$('[name="type"]').on('click', TypeForgot);
$('#password_id').on('blur', VerifyID);
$('[name="recover"]').on('click', function() { $('.next').fadeIn('slow'); });

InfoTool.Set({'align': 'bottom'});

if ( Method != 'remember_alt_email' )
	$('#search').on('keydown', SearchID);
else
	$('#go_search').on('click', SearchID);


$('.user .selection input').live('click', FoundID);

/* Pasos y verificación */

function TypeForgot()
{
	var Value = $('[name="type"]:checked').val();

	if ( Value != 'id' && Value != 'password' )
		return BrokeMe();

	$('.next').fadeOut('slow');

	$('[data-step="2"]').hide();
	$('[data-step="2"][data-type="' + Value + '"]').fadeIn('slow');
}

function VerifyID()
{
	var LoginId = $('#password_id').val();

	if ( LoginId == undefined || LoginId == '' )
		return;

	$.post(Path + '/actions/ajax/verify.id.php', {'id': LoginId}, function(result)
	{
		if ( result == 'NO_EXIST' )
		{
			$('#password_id').addClass('error');
			$('[data-for="password_id"]').html('No hemos podido encontrar ninguna cuenta con esta identificación.').css('display', 'block');

			$('.next').fadeOut('fast');
		}
		else
		{
			$('#password_id').removeClass('error');
			$('[data-for="password_id"]').fadeOut('slow');

			$('.next').fadeIn('slow');
		}

	});
}

function SearchID()
{
	var Search 	= $('#search').val();
	var Type 	= $('#search').data('type');

	if ( Search == undefined || Search == '' )
		return;

	$.post(Path + '/actions/ajax/search.php', {'query': Search, 'type': Type}, function(result)
	{
		$('.results').html(result);
	});
}

function FoundID()
{
	var Parent 		= $(this).parent().parent();
	var Username 	= Parent.data('username');
	var Email 		= Parent.data('email');

	console.log(Username);

	$('.inalert-box .username').html(Username);
	$('.inalert-box .email').html(Email);

	InfoAlerts.ShowIt('#sucess_recover', {
		'ok_title': 	'Iniciar sesión',
		'ok_callback': 	'document.location = \'' + Path + '/connect/login?id=' + Username + '\'',
		'close_title': 	''
	});
}

/* Utilidades */

function BrokeMe()
{
	Info.ShowMsg('¡Vaya! Al parecer has roto algo, recarga la página y vuelve a intentarlo.', 5000);
}