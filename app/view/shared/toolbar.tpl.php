
<span class="about">
	<a href="<?= $this->di->url->create('about') ?>"><span class="fa fa-info-circle"></span> Om Spelaihop</a>
</span>

<?php if( $this->di->userContext->isLoggedIn() ): ?>
<div class="right">
	<span class="fa fa-user"></span>&nbsp;<a href="<?= $this->di->url->create('users/profile') ?>"><?= $this->di->userContext->getUserDisplayName() ?></a>
	&nbsp;&nbsp;&nbsp;
	<span class="fa fa-star" style="color:yellow"></span> <a href="<?= $this->di->url->create('users/profile/' . $this->di->userContext->getUserId()) ?>/reputation" title="Your reputation"><strong style="color: yellow">331</strong></a>
	&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
	<a href="<?= $this->di->url->create('users/logout') ?>" title="Your reputation">Logga ut</a>

</div>
<?php else: ?>
	<div class="right">
		<a href="<?= $this->di->url->create('users/login') ?>" title="Logga in med ditt befintliga konto">Logga in</a> |
		<a href="<?= $this->di->url->create('users/signup') ?>" title="Registrera dig helt gratis">Registrera dig</a> 
	</div>
<?php endif; ?>