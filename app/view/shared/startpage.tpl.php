<?php if(isset($tags)): ?>
	
<?php endif; ?>

<div>

<?php if($yourOpenQuestions): ?>
	<div class="start-splash">
		<div class="splash-heading darkblue">
			<h2>Dina öppna frågor</h2>
		</div>
		<div class="splash-content darkblue">
			<ul class="fa-ul">
			<?php foreach($yourOpenQuestions as $question): ?>
				<li>

					<span class="fa-li fa fa-question green"></span> 
					<img src="<?=$question->avatar?>" width="16" height="16" alt="avatar"/>
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
	<div class="start-splash">
		<div class="splash-heading green">
			<h2>Senaste frågorna</h2>
		</div>
		<div class="splash-content green">
			<ul class="fa-ul">
			<?php foreach($recentQuestions as $question): ?>
				<li><span class="fa-li fa fa-question green"></span>
				<img src="<?=$question->avatar?>" width="16" height="16" alt="avatar"/>
				<a href="<?=$question->calculatedUrl?>"><?= $question->title ?></a> 
					<small>- 
						<date datetime="<?=$question->created?>" class="js-relative"><?=$question->created?></date>
					</small>	
					</li>
			<?php endforeach; ?>
			</ul>
		</div>
	</div>


	<div class="start-splash">
		<div class="splash-heading blue">
			<h2>Populära taggar</h2>
		</div>
		<div class="splash-content blue">
			<div class="tag-cloud">
				<?php foreach($tags as $tag): ?>
					<a href="<?=$this->url->create('questions/tagged/' . $tag->slug)?>" style="font-size: <?=$tag->heat?>px;"><?= $tag->name ?></a></li>	
				<?php endforeach; ?>
			</div>
		</div>
	</div>


	<div class="start-splash">
		<div class="splash-heading red">
			<h2>Aktiva trådar</h2>
		</div>
		<div class="splash-content red">
			<ul class="fa-ul">
			<?php foreach($activeThreads as $question): ?>
				<li><span class="fa-li fa fa-fire red"></span> <a href="<?=$question->calculatedUrl?>"><?= $question->title ?></a> 
					<small>- aktiv 
						<date datetime="<?=$question->thread_updated?>" class="js-relative"><?=$question->thread_updated?></date>
					</small>	
					</li>
			<?php endforeach; ?>
			</ul>
		</div>
	</div>


	<div class="start-splash">
		<div class="splash-heading orange">
			<h2>Saknar korrekt svar</h2>
		</div>
		<div class="splash-content orange">
			<ul class="fa-ul">
			<?php foreach($questionsWithoutCorrectAnswer as $question): ?>
				<li>
					<span class="fa-li fa fa-frown-o orange"></span> <a href="<?=$question->calculatedUrl?>"><?= $question->title ?></a> 
					<small>- 
						<date datetime="<?=$question->created?>" class="js-relative"><?=$question->created?></date>
					</small>	
				</li>
			<?php endforeach; ?>
			</ul>
		</div>
	</div>

</div>
