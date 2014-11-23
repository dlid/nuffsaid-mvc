<h1>Användare</h1>
<p>Här kan du söka reda på användare</p>

<hr />

<form action="?" method="get">
	Sök efter användare: <input type="text" name="q" value="<?=$query?>" /> <button>Sök</button>
</form>

<hr />

<div class="row">
<?php foreach($users as $item): ?>

	<div class="col-4">
			<div class="ns-user-block">
			<img src="<?=$item->avatar32?>" />
			<div>
				av <a href="<?=$item->user_url?>"><?=$item->acronym?></a> <?=$item->userActivityBadge?> <?=$item->userReputationBadge?><br />
				<small><date datetime="<?= $item->created ?>" class="js-relative"><?= $item->created ?></date></small>
			</div>
		</div>
	</div>


<?php endforeach; ?>
</div>