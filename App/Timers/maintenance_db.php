<?
# Acción ilegal.
if ( !defined('BEATROCK') )
	exit;

###############################################################
## Cronometro: Mantenimiento de la base de datos
###############################################################
## Ejecuta los procesos necesarios para dar mantenimiento
## a la base de datos. Eliminar información innecesaria.
###############################################################

# Vaciar las visitas reales al sitio.
Query('site_visits_total')->Truncate()->Run();

# Vaciar los errores que han ocurrido.
Query('site_errors')->Truncate()->Run();

# Vaciar los logs.
Query('site_logs')->Truncate()->Run();
