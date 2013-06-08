/**
 * JavaScript - Desarrolladores
 * InfoSmart. Todos los derechos reservados.
 *
 * Copyright 2013 - Iván Bravo and jQuery Technology.
 * http://accounts.infosmart.mx - http://www.jquery.com/
**/

var ImgEl = null;

$('#add_feature').on('click', AddFeature);

$('.feature figure img').live('click', Pre_UploadFeaturePhoto);
$('.feature figure input[type="file"]').live('change', UploadFeaturePhoto);

$('#logo').live('change', UploadLogoPhoto);

$('.apps section [data-action="new-key"]').on('click', GenerateKey);

$('.connect input[type="checkbox"]').on('click', OpenConnection);
$('.connect input[type="checkbox"]').each(function()
{
	if ( $(this).attr('data-checked') == 'true' )
		$(this).click();
});

$('#delete_app').on('click', function(e)
{
	if ( !confirm('¿Estas seguro de eliminar esta aplicación? Este proceso no se puede revertir.') )
		return e.preventDefault();
});

/**
 * Obtiene el número de caracteristicas.
 */
function CountFeatures()
{
	var i = 0;

	$('.feature').each(function()
	{
		++i;
	});

	return i;
}

/**
 * Agrega una nueva característica
 */
function AddFeature()
{
	if ( CountFeatures() >= 5 )
		return alert('Solo puede agregar un máximo de 5 características.');

	Html = $('#feature-template').html();
	Html = '<div class="feature">' + Html + '</div>';

	$('#features').append(Html);
}

/**
 * Prepara la variable necesaria para la subida de fotos
 * para las características.
 */
function Pre_UploadFeaturePhoto()
{
	ImgEl = $(this);
	ImgEl.next('input[type="file"]').click();
}

/**
 * Muestra la vista previa de una foto de característica.
 * @param {object} e
 */
function UploadFeaturePhoto(e)
{
	Photos.Return(e, function(data)
	{
		ImgEl.attr('src', data);
	});
}

/**
 * Muestra la vista previa del Logo
 * @param {object} e
 */
function UploadLogoPhoto(e)
{
	Photos.Return(e, function(data)
	{
		$('#logo-image').attr('src', data);
	});
}

/**
 * Muestra los detalles de una conexión.
 */
function OpenConnection()
{
	var Details = $(this).parent().next('.details');
	Details.slideToggle('slow');
}

function GenerateKey()
{
	if ( !confirm('Ten en cuenta que al generar una nueva llave privada deberás actualizar tus peticiones a la API con la nueva llave. ¿Deseas continuar?') )
		return;

	var Element = $(this);
	var App 	= Element.parent().parent().parent();
	var AppID 	= App.data('appid');

	Element.fadeOut('fast');
	Info.ShowMsg();

	$.post(Path + '/actions/dev/generate.key.php', {'public': AppID}, function(result)
	{
		if ( result.length < 55 )
		{
			Info.ShowMsg('Ha ocurrido un problema al intentar procesar tu solicitud. Vuelve a intentarlo más tarde.');
			return;
		}

		var Private = App.find('.private-key');

		Private.fadeOut('fast', function()
		{
			Private.html(result);
			Private.addClass('fresh');
			Private.fadeIn('slow');

			setTimeout(function()
			{
				Private.removeClass('fresh');
			}, 5000);

		});

		Element.fadeIn('fast');
		Info.CloseMsg();

	})
	.error(function()
	{
		Element.fadeIn('fast');
		Info.CloseMsg();
	});
}