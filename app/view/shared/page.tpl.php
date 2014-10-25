
	


	<div class="row content-block content-main">
		<div id='main'<?= !isset($sidebar) ? ' class="wide"' : null; ?>>
			<?= $content ?>
		</div>
		<?php if(isset($sidebar)): ?>
			<div id='sidebar'>
				<?php echo $sidebar ?>
			</div>
		<?php endif; ?>
	</div>
