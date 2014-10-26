
<?php if(isset($info) && $info): ?>
<div class="content-flash flash-info">
	<?= $info; ?>
</div>
<?php endif; ?>

<?php if(isset($danger) && $danger): ?>
<div class="content-flash flash-danger">
	<?= $danger; ?>
</div>
<?php endif; ?>

<?php if(isset($warning) && $warning): ?>
<div class="content-flash flash-warning">
	<?= $warning; ?>
</div>
<?php endif; ?>