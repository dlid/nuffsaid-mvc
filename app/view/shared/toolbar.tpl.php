
<span class="about">
	<a href="<?= $this->di->url->create('about') ?>"><span class="fa fa-info-circle"></span> Om Spelaihop</a>
</span>

<?php if( $this->di->userContext->isLoggedIn() ): ?>
<div class="right">
<?php if($this->di->userContext->getIsAdmin()): ?>
	<a href="">Administration</a> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; 
<?php endif; ?>

	

	<img alt="" src="http://www.gravatar.com/avatar/<?=md5($this->di->userContext->getUserEmail());?>.jpg?s=16"  />
	<a href="<?= $this->di->url->create('users/view/' . $this->di->userContext->getUserAcronym()) ?>"><?= $this->di->userContext->getUserDisplayName() ?></a>
	&nbsp;&nbsp;&nbsp;
	<a href="<?= $this->di->url->create('users/view/' . $this->di->userContext->getUserAcronym()) ?>/reputation" title="Dina ryktespoäng">
	<span class="fa fa-star<?= $this->di->userContext->getUserReputation() < 0 ? '-o orange' : ' yellow' ?>"></span> 
	<strong class="<?= $this->di->userContext->getUserReputation() < 0 ? 'orange' : 'yellow' ?>"><?=$this->di->userContext->getUserReputation()?></strong></a>
	<a href="<?= $this->di->url->create('users/view/' . $this->di->userContext->getUserAcronym()) ?>/activity" title="Dina aktivitetspoäng">
	<span class="fa fa fa-tachometer"></span> 
	<strong><?=$this->di->userContext->getUserActivityScore()?></strong></a>
	&nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
	<a href="<?= $this->di->url->create('users/logout') ?>" title="Your reputation">Logga ut</a>

</div>
<?php else: ?>
	<div class="right">
		<a href="<?= $this->di->url->create('users/login') ?>" title="Logga in med ditt befintliga konto">Logga in</a> |
		<a href="<?= $this->di->url->create('users/signup') ?>" title="Registrera dig helt gratis">Registrera dig</a> 
	</div>
<?php endif; ?>