<?
$page['is_ajax'] = true; // Bloquear todo acceso que no sea por AJAX.
require '../../Init.php';

if ( empty($P['query']) )
	exit;

if ( $P['type'] == 'id' )
{
	# Buscamos a los usuarios que coinciden.
	$users = Users::Search($P['query']);
}

if ( $P['type'] == 'alt_email' )
{
	# Buscamos al usuario con este correo alternativo.
	$users = AcUsers::GetUserWithAlternativeEmail($P['query']);
}

# ¡No existe!
if ( !$users )
	exit('<p>No se han encontrado resultados.</p>');

# GetUserWithAlternativeEmail devuelve un array con los datos del usuario.
# necesitamos pasarlo a una llave.
if ( $P['type'] == 'alt_email' )
{
	$tmp 	= $users;
	$users 	= array(0 => $tmp);
}

if ( $users instanceof mysqli_result )
{
	$tmp = array();

	while ( $row = Assoc($users) )
	{
		$tmp[$row['id']] = $row;
	}

	$users = $tmp;
}

# En estos momentos considero más fácil y útil hacer esto...
foreach ( $users as $row )
{
?>
<div class="user" data-username="<?=$row['username']?>" data-email="<?=Core::CensureEmail($row['email'])?>">
	<div class="selection">
		<input type="radio" name="userid" value="<?=$row['id']?>" data-title="¡Soy yo!" />
	</div>

	<div class="info">
		<figure>
			<img src="<?=PATH?>/photo/<?=$row['username']?>/small" />
		</figure>

		<span class="name"><?=$row['name']?></span>
		<span class="username"><?=$row['username']?></span>
	</div>
</div>
<? } ?>