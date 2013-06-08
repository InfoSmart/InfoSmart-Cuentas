<?
require '../../Init.php';

# @TODO: ¿Que es esto?

if ( LOG_IN )
	exit;

if ( !Users::UserExist($P['id']) )
	echo 'NO_EXIST';
else
	echo 'OK';
