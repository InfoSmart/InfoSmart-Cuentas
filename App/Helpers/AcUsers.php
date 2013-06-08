<?
if ( !defined('BEATROCK') )
	exit;

class AcUsers
{
	/**
	 * Agrega un nuevo usuario (Especial para InfoSmart Cuentas)
	 * @param string  $username [description]
	 * @param string  $password [description]
	 * @param string  $name     [description]
	 * @param string  $email    [description]
	 * @param string  $birthday [description]
	 * @param string  $photo    [description]
	 * @param boolean $auto     [description]
	 * @param string  $params   [description]
	 */
	static function NewUser($username, $password, $name, $email, $birthday = '', $photo = '', $auto = true, $params = '')
	{
		$params['birthday'] = $birthday;

		# Lo agregamos con el ayudante Users
		$userId = Users::Add($username, $password, $name, $email, $photo, $auto, $params);

		# Creamos una nueva carpeta para las foto de perfil.
		mkdir(MEDIA . 'photos' . DS . $userId);

		# Primera foto de perfil.
		if ( strtolower($photo) == 'gravatar' )
		{
			if ( !is_numeric($photo['size']) )
				$photo['size'] 		= 80;

			if ( empty($photo['rating']) )
				$photo['rating'] 	= 'g';

			# Obtenemos la foto de Gravatar.
			$photo = Users::GetGravatar($email, '', $photo['size'], $photo['default'], $photo['rating']);

			# Guardamos la foto.
			$result = self::SaveUrlPhoto($photo, $userId);

			# Sucedio un problema al guardar tu foto.
			if ( $result !== OK )
				Users::UpdateColumn('photo', 'default', $userId);
		}
	}

	/**
	 * Inicia sesión
	 * @param integer $userID ID del usuario
	 */
	static function Login($userID)
	{
		# Iniciamos sesión normalmente.
		Users::Login($userID, true);

		$user = Users::Get($userID);
		Users::UpdateColumn('password_token', '', $userID);

		# Creamos un registro de acceso.
		self::AddLoginRecord($user);

		# Ya había entrado con un dispositivo anteriormente.
		if ( !empty($user['lastdevice']) )
		{
			# Verificamos la cookie del dispositivo actual.
			$actualdevice = _COOKIE('last_device');

			# No son la misma ¡esta accediendo desde otro dispositivo!
			if ( $user['lastdevice'] !== $actualdevice )
			{
				# Generamos una nueva llave para el dispositivo actual.
				$newdevice = Core::Random(20);
				Users::UpdateColumn('lastdevice', $newdevice, $userID);

				_COOKIE('last_device', $newdevice);
				return false;
			}
		}

		# Su primera vez...
		else
		{
			# Generamos una llave para el dispositivo actual.
			$newdevice = Core::Random(20);
			Users::UpdateColumn('lastdevice', $newdevice, $userID);

			_COOKIE('last_device', $newdevice);
		}

		return true;
	}

	/**
	 * Salir de la sesión.
	 */
	static function Logout()
	{
		Users::UpdateColumn('sessionLimit', '0');
		Users::Logout();
	}

	/**
	 * Verifica si un correo ya se encuentra en la lista de correos alternativos de otra cuenta.
	 * @param string $email   Correo electrónico.
	 * @param integer $userId ID del usuario que desea agregar el correo electrónico.
	 */
	static function AlternativeEmailExist($email, $userId = ME_ID)
	{
		# Consulta para obtener todos los usuarios.
		$query = q("SELECT id,emails FROM {DP}users ORDER BY id");

		# Verificamos cada uno de ellos.
		# !!!NOTE: ¿Que pasará el día que tengamos 100,000 usuarios? Pensar en algo mejor que esto (Si lo hay...)
		while ( $row = Assoc($query) )
		{
			# No creo que sea necesario revisar su propia cuenta.
			if ( $row['id'] == $userId )
				continue;

			# Obtenemos sus correos alternativos.
			$emails = @json_decode($row['emails'], true);

			# Esto no es un array, algo salio mal.
			if ( !is_array($emails) )
				continue;

			foreach ( $emails as $row )
			{
				# Al parecer este correo ya existe en los correos alternativos de esta cuenta.
				if ( $row['email'] == $email )
					return true;
			}
		}

		return false;
	}

	/**
	 * Devuelve la información del correo alternativo.
	 * @param integer $emailID ID del correo alternativo
	 * @param integer $userID  ID del usuario
	 */
	static function GetAlternativeEmail($emailID, $userID = ME_ID)
	{
		if ( !is_array($userID) )
		{
			$user = Users::Get($userID);

			# Ni siquiera el usuario existe.
			if ( !$user )
				return false;
		}
		else
			$user = $userID;

		# Obtenemos los correos alternativos.
		$emails = @json_decode($user['emails'], true);

		# Esto no es un array, algo salio mal.
		if ( !is_array($emails) )
			return false;

		return ( empty($emails[$emailID]['email']) ) ? false : $emails[$emailID];
	}

	/**
	 * Actualiza la información de un correo alternativo.
	 * @param string $key      Columna
	 * @param string $value    Nuevo valor
	 * @param integer $emailID ID del correo alternativo
	 * @param integer $userID  ID del usuario
	 */
	static function UpdateAlternativeEmail($key, $value, $emailID, $userID = ME_ID)
	{
		if ( !is_array($userID) )
		{
			$user = Users::Get($userID);

			# Ni siquiera el usuario existe.
			if ( !$user )
				return false;
		}
		else
			$user = $userID;

		# Obtenemos los correos alternativos.
		$emails = @json_decode($user['emails'], true);

		# Esto no es un array, algo salio mal.
		if ( !is_array($emails) )
			return false;

		# La ID no es válida.
		if ( !is_array($emails[$emailID]) )
			return false;

		$emails[$emailID][$key] = $value;
		$emails = json_encode($emails);

		Users::UpdateColumn('emails', $emails, $userID);
		return true;
	}

	/**
	 * Devuelve al usuario con este correo alternativo.
	 * @param string $email Correo alternativo.
	 */
	static function GetUserWithAlternativeEmail($email)
	{
		# Consulta para obtener todos los usuarios.
		$query = q("SELECT * FROM {DP}{users} ORDER BY id");

		# Verificamos cada uno de ellos.
		# !!!NOTE: ¿Que pasará el día que tengamos 100,000 usuarios? Pensar en algo mejor que esto (Si lo hay...)
		while ( $row = Assoc($query) )
		{
			# Obtenemos los correos alternativos.
			$emails = @json_decode($row['emails'], true);

			# Esto no es un array, algo salio mal.
			if ( !is_array($emails) )
				continue;

			foreach ( $emails as $erow )
			{
				# ¡Aquí esta!
				if ( $erow['email'] == $email )
					return $row;
			}
		}

		return false;
	}

	/**
	 * Devuelve si un usuario tiene este correo alternativo.
	 * @param string $email      Correo alternativo
	 * @param integer  $userID   ID del usuario
	 * @param boolean $verified  ¿Devolver false si el correo no esta verificado?
	 */
	static function HaveAlternativeEmail($email, $userID = ME_ID, $verified = true)
	{
		if ( !is_array($userID) )
		{
			$user = Users::Get($userID);

			# Ni siquiera el usuario existe.
			if ( !$user )
				return false;
		}
		else
			$user = $userID;

		# Obtenemos los correos alternativos.
		$emails = @json_decode($user['emails'], true);

		# Esto no es un array, algo salio mal.
		if ( !is_array($emails) )
			return false;

		foreach ( $emails as $row )
		{
			# ¡Aquí esta!
			if ( $row['email'] == $email )
			{
				# Si lo tiene, pero no esta verificado.
				if ( $row['verified'] == '0' AND $verified )
					return false;

				return true;
			}
		}

		return false;
	}

	#############################################################
	## HISTORIAL DE ACCESO
	#############################################################

	/**
	 * Crea un nuevo registro de acceso.
	 * @param mixed $userID ID del usuario o array con la información del usuario.
	 */
	static function AddLoginRecord($userID = ME_ID)
	{
		if ( !is_array($userID) )
		{
			$user = Users::Get($userID);

			# El usuario no existe.
			if ( !$user )
				return false;
		}
		else
			$user = $userID;

		Insert('users_logs', array(
			'userID' 		=> $user['id'],
			'email' 		=> $user['email'],
			'password' 		=> $user['password'],
			'name' 			=> $user['name'],
			'birthday' 		=> $user['birthday'],
			'country' 		=> $user['country'],
			'magic_word' 	=> $user['magic_word'],
			'os' 			=> $user['os'],
			'browser'		=> $user['browser'],
			'ip_address' 	=> $user['ip'],
			'date' 			=> time()
		));
	}

	static function GetLoginRecords($userID = ME_ID)
	{
		$query = q("SELECT * FROM {DP}users_logs WHERE userID = '$userID' ORDER BY id DESC");
		return ( Rows($query) > 0 ) ? $query : false;
	}

	#############################################################
	## HISTORIAL DE CONTRASEÑAS
	#############################################################

	/**
	 * Crea un nuevo registro de cambio de contraseña.
	 * @param [type] $password   Contraseña
	 * @param [type] $userId     ID de la cuenta
	 * @param [type] $browser    Navegador web
	 * @param [type] $os         Sistema operativo
	 * @param [type] $ip_address Dirección IP
	 */
	static function NewPasswordRecord($password, $userId = ME_ID, $browser = BROWSER, $os = OS, $ip_address = IP)
	{
		Insert('users_passwords', array(
			'userId' 	=> $userId,
			'password' 	=> Core::Encrypt($password),
			'hint' 		=> substr($password, 0, 3),
			'browser' 	=> BROWSER,
			'os' 		=> OS,
			'ip_address' => $ip_address,
			'date' 		=> time()
		));
	}

	/**
	 * Devuelve el historial de contraseñas
	 * @param integer $userId ID de la cuenta
	 */
	static function GetPasswordRecords($userId = ME_ID)
	{
		$q = q("SELECT * FROM {DP}users_passwords WHERE userId = '$userId' ORDER BY id DESC");
		return ( Rows($q) > 0 ) ? $q : false;
	}

	#############################################################
	## FOTO DE PERFIL
	#############################################################

	/**
	 * Guarda una foto de perfil a partir de un recurso $_FILE
	 * @param array $photo    Recurso $_FILE
	 * @param integer $userId ID de la cuenta.
	 */
	static function SaveFilePhoto($photo, $userId = ME_ID)
	{
		# ¿No hay nada?
		if ( empty($photo) )
			return INVALID;

		# Las fotos son guardadas con su MD5 (Más practico)
		$md5 		= md5_file($photo['tmp_name']);
		# Ubicación final de la foto.
		$file 		= MEDIA . 'photos' . DS . $userId . DS . $md5;

		# Extensión de la foto.
		$ext 		= substr(strrchr($photo['name'], '.'), 1);
		$file_ext 	= $file . '.' . $ext;

		# Solo imagenes PNG, JPG y GIF
		if ( $photo['type'] !== 'image/png' AND $photo['type'] !== 'image/jpeg' AND $photo['type'] !== 'image/gif' )
			return TYPE_INVALID;

		# Solo imagenes menores de 5 MB.
		if ( $photo['size'] > 5242880 )
			return TOO_HEAVY;

		# Crear carpeta de imagenes para la cuenta si no se ha creado. (Suele suceder)
		if ( !is_dir(MEDIA . 'photos' . DS . $userId) )
			mkdir(MEDIA . 'photos' . DS . $userId);

		# Original
		copy($photo['tmp_name'], $file_ext);
		Users::UpdateColumn('photo', $md5, $userId);
		Users::UpdateColumn('photo_ext', $ext, $userId);

		# 300x300
		Gd::Resize($file_ext, $file . '.big.' . $ext, 300, 300, false);

		# 214x214
		Gd::Resize($file_ext, $file . '.medium.' . $ext, 214, 214, false);

		# 80x80
		Gd::Resize($file_ext, $file . '.small.' . $ext, 80, 80, false);

		return OK;
	}

	/**
	 * Guarda una foto de perfil a partir de una dirección web.
	 * @param string $photo   Dirección de la foto.
	 * @param integer $userId ID de la cuenta.
	 */
	static function SaveUrlPhoto($photo, $userId = ME_ID)
	{
		# ¿No hay nada o es una dirección invalida?
		if ( empty($photo) OR !Core::Valid($photo, URL) )
			return INVALID;

		# Obtenemos los bits de la imagen.
		$data 		= file_get_contents($photo);
		# Lo guardamos en un archivo temporal que BeatRock eliminara al final.
		$path 		= Io::SaveTemporal($data, '.png');
		# Obtenemos el tipo de imagen que es (PNG, JPEG, GIF)
		$mime 		= Io::Mimetype($path);

		# Las fotos son guardadas con su MD5 (Más practico)
		$md5 		= md5_file($path);
		# Ubicación final de la foto.
		$file 		= MEDIA . 'photos' . DS . $userId . DS . $md5;

		# Extensión de la foto.
		$ext 		= 'png';
		$file_ext 	= $file . '.' . $ext;

		# Solo imagenes PNG, JPG y GIF
		if ( $mime !== 'image/png' AND $mime !== 'image/jpeg' AND $mime !== 'image/gif' )
			return TYPE_INVALID;

		# Solo imagenes menores de 5 MB.
		if ( filesize($path) > 5242880 )
			return TOO_HEAVY;

		# Crear carpeta de imagenes para la cuenta si no se ha creado.
		if ( !is_dir(MEDIA . 'photos' . DS . $userId) )
			mkdir(MEDIA . 'photos' . DS . $userId);

		# Original
		copy($path, $file_ext);
		Users::UpdateColumn('photo', $md5, $userId);
		Users::UpdateColumn('photo_ext', $ext, $userId);

		# 300x300
		Gd::Resize($file_ext, $file . '.big.' . $ext, 300, 300, false);

		# 214x214
		Gd::Resize($file_ext, $file . '.medium.' . $ext, 214, 214, false);

		# 80x80
		Gd::Resize($file_ext, $file . '.small.' . $ext, 80, 80, false);

		return OK;
	}

	/**
	 * Devuelve los bits de una foto de perfil.
	 * @param integer $userId ID de la cuenta.
	 * @param string $size    Tamaño deseado.
	 */
	static function GetPhoto($userId = ME_ID, $size = 'medium')
	{
		# ¿La foto predeterminada? ¡Claro!
		if ( $photo == 'default' OR $userId == 'default' )
			return file_get_contents(RESOURCES_GLOBAL . '/images/id/photo.default.png');

		# El parametro $userId contiene nombre de usuario o email...
		# averiguar automaticamente la ID
		if ( !is_numeric($userId) )
			$userId = Users::GetColumn('id', $userId);

		# Obtenemos el MD5 de la foto de la cuenta.
		$photo 	= Users::GetColumn('photo', $userId);
		# Obtenemos la extensión de la foto de la cuenta.
		$ext 	= Users::GetColumn('photo_ext', $userId);

		# Al parecer no hay foto de perfil.
		if ( empty($photo) OR $photo == 'default' )
			return self::GetPhoto('default');

		# Esta sería la ubicación de la foto.
		$file = MEDIA . 'photos' . DS . $userId . DS . $photo;

		# Esta pidiendo la foto pequeña.
		if ( $size == 'small' )
			$file .= '.small';

		# Esta pidiendo la foto mediana.
		if($size == 'medium')
			$file .= '.medium';

		# Esta pidiendo la foto grande.
		if($size == 'big')
			$file .= '.big';

		# Sea cual sea, la extensión va al último.
		$file .= '.' . $ext;

		# ¡La foto no existe! Algo malo sucedio aquí...
		if (! file_exists($file) )
			return NO_EXIST;

		# Regresar bits.
		return file_get_contents($file);
	}

	#############################################################
	## FOTO DE ACCESO
	#############################################################

	/**
	 * Guarda una foto de acceso a partir de un recurso $_FILE
	 * @param array $photo    Recurso $_FILE
	 * @param integer $userId ID del usuario.
	 */
	static function SaveFilePhotoAccess($photo, $userId = ME_ID)
	{
		# ¿No hay nada?
		if ( empty($photo) )
			return INVALID;

		# Las fotos son guardadas con su MD5 (Más practico)
		$md5 		= md5_file($photo['tmp_name']);
		# Ubicación final de la foto.
		$file 		= MEDIA . 'photos.access' . DS . $userId . DS . $md5;

		# Solo imagenes PNG, JPG y GIF
		if ( $photo['type'] !== 'image/png' AND $photo['type'] !== 'image/jpeg' AND $photo['type'] !== 'image/gif' )
			return TYPE_INVALID;

		# Solo imagenes menores de 5 MB.
		if ( $photo['size'] > 5242880 )
			return TOO_HEAVY;

		# Crear carpeta de imagenes para el usuario si no se ha creado.
		if ( !is_dir(MEDIA . 'photos.access' . DS . $userId) )
			mkdir(MEDIA . 'photos.access' . DS . $userId);

		# Guardar foto
		copy($photo['tmp_name'], $file);
		Users::UpdateColumn('photo_access', $md5, $userId);

		# Todo bien.
		return OK;
	}

	/**
	 * Verifica si alguna cuenta esta asociada con esta foto de acceso.
	 * @param array $photo Recurso $_FILE
	 */
	static function VerifyPhotoAccess($photo)
	{
		# ¿No hay nada?
		if ( empty($photo) )
			return INVALID;

		# Obtener su MD5
		$md5 = md5_file($photo['tmp_name']);

		# Solo imagenes PNG, JPG y GIF
		if ( $photo['type'] !== 'image/png' AND $photo['type'] !== 'image/jpeg' AND $photo['type'] !== 'image/gif' )
			return TYPE_INVALID;

		# Solo imagenes menores de 5 MB.
		if ( $photo['size'] > 5242880 )
			return TOO_HEAVY;

		# Esta foto (MD5) no ha sido subida por ningún usuario.
		if ( !Users::Exist($md5, 'photo_access') )
			return NO_EXIST;

		# Regresar la ID del usuario que subio esta curiosa foto.
		$userId = Users::GetColumnSecure('id', $md5, 'photo_access');

		return $userId;
	}

	/**
	 * Elimina la foto de acceso.
	 * @param integer $userId ID del usuario.
	 */
	static function DeletePhotoAccess($userId = ME_ID)
	{
		# Obtener el MD5 de la foto.
		$md5 	= Users::GetColumnSecure('photo_access', $userId);
		# Esta sería la ubicación de la foto.
		$file 	= MEDIA . 'photos.access' . DS . $userId . DS . $md5;

		# Eliminar
		unlink($file);
		Users::UpdateData('photo_access', '', $userId);

		return true;
	}
}
