<?
$page['require_login'] = true; 		// Con esto hacemos que sea necesario iniciar sesión.
$page['only_infosmart'] = true; 	// Solo permitir desde infosmart.mx
$page['take_cookies'] 	= true; 	// Tomar las COOKIES enviadas.

require '../../Init.php';

# Permitir carga desde cualquier dominio.
Tpl::AllowCross('*');

# Los datos necesarios son inválidos.
if ( empty($P['latitude']) OR empty($P['longitude']) )
	exit;

# Usamos la API de Google Maps para obtener la ubicación.
$url 	= 'http://maps.googleapis.com/maps/api/geocode/json?latlng=' . $P['latitude'] . ',' . $P['longitude'] . '&sensor=false';
$data 	= file_get_contents($url);
$data 	= json_decode($data, true);

$results = array();

# Al parecer algo salio mal...
if ( $data['status'] !== 'OK' )
	exit;

# Ordenamos los resultados con la información que nos interesa.
foreach ( $data['results'] as $key => $result )
{
	if ( !in_array('locality', $result['types']) )
		continue;

	$results = $result;
}

# Al parecer algo salio mal...
if ( empty($results) )
	exit;

# Guardamos la información y la devolvemos.
Users::UpdateColumn('location', $results['formatted_address']);
echo json_encode($results);