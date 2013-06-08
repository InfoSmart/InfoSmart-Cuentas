<?
if ( !defined('BEATROCK') )
	exit;

const 	APP_FEATURE = 1,
		APP_LOGO 	= 2;

class Apps
{
	/**
	 * Define los nombres de los folders de guardado.
	 * @var array
	 */
	static $folders = array(
		APP_FEATURE => 'app.features.photos',
		APP_LOGO 	=> 'app.logos'
	);

	/**
	 * Crea una nueva aplicación.
	 * @param string $name        Nombre de la aplicación.
	 * @param string $description Descripción.
	 * @param array  $features    Características.
	 * @param integer $ownerId     ID del dueño.
	 */
	static function NewApp($name, $description, $website, $webmaster, $logo, $callbacks = array(), $features = array(), $connect = array(), $ownerId = ME_ID)
	{
		Insert('apps', array(
			'name'			=> $name,
			'description'	=> $description,
			'website' 		=> $website,
			'webmaster' 	=> $webmaster,
			'logo' 			=> $logo,
			'ownerId'		=> $ownerId,
			'callbacks' 	=> json_encode($callbacks),
			'features'		=> json_encode($features),
			'public_key'	=> Core::Random(30),
			'private_key'	=> Core::Random(60),
			'connect' 		=> json_encode($connect),
			'date' 			=> time()
		));

		return LastID();
	}

	/**
	 * Genera una nueva llave privada.
	 * @param integer $appId ID de la aplicación.
	 */
	static function GenerateKey($appId)
	{
		$newKey = Core::Random(60);
		self::UpdateColumn('private_key', $newKey, $appId);

		return $newKey;
	}

	/**
	 * Elimina una aplicación a partir de su clave pública.
	 * @param string $public Clave pública.
	 */
	static function DeleteAppPublic($public)
	{
		$app = self::GetPublic($public);

		q("DELETE FROM {DP}apps WHERE id = '$app[id]' LIMIT 1");
		q("DELETE FROM {DP}users_apps WHERE appId = '$app[id]'");
		q("DELETE FROM {DP}users_apps_authorize WHERE appId = '$app[id]'");
	}

	/**
	 * Verifica si una aplicación existe.
	 * @param integer $appId ID de la aplicación.
	 */
	static function AppExist($appId)
	{
		q("SELECT null FROM {DA}apps WHERE id = '$appId' LIMIT 1");
		return (Rows() > 0) ? true : false;
	}

	/**
	 * Obtiene la información de una aplicación.
	 * @param integer $public ID de la aplicación
	 */
	static function Get($appId)
	{
		q("SELECT * FROM {DP}apps WHERE id = '$appId' LIMIT 1");
		return ( Rows() > 0 ) ? Assoc() : false;
	}

	/**
	 * Obtiene la información de una aplicación.
	 * @param string $public Llave pública.
	 */
	static function GetPublic($public)
	{
		q("SELECT * FROM {DA}apps WHERE public_key = '$public' LIMIT 1");
		return ( Rows() > 0 ) ? Assoc() : false;
	}

	/**
	 * Obtiene la información de una aplicación.
	 * @param string $private Llave privada.
	 */
	static function GetPrivate($private)
	{
		q("SELECT * FROM {DA}apps WHERE private_key = '$private' LIMIT 1");
		return ( Rows() > 0 ) ? Assoc() : false;
	}

	/**
	 * Actualiza la información de una aplicación.
	 * @param array $data  Información
	 * @param integer $appId ID de la aplicación.
	 */
	static function Update($data, $appId)
	{
		Update('apps', $data, array(
			"id = '$appId' OR",
			"public_key = '$appId'"
		));
	}

	/**
	 * Actualiza una columna de una aplicación.
	 * @param string $key   Columna
	 * @param string $value Nuevo valor.
	 * @param integer $appId ID de la aplicación.
	 */
	static function UpdateColumn($key, $value, $appId)
	{
		Update('apps', array(
			$key => $value
		), array(
			"id = '$appId' OR",
			"public_key = '$appId'"
		));
	}

	/**
	 * Devuelve la carpeta según el tipo.
	 * @param integer $type Tipo
	 */
	static function GetFolder($type = APP_FEATURE)
	{
		return self::$folders[$type];
	}

	/**
	 * Guarda la foto de una característica.
	 * @param resource $photo $_FILE
	 */
	static function SaveFilePhoto($photo, $type = APP_FEATURE)
	{
		# No hay información que demuestre que proviene de un $_FILE
		if ( empty($photo['name']) OR $photo['error'] !== 0 )
			return false;

		# Obtenemos la carpeta donde guardar esta foto.
		$folder 	= self::GetFolder($type);
		# Las fotos son guardadas con su MD5 (Más practico)
		$md5 		= md5_file($photo['tmp_name']);
		# Ubicación final de la foto.
		$file 		= MEDIA . $folder . DS . $md5;

		# Solo imagenes PNG y JPG
		if ( $photo['type'] !== 'image/png' AND $photo['type'] !== 'image/jpeg' )
			return false;

		# Solo imagenes menores de 5 MB.
		if ( $photo['size'] > 5242880 )
			return false;

		# Copiamos el archivo original al destino.
		copy($photo['tmp_name'], $file);

		return $md5;
	}

	/**
	 * Obtiene los "bits" de una imagen en la carpeta de caracteristicas.
	 * @param string $md5 MD5
	 */
	static function GetPhoto($md5, $type = APP_FEATURE)
	{
		# ¿La foto predeterminada? ¡Claro!
		if ( $md5 == 'default' )
			return file_get_contents(RESOURCES_GLOBAL . '/images/id/web.default.png');

		# Obtenemos la carpeta donde guardar esta foto.
		$folder = self::GetFolder($type);
		# Esta sería la ubicación de la foto.
		$file 	= MEDIA . $folder . DS . $md5;

		# ¡La foto no existe! Algo malo sucedio aquí...
		if ( !file_exists($file) )
			return NO_EXIST;

		# Regresar bits.
		return file_get_contents($file);
	}

	#############################################################
	## CUENTAS
	#############################################################

	/**
	 * Verifica si la aplicación ya tiene permiso de acceder a la información de una cuenta.
	 * @param integer $appId  ID de la aplicación.
	 * @param integer $userId ID de la cuenta.
	 */
	static function Authorized($appId, $userId = ME_ID)
	{
		q("SELECT null FROM {DA}users_apps WHERE appId = '$appId' AND userId = '$userId' LIMIT 1");
		return ( Rows() > 0 ) ? true : false;
	}

	/**
	 * Agrega la autorización para que una aplicación use la información de una cuenta.
	 * @param integer $appId  	ID de la aplicación.
	 * @param array $scope 		Permisos
	 * @param integer $userId 	ID de la cuenta.
	 */
	static function Authorize($appId, $scope = array(), $userId = ME_ID)
	{
		# La aplicación ya tiene la autorización, solo actualizar la
		# última vez que accedio esta cuenta.
		if ( self::Authorized($appId, $userId) )
		{
			Update('users_apps', array(
				'scope' 	=> json_encode($scope),
				'last_used'	=> time()
			), array(
				"userId = '$userId' AND",
				"appId = '$appId'"
			));
		}

		# Añadir la autorización.
		else
		{
			Insert('users_apps', array(
				'userId'	=> $userId,
				'appId'		=> $appId,
				'scope' 	=> json_encode($scope),
				'last_used' => time(),
				'date'		=> time()
			));
		}
	}

	/**
	 * Devuelve una lista de las aplicaciones propietarias de una cuenta.
	 * @param integer $userId ID de la cuenta
	 */
	static function GetApps($userId = ME_ID)
	{
		$sql = q("SELECT * FROM {DA}apps WHERE ownerId = '$userId' ORDER BY id DESC");
		return ( Rows($sql) > 0 ) ? $sql : false;
	}

	/**
	 * Devuelve una lista de las aplicaciones que ha usado una cuenta.
	 * @param integer $userId ID de la cuenta.
	 */
	static function GetUsedApps($userId = ME_ID)
	{
		$sql = q("SELECT * FROM {DP}users_apps WHERE userId = '$userId' ORDER BY last_used DESC");
		return ( Rows($sql) > 0 ) ? $sql : false;
	}

	/**
	 * Devuelve la lista de permisos que requiere esta aplicación.
	 * @param integer $appId  ID de la aplicación.
	 * @param integer $userId ID de la cuenta.
	 */
	static function GetScopeUsedApp($appId, $userId = ME_ID)
	{
		$result = Get("SELECT scope FROM {DP}users_apps WHERE appId = '$appId' AND userId = '$userId' LIMIT 1");
		$result = json_decode($result, true);

		if ( !is_array($result) )
			return false;

		return $result;
	}

	/**
	 * Solicita a la aplicación eliminar la información del usuario.
	 * @param integer $appId  ID de la Aplicación
	 * @param integer $userId ID del usuario.
	 */
	static function RequestDeleteInfo($appId, $userId = ME_ID)
	{
		# Obtenemos información de la aplicación.
		$app = self::Get($appId);

		# Al parecer esta aplicación no existe.
		if ( !$app )
			return false;

		# Obtenemos las llamadas.
		$callbacks 	= json_decode($app['callbacks'], true);
		# Creamos una llave de autorización para eliminar la información.
		$key 		= Auth::NewKey($appId, $userId, KEY_DELETE_INFO);
		# Creamos una llave de autorización para obtener la información.
		$keyInfo 	= Auth::NewKey($appId, $userId);

		# Al parecer la llamada de eliminación no es válida.
		if ( !Core::Valid($callbacks['delete'], URL) AND !DEVELOPMENT OR empty($callbacks['delete']) )
			return false;

		# Creamos la petición.
		$curl 		= new Curl($callbacks['delete'], array('agent' => API_AGENT));
		$response 	= $curl->Post(array(
			'authorizeDelete' 	=> $key,
			'authorizeInfo' 	=> $keyInfo
		));
		$response 	= strtoupper(trim($response));

		if ( $response == 'OK' )
			return true;
		else
			return false;
	}

	/**
	 * Solicita a la aplicación actualizar la información del usuario.
	 * @param integer $appId  ID de la Aplicación
	 * @param integer $userId ID del usuario.
	 */
	static function RequestUpdateInfo($appId, $userId = ME_ID)
	{
		# Obtenemos información de la aplicación.
		$app = self::Get($appId);

		# Al parecer esta aplicación no existe.
		if ( !$app )
			return false;

		# Obtenemos las llamadas.
		$callbacks 	= json_decode($app['callbacks'], true);
		# Creamos una llave de autorización para actualizar la información.
		$key 		= Auth::NewKey($appId, $userId, KEY_UPDATE_INFO);
		# Creamos una llave de autorización para obtener la información.
		$keyInfo 	= Auth::NewKey($appId, $userId);

		# Al parecer la llamada de eliminación no es válida.
		if ( !Core::Valid($callbacks['updates'], URL) AND !DEVELOPMENT OR empty($callbacks['updates']) )
			return false;

		# Creamos la petición.
		$curl 		= new Curl($callbacks['updates'], array('agent' => API_AGENT));
		$response 	= $curl->Post(array(
			'authorizeUpdate' 	=> $key,
			'authorizeInfo' 	=> $keyInfo
		));
		$response 	= strtoupper(trim($response));

		if ( $response == 'OK' )
			return true;
		else
			return false;
	}

	/**
	 * Retira los permisos a una aplicación.
	 * @param integer $appId  ID de la aplicación.
	 * @param integer $userId ID de la cuenta.
	 */
	static function DeleteUsedApp($appId, $userId = ME_ID)
	{
		q("DELETE FROM {DP}users_apps WHERE appId = '$appId' AND userId = '$userId' LIMIT 1");
		q("DELETE FROM {DP}users_apps_authorize WHERE appId = '$appId' AND userId = '$userId'");
	}
}
