/* Información personal */
$('[data-save], .home input[type="hidden"]').on('change', SaveInfo);

/* Correo electrónico */
$('[data-action="delete_altemail"]').live('click', DeleteAltEmail);
$('#add_alt_email').on('click', AddAltEmail);
$('[data-action="verify_altemail"]').live('click', RequestAltEmailToken);

/* Privacidad */
$('#privacy input').on('change', SavePrivacy);

/* Foto de acceso */
$('#photo_access').on('change', UploadPhotoAccess);
$('#delete_photo_access').on('click', DeletePhotoAccess);

/* Palabra mágica */
$('#magic_word').on('focus', function() { $(this).prop('type', 'text'); });
$('#magic_word').on('blur', function() { $(this).prop('type', 'password'); });

/* Aplicaciones */
$('#delete_app').on('click', DeleteApp);

/* Validación de correo */
$('#request_email_key').live('click', RequestEmailToken);

/* Comandos por voz */
$('#start_voice').on('click', StartVoice);

if ( Cookie.Get('voice') == 'true' )
	StartVoice();

setInterval(Ping, 20000);

function Ping()
{
	$.get(Path + '/actions/ajax/ping.php', function(result)
	{
		if ( result == 'FAIL' )
			document.location.reload();
	});
}

/* Información personal */

/**
 * Guarda la información.
 */
function SaveInfo()
{
	var Type 	= $(this).attr('type'); // Tipo del input.
	var Name 	= $(this).attr('name'); // Nombre del campo.
	var Value 	= $(this).val(); 		// Valor
	var Th 		= $(this); 				// El campo en si.

	// Las herramientas de privacidad no.
	if ( Kernel.Contains(Name, 'privacy_') )
		return;

	// Un checkbox tiene una forma distinta de obtener su valor.
	if ( Type == 'checkbox' )
		Value = $('[name="' + Name + '"]:checked').val();

	// Lo desactivamos para que no se puede editar mientras lo guardamos.
	Th.attr('disabled', '');

	$.post(Path + '/actions/ajax/save.data.php', {'type': Name, 'value': Value}, function(result)
	{
		OOPS(result);

		// Al parecer hubo un error.
		if ( result.status == 'ERROR' )
		{
			// Se trata del cambio de nombre.
			if ( Name == 'firstname' || Name == 'lastname' )
			{
				$('[name="' + Name + '"]').addClass('error');
				HintError(result.message, 'name');
			}
			else
				HintInputError(result.message, Name);
		}

		// Todo perfecto.
		else if(result.status == 'OK')
		{
			// Removemos cualquier error.
			Th.removeClass('error');

			// Le notificamos al usuario que todo salio bien.
			AlertSave();

			// Si se trato del nombre, cambiarlo en tiempo real.
			if ( Name == 'firstname' )
				LiveUpdate('firstname', Value);

			if ( Name == 'lastname' )
				LiveUpdate('lastname', Value);

			if ( Name == 'location' )
				LiveUpdate('location', Value);
		}

		// Removemos la desactivación del campo.
		Th.removeAttr('disabled');

	}, 'JSON')
	.error(function(result)
	{
		console.log(result);
		OOPS(result.responseText);
	});
}

/* Correo electrónico */

/**
 * Agrega una nueva cuenta de correo electrónico alternativa.
 */
function AddAltEmail()
{
	// Obtenemos el correo electrónico.
	var AltEmail = $('#alt_email').val();

	// Esta vacio o no definido. ¿solo presiono el botón para probar?
	if ( AltEmail == '' || AltEmail == undefined )
		return;

	// Es muy pequeño.
	if ( AltEmail.length < 5 )
		return HintInputError('Debes escribir una cuenta de correo electrónico válida.', 'alt_email');

	// Desactivamos el campo.
	$('#add_alt_email').attr('disabled', '');
	$('[data-for="alt_email"]').hide();

	// Mostramos el mensaje "Cargando..."
	Info.ShowMsg();

	// Enviamos la petición.
	$.post(Path + '/actions/ajax/add.email.php', {'email': AltEmail}, function(result)
	{
		// Cerramos el mensaje y verificamos por algún error técnico.
		OOPS(result);
		Info.CloseMsg();

		// Todo salio perfecto.
		if ( result.status == 'OK' )
		{
			ResetAltEmail();
			AppendAltEmail(AltEmail, result.email_id);

			return AlertSave();
		}

		// Mostramos el error.
		HintInputError(result.message, 'alt_email');
		$('#add_alt_email').removeAttr('disabled');

	}, 'JSON')
	.error(function(result)
	{
		OOPS(result);
	});
}

/**
 * Restaura el campo para agregar un correo electrónico alternativo.
 */
function ResetAltEmail()
{
	$('#alt_email').removeClass('error');
	$('#alt_email').val('');

	$('#add_alt_email').removeAttr('disabled');

	$('[data-for="alt_email"]').html('');
	$('[data-for="alt_email"]').hide();
}

/**
 * Agrega un correo electrónico en la sección de correos alternativos.
 * @param {string} email Correo electrónico.
 */
function AppendAltEmail(email, emailID)
{
	// Este correo no es válido.
	if ( email == '' || email == undefined )
		return;

	if ( !is_numeric(emailID) )
		return;

	var Element 	= $('<p>' + email + '</p>');
	var T1Element 	= $('<span />', {
		'class': 	't1'
	});
	var DelElement 	= $('<a />', {
		'class': 			'icon',
		'title': 			'Eliminar correo',
		'data-action': 		'delete_altemail',
		'data-altemail': 	email,
		'text': 			''
	});
	var VerifyElement = $('<a />', {
		'class': 			'alert',
		'title': 			'Enviar correo de verificación.',
		'data-tooltip': 	'Los correos electronicos sin verificar no pueden ser usados en otras aplicaciones o para recuperar el acceso a su cuenta. De clic para enviar un correo electrónico de verificación.',
		'data-action': 		'verify_altemail',
		'data-altemail': 	emailID,
		'text': 			'¡Sin verificar!'
 	});

	T1Element.append(DelElement);
	Element.prepend(T1Element);
	Element.append(VerifyElement);

	$('.alt_emails').append(Element);
}

/**
 * Elimina un correo electrónico alternativo.
 */
function DeleteAltEmail()
{
	// Obtenemos el correo electrónico.
	var AltEmail = $(this).attr('data-altemail');

	console.log(AltEmail);

	// Este correo no es válido.
	if ( !is_numeric(AltEmail) )
		return BrokeMe();

	// Mostramos el mensaje de "Cargando..."
	Info.ShowMsg();

	// Enviamos la petición.
	$.post(Path + '/actions/ajax/delete.email.php', {'email': AltEmail}, function(result)
	{
		// Cerramos el mensaje y verificamos por algún error técnico.
		OOPS(result);

		// Todo salio bien.
		if ( result.status == 'OK' )
		{
			Info.CloseMsg();
			return SupAltEmail(AltEmail);
		}

		// !!!BUG: Info.ShowMsg se ejecuta antes de terminar Info.CloseMsg así que lo retrasamos 1 seg.
		Info.ShowMsg(result.message, 5000);

		// Ha ocurrido un error, pero aún así es posible quitar el correo de la lista.
		if ( result.continue == 'true' )
			SupAltEmail(AltEmail);

	}, 'JSON')
	.error(function(result)
	{
		OOPS(result);
	});
}

/**
 * Elimina un correo electrónico de la sección de correos alternativos.
 * @param {string} email Correo electrónico.
 */
function SupAltEmail(emailID)
{
	// El correo electrónico no es válido.
	if ( !is_numeric(emailID) )
		return;

	// Obtenemos el elemento raiz.
	var Element = $('.icon[data-altemail="' + emailID + '"]').parent().parent();

	// ¡No hay ningún elemento raiz!
	if ( Element == undefined )
		return;

	// Lo desaparecemos y después lo quitamos.
	Element.fadeOut('slow', function()
	{
		Element.remove();
	});
}

/* Privacidad */

/**
 * Guarda las opciones de privacidad.
 */
function SavePrivacy()
{
	var Th 		= $(this);
	var Value 	= Th.val();
	var Name 	= Th.attr('name');

	if ( Name == undefined || Name == '' )
		return;

	if ( Value == undefined || Value == '' )
		return;

	if ( Value != 'public' && Value != 'private' )
		return BrokeMe();

	$.post(Path + '/actions/ajax/save.privacy.php', {'type': Name, 'value': Value}, function(result)
	{
		OOPS(result);

		if ( result.status == 'ERROR' )
			HintInputError(result.message, Name);

		else if ( result.status == 'OK' )
		{
			Th.removeClass('error');
			AlertSave();
		}

	}, 'JSON')
	.error(function(result)
	{
		OOPS(result);
	});
}

/* Foto de acceso */

function UploadPhotoAccess(photo)
{
	photo = Photos.Data(photo);

	if(photo == undefined)
		return;

	Info.ShowProgresBar('¡Estamos subiendo tu foto! <span class="progress">0</span>%', '.progress');

	FD 	= new FormData();
	FD.append('photo', photo);

	XHR = new XMLHttpRequest();

	XHR.addEventListener('progress', function(e)
	{
		Loaded 	= e.position || e.loaded
		Total 	= e.totalSize || e.total;

		if(Total == 0 || Total == undefined)
			return console.error('Ha ocurrido un error con el progreso.');

		Progress = 100 / intval(Total) * intval(Loaded);
		Progress = round(Progress);

		$('.progress').html(Progress);

		if(Info._Bar_Active == true)
		{
			$('.ProgressBar').val(Progress);
			$('body, html').attr('style', ' ');
		}
	}, false);

	XHR.addEventListener('load', function(e)
	{
		if(this.readyState != 4)
			return Info.ShowMsg('¡Uy! Al parecer no pudimos recibir tu foto de perfil, reinicia la página y vuelve a intentarlo.', 5000);

		result = Json.Parse(e.currentTarget.response);
		OOPS(result);

		if(result.status == 'ERROR')
		{
			$('#photo_access').val('');
			return Info.ShowMsg(result.message, 6000);
		}
		else
		{
			$('#photo_access').val('');
			Info.CloseProgressBar();

			location.reload();
		}

	}, false);

	XHR.open('POST', Path + '/actions/ajax/upload.photo.access.php', true);
	XHR.setRequestHeader('X-File-Name', photo.name);
	XHR.setRequestHeader('X-File-Size', photo.size);
	XHR.setRequestHeader('X-File-Type', photo.type);
	XHR.send(FD);
}

function DeletePhotoAccess()
{
	Info.ShowMsg();

	$.get(Path + '/actions/ajax/delete.photo.access.php', function(result)
	{
		OOPS(result);

		if(result.status == 'OK')
		{
			$('.info_photo_access').fadeOut('slow', function()
			{
				$(this).remove();
			});
		}

		Info.CloseMsg();

	}, 'JSON');
}

/* Aplicaciones */

/**
 * Empieza el proceso para retirar permisos a una aplicación.
 */
function DeleteApp()
{
	var AppId = $(this).data('appid');

	// Esta ID no es númerica.
	if ( !is_numeric(AppId) )
		return;

	// ¿Estas seguro?
	if ( !confirm('¿Estas seguro de retirar los permisos a esta aplicación? Este proceso no podrá revertirse.') )
		return;

	// Mostramos mensaje de "Cargando..."
	Info.ShowMsg();

	$.post(Path + '/actions/ajax/delete.app.info.php', {'id': AppId}, function(results)
	{
		// Cerramos mensaje y verificamos por algún error técnico.
		Info.CloseMsg();
		OOPS(results);

		if ( results == 'VALID' )
			setTimeout(function() { RequestDeleteAppInfo(AppId) }, 1500);
		else
		{
			// Eliminamos la aplicación de nuestra lista.
			if ( results == 'OK' )
				DeleteAppConfirm(AppId);
		}

	}).error(function() { Info.CloseMsg(); DeleteAppConfirm(AppId); });
}

/**
 * Elimina una aplicación usada.
 * @param {integer} AppId ID de la aplicación
 */
function RequestDeleteAppInfo(AppId)
{
	var DeleteMessage = 'Esta aplicación te permite eliminar toda la información que hayas almacenado en ella ¿deseas hacerlo?';

	// Esta aplicación te permite eliminar tu información ¿quieres?
	if ( confirm(DeleteMessage) )
	{
		Info.ShowMsg('Estamos eliminando tu información de la aplicación, un momento...', 0, true);

		// Le decimos a la aplicación que elimine la información.
		$.post(Path + '/actions/ajax/delete.app.info.php', {'id': AppId, 'confirm': 'true'}, function(results)
		{
			// Cerramos mensaje y verificamos por algún error técnico.
			Info.CloseMsg();
			OOPS(results);

			// La aplicación no nos ha respondido como queriamos.
			if ( results != 'OK' )
				InfoPop.Show('La eliminación ha fallado.', 'No se ha podido eliminar tu información de la aplicación, es probable que se deba a un error técnico de parte de la misma.');

			// Eliminamos la aplicación de nuestra lista.
			DeleteAppConfirm(AppId);

		}).error(function() { Info.CloseMsg(); DeleteAppConfirm(AppId); });
	}
	else
	{
		// Eliminamos la aplicación de nuestra lista.
		if ( results == 'OK' || results == 'VALID' )
			DeleteAppConfirm(AppId);
	}
}

/**
 * Elimina una aplicación y retira sus permisos.
 * @param {integer} AppId ID de la aplicación.
 */
function DeleteAppConfirm(AppId)
{
	$.post(Path + '/actions/ajax/delete.app.php', {'id': AppId}, function(results)
	{
		// Verificamos por algún error técnico.
		OOPS(results);

		if ( results != 'OK')
			return;

		AlertSave();

		$('.app[data-appid="' + AppId + '"]').fadeOut('slow', function()
		{
			$(this).remove();
		});
	});
}

/* Validación de correo */

/**
 * Solicita una llave de verificación para el correo principal.
 */
function RequestEmailToken()
{
	$.get(Path + '/actions/ajax/request.email.token.php', function(result)
	{
		if ( result == 'OK' )
			return Info.ShowNotify('Hemos enviado a tu correo electrónico un enlace de validación. No olvides checar tu bandeja de SPAM.', 6000);

		Info.ShowNotify('¡Uy! Lo sentimos pero ha ocurrido un error. Verifica que tu correo electrónico se encuentre disponible y vuelve a intentarlo.');
	});
}

/**
 * Solicita una llave de verificación para un correo alternativo.
 */
function RequestAltEmailToken()
{
	// Obtenemos el correo electrónico.
	var AltEmail = $(this).attr('data-altemail');

	// Este correo no es válido.
	if ( !is_numeric(AltEmail) )
		return BrokeMe();

	// Mostramos el mensaje de "Cargando..."
	Info.ShowMsg();

	// Enviamos la petición.
	$.post(Path + '/actions/ajax/request.alt.email.token.php', {'email': AltEmail}, function(result)
	{
		// Cerramos el mensaje y verificamos por algún error técnico.
		OOPS(result);

		// Todo salio bien.
		if ( result.status == 'OK' )
			return Info.ShowMsg('Te hemos enviado el correo electrónico de verificación. Recuerda revisar tu bandeja de SPAM.', 6000);

		Info.ShowMsg(result.message, 5000);
	}, 'JSON')
	.error(function(result)
	{
		OOPS(result);
	});
}

/* Comandos por voz */

Voice.Set('información', function()
{
	document.location = Path + '/';
});

Voice.Set('seguridad', function()
{
	document.location = Path + '/security';
});

Voice.Set('privacidad', function()
{
	document.location = Path + '/profile';
});

Voice.Set('aplicaciones', function()
{
	document.location = Path + '/apps';
});

Voice.Set('cerrar sesión', function()
{
	document.location = Path + '/actions/logout';
});

Voice.Set('actualizar ubicación', function()
{
	Geo.Get(API.UpdateLocation, function() { });
});

Voice.Set('conectar con facebook', function()
{
	document.location = Path + '/actions/connect.social.php?type=facebook';
});

Voice.Set('conectar con twitter', function()
{
	document.location = Path + '/actions/connect.social.php?type=twitter';
});

Voice.Set('conectar con steam', function()
{
	document.location = Path + '/actions/connect.social.php?type=steam';
});

Voice.Set('actualizar mi foto ', function()
{
	$('#upload_photo').click();
});

function StartVoice()
{
	Voice.OnReady = function()
	{
		$('#start_voice').hide();
	};

	Voice.OnError = function(e)
	{
		if ( e == 'network' )
			alert('El servicio de comandos por voz no se ha iniciado correctamente debido a un problema con tu conexión.');
	};

	Voice.OnSpeech = function(speech)
	{
		console.log('[VOICE] Se ha detectado: ' + speech);
	};

	Voice.Enable();

	Cookie.Set('voice', 'true', 300, '.infosmart.mx');
}

/* Utilidades */

function LiveUpdate(id, value)
{
	$('.in-' + id).fadeOut('fast', function()
	{
		$('.in-' + id).html(value).fadeIn('slow');
	});
}

function HintInputError(message, fori)
{
	if(message == '' || message ==  undefined)
		return;

	if(fori == '' || fori == undefined)
		return;

	if($('#' + fori) != undefined)
		$('#' + fori).addClass('error');

	if($('[name="' + fori + '"]') != undefined)
		$('[name="' + fori + '"]').addClass('error');

	$('[data-for="' + fori + '"]').html(message).css('display', 'block');
}

function HintError(message, fori)
{
	if(message == '' || message ==  undefined)
		return;

	if(fori == '' || fori == undefined)
		return;

	$('[data-for="' + fori + '"]').html(message).css('display', 'block');
}

function AlertSave()
{
	InfoPop.Show('Tus cambios se han guardado con éxito.', '', 4000);
}

function BrokeMe()
{
	Info.ShowMsg('¡Vaya! Al parecer has roto algo, recarga la página y vuelve a intentarlo.', 5000);
}

function OOPS(data)
{
	if ( !Kernel.Contains(data, '{') )
		return;

	if ( is_string(data) )
		data = Json.Parse(data);

	if ( !is_array(data) )
		return;

	if ( data.system_error == undefined )
		return;

	Info.CloseMsg();

	$('#oops_code').html(data.system_error.report_code);

	InfoAlerts.ShowIt('#oops_message',
	{
		ok_callback: 'location.reload()',
		close_title: ''
	});
}