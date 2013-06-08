<?
if ( !defined('BEATROCK') )
	exit;

class API
{
	/**
	 * Campos que deben ser excluidos.
	 * @var array
	 */
	static $PRIVATE = array(
		'password',
		'emails',
		'email_verified',
		'email_key',
		'public_api',
		'lastdevice',
		'sessionLimit',
		'status',
		'agent',
		'lasthost',
		'photo',
		'photo_ext',
		'photo_access',
		'profile',
		'rank',
		'reg_ip',
		'newsletter',
		'cookie',
		'service_hash',
		'secret',
		'banned',
		'service',
		'ext_api_session',
		'secure',
		'magic_word'
	);

	/**
	 * Campos públicos.
	 * @var array
	 */
	static $PUBLIC = array(
		'id',
		'name',
		'firstname',
		'lastname',
		'username',
		'gender'
	);

	/**
	 * Permisos disponibles
	 * @var array
	 */
	static $SCOPE = array(
		'email' 		=> 'Correo electrónico',
		'birthday' 		=> 'Fecha de nacimiento',
		'lastaccess'	=> 'Último acceso',
		'ip' 			=> 'Dirección IP',
		'browser' 		=> 'Navegador web',
		'os' 			=> 'Sistema operativo',
		'country' 		=> 'País',
		'location' 		=> 'Ubicación',
		'privacy' 		=> 'Configuración de privacidad'
	);

	/** AUTENTICACIÓN DE API **/

	/**
	 * Verifica que la llave privada de aplicación y la llave de autorización sean válidos.
	 * @param string $private   Llave privada.
	 * @param string $authorize Llave de autorización.
	 */
	static function VerifyAPIAccess($private, $authorize)
	{
		# Obtenemos la información de la aplicación con esta llave privada.
		$app = Apps::GetPrivate($private);

		# La llave privada no es válida.
		if ( !$app )
			return APP_NO_EXIST;

		# Obtenemos la información de la cuenta con esta llave de autorización.
		$user = Auth::GetUser($authorize, $app['id']);

		# La llave de autorización no es válida.
		if ( !is_array($user) )
			return $user;

		return true;
	}

	/**
	 * Devuelve la información de la cuenta y la aplicación que tengan estas llaves
	 * de autorización y privada.
	 * @param string $private   Llave privada.
	 * @param string $authorize Llave de autorización.
	 */
	static function GetAPIAccess($private, $authorize)
	{
		# Obtenemos la información de la aplicación con esta llave privada.
		$app = Apps::GetPrivate($private);

		# La llave privada no es válida.
		if ( !$app )
			return APP_NO_EXIST;

		# Obtenemos la información de la cuenta con esta llave de autorización.
		$user = Auth::GetUser($authorize, $app['id']);

		# La llave de autorización no es válida.
		if ( !is_array($user) )
			return $user;

		$result['user'] = $user;
		$result['app']	= $app;

		# Devolvemos la información.
		return $result;
	}

	/** ERRORES **/

	/**
	 * Devuelve la información de un código de error.
	 * @param string $code Código
	 */
	static function GetCodeError($code)
	{
		q("SELECT * FROM {DA}api_errors WHERE code = '$code' LIMIT 1");
		return ( Rows() > 0 ) ? Assoc() : false;
	}

	/**
	 * Devuelve la información acomodada de un código de error.
	 * @param string $code Código
	 */
	static function GetError($code)
	{
		# Obtenemos la información del error.
		$error = self::GetCodeError($code);

		# La acomodamos.
		$return = array(
			'error' => array(
				'code'			=> $error['code'],
				'title'			=> $error['title'],
				'description'	=> $error['description'],
				'lol' 			=> 'Información'
			)
		);

		return $return;
	}

	/**
	 * Imprime el código JSON de un error.
	 * @param string $code Código de error.
	 */
	static function Error($code)
	{
		# Esto no se trata de un error.
		if ( $code === true OR is_array($code) )
			return;

		# Obtenemos la información acomodada del error.
		$return = self::GetError($code);

		echo json_encode($return, JSON_PRETTY_PRINT);
		exit;
	}

	/** USUARIOS y FILTROS **/

	/**
	 * Ordena los permisos con sus traducciones.
	 * @param array $scope Permisos
	 */
	static function GetScope($scope)
	{
		$newScope = array();

		foreach ( $scope as $key => $value )
		{
			if ( empty(self::$SCOPE[$value]) )
				continue;

			$newScope[$value] = strtolower(self::$SCOPE[$value]);
		}

		return $newScope;
	}

	/**
	 * Hace cambios en el valor a presentar al usuario en un permiso.
	 * @param string $val   Valor incial
	 * @param string $scope Permiso.
	 */
	static function ScopeValue($val, $scope)
	{
		# Último acceso: Cambiar a tiempo entendible por humanos.
		if ( $scope == 'lastaccess' )
			$val = CalcTime($val);

		# Privacidad: Muy largo, no mostrar nada.
		if ( $scope == 'privacy' )
			$val = '';

		return $val;
	}

	/**
	 * Elimina toda la información privada/secreta de una cuenta.
	 * @param array $data Información de una cuenta.
	 */
	static function FilterInfo($data, $scope = array())
	{
		# Eliminamos cualquier campo que se encuentre en la variable de $PRIVATE
		foreach ( self::$PRIVATE as $key )
			unset($data[$key]);

		if ( $scope !== 'all' )
		{
			# Eliminamos cualquier campo del que no se hayan pedido permisos.
			foreach ( self::$SCOPE as $key => $value )
			{
				if ( !in_array($key, $scope) )
					unset($data[$key]);
			}
		}

		return $data;
	}

	/**
	 * Transforma la información de una cuenta en JSON
	 * Es usado para mantener la información de la cuenta en la variable me de JavaScript (Solo InfoSmart)
	 * @param array $data Información de una cuenta.
	 */
	static function PrepareJS($data)
	{
		$data = self::FilterInfo($data, 'all');

		foreach ( $data as $key => $value )
		{
			if ( !Contains($value, array('{', '[')) )
				continue;

			$json = json_decode($value, true);

			if ( is_array($json) )
				$data[$key] = $json;
		}

		return $data;
	}
}