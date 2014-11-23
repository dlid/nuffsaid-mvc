<?php if($yourOpenQuestions): ?>
	<div class="start-splash">
		<div class="splash-heading darkblue">
			<h2><?=isset($title) ? $title : 'Dina öppna frågor'?></h2>
		</div>
		<div class="splash-content darkblue">
			<ul class="fa-ul">
			<?php foreach($yourOpenQuestions as $question): ?>
				<li>

					<span class="fa-li fa fa-question green"></span> 
					<?php if(!isset($skipavatar)): ?>
						<img src="<?=$question->avatar?>" width="16" height="16" alt="avatar"/>
					<?php endif; ?>
					<a href="<?=$question->calculatedUrl?>"><?= $question->title ?></a> 
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