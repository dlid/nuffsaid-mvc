
<span class="about">
	<a href="<?= $this->di->url->create('about') ?>"><span class="fa fa-info-circle"></span> Om Spelaihop</a>
</span>

<?php if( $this->di->userContext->isLoggedIn() ): ?>
a
<?php else: ?>
	<div class="right">
		<a href="#" title="Logga in med ditt befintliga konto">Logga in</a> |
		<a href="#" title="Registrera dig helt gratis">Registrera dig</a> 
	</div>
<?php endif; ?>