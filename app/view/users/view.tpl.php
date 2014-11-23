<h1><?=htmlentities($item->name ? $item->name : $item->acronym, null, 'utf-8')?></h1>

<?php if( $item->user_id == $this->userContext->getUserId()): ?>
	<p><a href="<?=$this->url->create('users/update')?>"><span class="fa fa-edit"></span> redigera din profil</a></p>
<?php endif; ?>

<div class="row">
	<div class="col-4">
			<div class="ns-user-block">
			<img src="<?=$item->avatar32?>" />
			<div>
				<a href="<?=$item->user_url?>"><?=$item->acronym?></a> <?=$item->userActivityBadge?> <?=$item->userReputationBadge?><br />
				<small>blev medlem <date datetime="<?= $item->created ?>" class="js-relative"><?= $item->created ?></date></small>
			</div>
		</div>
	</div>
</div>