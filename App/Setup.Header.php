<?
/**
 * BeatRock
 *
 * Framework para el desarrollo de aplicaciones web.
 *
 * @author 		Iván Bravo <webmaster@infosmart.mx> @Kolesias123
 * @copyright 	InfoSmart 2013. Todos los derechos reservados.
 * @license 	http://creativecommons.org/licenses/by-sa/2.5/mx/  Creative Commons "Atribución-Licenciamiento Recíproco"
 * @link 		http://beatrock.infosmart.mx/
 * @version 	3.0
 *
*/

# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

## --------------------------------------------------
##        Funciones de cabecera
## --------------------------------------------------
## Utilice este archivo para definir la
## implementación de recursos CSS/JS/Meta en
## su aplicación, utilice la variable $page[id]
## para separar recursos de páginas únicas.
## --------------------------------------------------

# Esta página requiere inicio de sesión o es parte de la home (Panel del usuario)
# Lo ponemos aquí por lo de $page['home']
if ( ($page['require_login'] == true OR $page['home'] == true) && !LOG_IN )
	Core::Redirect('/connect/login?return=' . urlencode(PATH_NOW));

#####################################################
## IMPLEMENTACIÓN DE RECURSOS RECOMENDADOS.
#####################################################

# Cargamos Modernizr
Tpl::AddScript('modernizr', true);

# Cargamos main y el Kernel.
//Tpl::AddLocalScript('main');
Tpl::AddScript('kernel', true);

# Cargamos el estilo principal.
Tpl::AddStyle('style.light', true);

# Implementando la etiqueta RSS, solo si lo activamos desde la base de datos.
if ( $site['site_rss'] == 'true' )
	Tpl::AddStuff('<link rel="alternate" type="application/rss+xml" title="%site_name%: RSS" href="'.Keys($site['site_rss_path']).'" />');

#####################################################
## AGREGANDO ESTILOS SEGÚN PÁGINA
#####################################################

if ( DEVELOPMENT )
	Tpl::AddVar('Official', 'true', false);

# Agregamos las fuentes necesarias.
Tpl::AddFonts(array(
	'Roboto' 			=> '300,400,700',
	'Open Sans' 		=> '400,600,300,700', 	# ¡NO QUITAR!
	'Source Sans Pro' 	=> '200,300,400,600,700,400italic'
));

Tpl::AddStyle('style.fonts', true);
Tpl::AddStyle('style.forms', true);
Tpl::AddStyle('style.tips', true);

# Páginas del inicio de sesión (Inicio de sesión [Dah] y Registro)
if ( $page['login'] == true )
{
	Tpl::AddStyle('style.login');
	Tpl::AddLoadFile('app.login.js');
}

# Páginas del home o panel de usuario.
if ( $page['home'] == true )
{
	Tpl::AddStyle('style.home');
	Tpl::AddLoadFile('app.home.js');
}

# Restauración de contraseña.
if ( $page['class'] == 'forgot' )
{
	Tpl::AddStyle('style.login');
	Tpl::AddLoadFile('app.forgot.js');
}

# InfoSmart Developers
# Temporalmente, en el futuro se desarollará developers.infosmart.mx
if ( $page['dev'] == true )
{
	Tpl::AddStyle('style.dev');
	Tpl::AddLoadFile('app.dev.js');
}

# Formularios.
Tpl::AddStyle('style.forms');

# Aquí todo lo necesario en caso de haber iniciado sesión.
if ( LOG_IN )
{
	Tpl::AddVar('My_Username', $me['username']);

	if ( $page['home'] == true )
	{
		if ( $page['id'] !== 'required' )
		{
			Tpl::AddNav('Información', 	PATH);
			Tpl::AddNav('Seguridad', 	PATH . '/security');
			Tpl::AddNav('Privacidad', 	PATH . '/profile');
			Tpl::AddNav('Aplicaciones', PATH . '/apps');

			if ( date('Y') == 2013 AND date('m') < 7 )
				Tpl::AddNav('[new] Comandos por voz', PATH . '/voice');
			else
				Tpl::AddNav('Comandos por voz', PATH . '/voice');

			//Tpl::AddNav('Addons', PATH . '/addons');
		}
	}

	if ( $page['dev'] == true )
	{
		Tpl::AddNav('Aplicaciones', 	PATH . '/dev/apps');
		//Tpl::AddNav('Documentación', 	PATH . '/dev/docs');
	}

	Tpl::AddNav('Cerrar sesión', PATH . '/actions/logout');

	Tpl::BuildNav();
}

# Ajustes globales.
require KERNEL . 'Setup.Header.php';
