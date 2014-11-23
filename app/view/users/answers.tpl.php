<?php if($recentlyAnswered): ?>
	<div class="start-splash">
		<div class="splash-heading green">
			<h2><?=isset($title) ? $title : 'Besvarade frågor'?></h2>
		</div>
		<div class="splash-content darkblue">
			<ul class="fa-ul">
			<?php foreach($recentlyAnswered as $question): ?>
				<li>

					<span class="fa-li fa fa-mail-reply"></span> 
					<?php if(!isset($skipavatar)): ?>
						<img src="<?=$question->avatar?>" width="16" height="16" alt="avatar"/>
					<?php endif; ?>
					<a href="<?=$question->calculatedUrl?>#c<?=$question->answer_id?>"><?= $question->title ?></a> 
					<small>- aktiv
						<date datetime="<?=$question->thread_updated?>" class="js-relative"><?=$question->thread_updated?></date>
					</small>	
				</li>
			<?php endforeach; ?>
			</ul>
		</div>
	</div>

	<hr />
<?php endif; ?>