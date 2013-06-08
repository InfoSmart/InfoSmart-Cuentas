<?
# Acción ilegal.
if( !defined('BEATROCK') )
	exit;

# Eliminar todos los códigos que han expirado.
Auth::DeleteExpiredKeys();