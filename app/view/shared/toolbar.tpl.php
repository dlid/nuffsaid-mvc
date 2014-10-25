<?php if( $this->di->userContext->isLoggedIn() ): ?>
a
<?php else: ?>
	<div class="right">
		<a href="">Logga in</a> |
		<a href="">Registrera dig</a> 
	</div>
<?php endif; ?>