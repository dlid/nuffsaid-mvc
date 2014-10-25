
<div class="warning">
	<h1>Oj! Kunde inte spara kommentaren</h1>

	<hr />

	<p>Kontrollera nedanstående förslag på vad som kan ha gått fel, och gå sedan tillbaka
	till föregående sida för att försöka igen.</p>

	<ul>
		<?php foreach($validationErrors as $error): ?>
				<li><?= $error ?></li>
		<?php endforeach; ?>
	</ul>


	<p>Klicka på "Tillbaka" i din webbläsare eller <a href="javascript:history.go(-1)">klicka här för att gå tillbaka</a>.</p>

</div>