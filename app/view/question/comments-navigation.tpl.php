<?php if( $count > 0): ?>
<div id="answers" class="tabs-container">
  <ul class="tabs">
    <li class="text"><?=$count?> svar</li>
  </ul> 
</div>
<?php else: ?>
	<h4 id="answers">Inga svar ännu</h4>
	<p>Ännu har ingen svarat på denna fråga. Om du har ett svar - skrev det här nedanför.</p>
<?php endif; ?>