<h1><?=$title?></h1>
 
<?php

	$prop = $user->getProperties();

	$status = "";
	if( $prop['active'] == null ) {
		$url = $this->url->create('users/activate/' . $prop['id']);
		$status = 'Inaktiv | <a href="'.$url.'">aktivera</a>';
	} else if( $prop['deleted'] != null ) {
		$url = $this->url->create('users/restore/' . $prop['id']);
		$status = 'Borttagen (' . $prop['deleted'] . ') | <a href="'.$url.'">återställ</a>';
	} else {
		$url = $this->url->create('users/deactivate/' . $prop['id']);
		$status = 'Aktiv (' . $prop['active'] . ') | <a href="'.$url.'">inaktivera</a>';
	}

?>

<h2>#<?= $prop['id'] ?> <?= $prop['name'] ?></h2>

<ul>
	<li>Alias: <?= $prop['acronym'] ?></li>
	<li>Namn: <?= $prop['name'] ?></li>
	<li>Status: <?= $status ?></li>
	<li>E-post: <?= $prop['email'] ?></li>
	<li>Skapad: <?= $prop['created'] ?></li>
</ul>
