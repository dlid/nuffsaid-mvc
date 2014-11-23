	<div class="start-splash">
		<div class="splash-heading cyan">
			<h2><?=isset($title) ? $title : 'Användaraktiviteter'?></h2>
		</div>
		<div class="splash-content cyan">
			<ul class="fa-ul">
			<?php foreach($userActivities as $activity): ?>
				<li style="font-size: .85em;border-bottom: 1px solid #eee; padding: 2px;">
					<span class="fa fa-li <?=$activity->icon?>"></span>
					<a href="<?=$this->url->create('users/view/' . $activity->acronym)?>"><?=$activity->username?></a><?=$activity->usernameSufix?>
				<?=$activity->description?>
				<?php if($activity->activity_score != 0): ?>
					<small title="Aktivitetspoäng indikerar hur aktiv en användare är" class="activity-change"><span class="fa fa-tachometer"></span>
					<?=$activity->activity_score > 0 ? '+' : '' ?><?=$activity->activity_score ?></small>
				<?php endif; ?>

				<?php if($activity->reputation_score != 0): ?>
					<small title="Ryktespoäng indikerar hur uppskattad en användare är" class="reputation-change <?=$activity->reputation_score > 0 ? 'green' : 'red'?>"><span class="fa fa-star<?=$activity->reputation_score < 0 ? '-o' : '' ?>"></span>
					<?=$activity->reputation_score > 0 ? '+' : '' ?>
					<?=$activity->reputation_score ?></small>
				<?php endif; ?>
				<a href="<?=$activity->link?>" title="Visa inlägg" class="fa-arrow-right fa"></a>
				</li>
			<?php endforeach; ?>
			<!--				<li><span class="fa-li fa fa-comment-o"></span> <a href="">david</a> kommenterade i <a href="">Destiny och nätverket</a></li>
				<li><span class="fa-li fa fa-question green"></span> <a href="">david</a> ställde frågan <a href="">Glöm inte</a></li>
				<li><span class="fa-li fa fa-question green"></span> <a href="">Test testsson</a> svarade på <a href="">Glöm inte</a></li>-->
			</ul>
		</div>
	</div>