<?
if ( !defined('BEATROCK') )
	exit;

const 	KEY_GET_INFO 		= 1,
		KEY_DELETE_INFO 	= 2,
		KEY_UPDATE_INFO 	= 3,
		KEY_AUTHORIZE		= 4;

class Auth
{
	/**
	 * Crea una nueva llave de autorización.
	 * @param integer  $appId  	ID de la aplicación.
	 * @param integer $type 	Tipo de llave.
	 * @param integer $expire 	Horas de duración.
	 * @param integer  $userId  ID de la cuenta.
	 */
	static function NewKey($appId, $userId = ME_ID, $type = KEY_GET_INFO, $expire = 1)
	{
		# Generamos la llave.
		$key = Core::Random(50);
		# Obtenemos el tiempo unix de expiración.
		$exp = time() + ($expire * 60 * 60);

		Insert('users_apps_authorize', array(
			'userId'		=> $userId,
			'appId'			=> $appId,
			'authorize_key'	=> $key,
			'type' 			=> strval($type),
			'date'			=> time(),
			'expire'		=> $exp
		));

		return $key;
	}

	/**
	 * Devuelve la información de una llave de autorización.
	 * @param string $authorize  Llave
	 * @param integer $type      Tipo de llave
	 */
	static function GetKey($authorize, $type = KEY_GET_INFO)
	{
		$type = strval($type);

		# Consulta para obtener la información de este código.
		$query = q("SELECT * FROM {DP}users_apps_authorize WHERE authorize_key = '$authorize' AND type = '$type' LIMIT 1");
		return ( Rows($query) > 0 ) ? Assoc($query) : false;
	}

	/**
	 * Elimina todas las llaves de autorización que han expirado.
	 */
	static function DeleteExpiredKeys()
	{
		# Consulta para obtener todas las llaves.
		$query = q("SELECT * FROM {DP}users_apps_authorize ORDER BY id");

		# Verificamos cada una de ellas.
		while ( $row = Assoc($query) )
		{
			# Esta llave ha expirado, la eliminamos.
			if ( $key['expire'] <= time() )
				self::DeleteKey($row['id']);
		}
	}

	/**
	 * Elimina una llave de autorización.
	 * @param integer $codeId ID del código.
	 */
	static function DeleteKey($codeId)
	{
		q("DELETE FROM {DP} users_apps_authorize WHERE id = '$codeId' OR authorize_key = '$codeId' LIMIT 1");
	}

	#############################################################
	## CUENTAS
	#############################################################

	/**
	 * Devuelve la información de una cuenta a partir de un código de autorización.
	 * @param string $authorize  Código de autorización.
	 * @param integer $appId     ID de la aplicación que solicita la información.
	 */
	static function GetUser($authorize, $appId)
	{
		# Obtenemos información de la llave.
		$key = self::GetKey($authorize);

		# Esta clave de autorización no existe.
		# Nota: Las claves expiradas son eliminadas en 1 hora. (Nos falta dinero para un MySQL más grande...)
		if ( !$key )
			return AUTHKEY_NO_EXIST;

		# ¡Código expirado!
		if ( $key['expire'] <= time() )
		{
			# Eliminamos el código.
			self::DeleteCode($key['id']);
			return AUTHKEY_EXPIRED;
		}

		# Un segundo... Esta clave no se ha asignado para esta aplicación. (¿Intento de Hacking?)
		if ( $key['appId'] !== $appId )
			return AUTHKEY_NO_APP_OWNER;

		# Retornar información de la cuenta.
		return Users::Get($key['userId']);
	}
}