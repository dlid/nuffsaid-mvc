<h1><?=$title?></h1>
 
<table>

<?php foreach ($users as $user) {


$prop = $user->getProperties();

$url = $this->url->create('users/id/' . $prop['id']);
$status = "";
if( $prop['active'] == null ) {
	$status = 'Inaktiv';
} else if( $prop['deleted'] != null ) {
	$status = 'Borttagen';
} else {
	$status = 'Aktiv';
}


echo <<<EOD
	<tr>
		<td><a href="$url">{$prop['id']}</a></td>
		<td>{$prop['acronym']}</td>
		<td>{$prop['name']}</td>
		<td>{$prop['email']}</td>
		<td>$status</td>
	</tr>
EOD;

} ?>
</table>
 
<p><a href='<?=$this->url->create('')?>'>Home</a></p>