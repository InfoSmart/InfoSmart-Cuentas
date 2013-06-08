/**
 * JavaScript - Página principal.
 * InfoSmart. Todos los derechos reservados.
 *
 * Copyright 2013 - Iván Bravo and jQuery Technology.
 * http://accounts.infosmart.mx - http://www.jquery.com/
**/

// Información del registro.
var Data = {};

/* Verificación de información en el registro */
$('input:not([type="submit"])').on('focusout', VerifyData);

/* Foto de acceso */
$('#access_photo').on('click', function() { $('#photo_access').click(); });
$('#photo_access').on('change', UploadPhoto);

$('#fb-login').on('click', FacebookLogin);

/* Verificación de información en el registro */

/**
 * Genera una clave única de registra a partir de la información.
 */
function PrepareData()
{
	$('input:not([type="submit"])').each(function()
	{
		var Key 	= $(this).attr('name');
		var Value 	= $(this).val();

		if ( Key == 'key' )
			return;

		Data[Key] = Value;
	});

	$('#key').val(Base64.encode(Json.Build(Data)));
}

/**
 * Verifica si un campo es correcto.
 */
function VerifyData()
{
	// Generamos una clave.
	PrepareData();

	// Enviamos toda la información del registro.
	$.post(Path + '/actions/ajax/verify.data.php', Data, function(result)
	{
		// Ocultamos los errores actuales.
		$('.herror').hide();
		$('input').removeClass('error');

		for ( key in result )
		{
			var For = key;

			// Estos campos son del cumpleaños.
			if ( key == 'bday' || key == 'bmonth' || key == 'byear' )
				For = 'birthday';

			// Agregamos la clase de error al campo con error.
			$('input[name="' + key + '"]').addClass('error');

			// Mostramos el error en un texto pequeño color rojo.
			$('.herror[data-for="' + For + '"]').html(result[key]);
			$('.herror[data-for="' + For + '"]').show();
		}

	}, 'JSON');
}

/* Foto de acceso */

/**
 * Sube la imagen de acceso.
 * @param {} photo Imagen
 */
function UploadPhoto(photo)
{
	// Obtenemos el recurso de la imagen.
	var photo = Photos.Data(photo);

	// No hay nada.
	if ( photo == undefined )
		return;

	// Subiendo imagen, un momento...
	Info.ShowMsg('Un momento, estamos subiendo y verificando tu imagen...', 0, true);

	// Creamos un nuevo formulario.
	FD = new FormData();
	FD.append('photo', photo);

	// Creamos una nueva petición AJAX
	XHR = new XMLHttpRequest();

	XHR.addEventListener('load', function(e)
	{
		// Algo ocurrio mal...
		if ( this.readyState != 4 )
			return Info.ShowMsg('¡Uy! Al parecer no pudimos recibir tu foto de acceso, reinicia la página y vuelve a intentarlo.', 5000);

		// Descodificamos la respuesta.
		result = Json.Parse(e.currentTarget.response);
		OOPS(result);

		Info.CloseMsg();

		if(result.status == 'ERROR')
		{
			$('.box-error').html(result.message);
			Kernel.ShowBox('error');
		}
		else
			document.location = Return;

	}, false);

	XHR.open('POST', Path + '/actions/ajax/verify.photo.access.php');
	XHR.setRequestHeader('X-File-Name', photo.name);
	XHR.setRequestHeader('X-File-Size', photo.size);
	XHR.setRequestHeader('X-File-Type', photo.type);
	XHR.send(FD);
}

/* Utilidades */

/**
 * Detecta si ha ocurrido un error técnico.
 * @param {array} data Respuesta descodificada.
 */
function OOPS(data)
{
	if ( data.system_error == undefined )
		return;

	$('#oops_code').html(data.system_error.report_code);
	InfoAlerts.ShowIt('#oops_message',
	{
		ok_callback: 'location.reload()',
		close_title: ''
	});
}

function FacebookLogin()
{
	if ( FB == undefined )
		return;

	FB.getLoginStatus(function(response)
	{
		if ( response.status == 'connected' )
			GoToFacebok();

		else if ( response.status == 'not_authorized' )
			FB.login(GoToFacebok, {scope: FacebookScope});

		else
			FB.login(GoToFacebok, {scope: FacebookScope});
	});
}

function GoToFacebok()
{
	document.location = FacebookLoginUrl;
}