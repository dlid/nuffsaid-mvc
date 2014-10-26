<div class="ns-contribution-item">
	<div class="row">
		<h2 class="ns-header"><?= $title ?></h2>
	</div>
	<div class="row">
		<div class="ns-footer">
			fråga från <a href="a"><img src="<?=$avatar?>" width="16" height="16" style="vertical-align:middle;" alt="gravatar image" /> <?= $username ?></a> <date datetime="<?=$created?>" class="js-relative"><?=$created?></date>
		</div>
	</div>
	<div class="row">
		<div class="ns-panel">
			<p class="compact center" style="color: #666"><span class="fa fa-thumbs-up fa-2x"></span></p>
			<p class="compact center" style="font-size: 2em;font-weight:bold;color: #666">0</p>
			<p class="compact center" style="color: #666"><span class="fa fa-thumbs-down fa-2x"></span></p>
		</div>
		<div class="ns-content">
			<?= $text ?>
		</div>
	</div>

	<?php if(isset($tags)): ?>
		<div class="row">
			<div class="ns-footer" >
				<span class="fa fa-tags"></span>
				<?php foreach( $tags as $tag): ?>
					<?php if(!isset($tag->url) || isset($preview)): ?>
						<span class="ns-tag<?= !is_numeric($tag->id) ? ' ns-tag-new' : '' ?>"><?= !is_numeric($tag->id) ? '<span class="fa fa-plus" style="color:green;cursor:help" title="Den här taggen kommer att skapas"></span> ' : '' ?><?= htmlentities($tag->name, null, 'utf-8') ?></span>
					<?php else: ?>
						<a href="<?=$tag->url?>" class="ns-tag"><?= htmlentities($tag->name, null, 'utf-8') ?></a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
	</div>	
	<?php endif; ?>
<?php if( !isset($preview)): ?>	
		<div class="row">
			<div class="ns-footer" style="margin-left:80px;margin-top:10px">
				<img width="24" height="24" alt="avatar" style="vertical-align:top; margin-top: 2px;" src="<?= $currentuser_avatar ?>">
				<input type="text" placeholder="Skriv din kommentar här" style="width: 80%; color:#333; padding:4px;" />
				<p style="margin:4px 0; line-height: 1.5em; font-size: .85em">Använd kommentarer om du behöver mer information för att kunna ge ett svar.<br />
				Du kan använda Markdown - men håll det kortfattat!</p>
			</div>
		</div>
<?php endif; ?>	
</div>
