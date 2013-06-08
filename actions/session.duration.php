<?
$page['require_login'] = true; // Con esto hacemos que sea necesario iniciar sesión.
require '../Init.php';

const MIN = 5;
const MAX = 60;

if ( !is_numeric($P['minutes']) OR $P['minutes'] < MIN OR $P['minutes'] > MAX )
	Core::Redirect();

$sessionLimit = Core::Time($P['minutes']);
Users::UpdateData('sessionLimit', $sessionLimit);

Core::Redirect();