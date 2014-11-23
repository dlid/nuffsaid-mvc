<div id="c<?=$id?>" class="ns-contribution-item ns-type-<?=strtolower($type)?>">
<?php if( $type == 'QUERY' ): ?>
	<div class="row">
		<h2 class="ns-header"><?= $title ?></h2>
	</div>
<?php endif; ?>
	<div class="row">
		<div class="ns-panel">
			<?php if(isset($upvote) && $upvote): ?>
				<p class="compact center up"><a title="Rösta upp <?=$type == 'QUERY' ? 'välställda frågor som du tycker innehåller bra information' : 'svar som tillför något värdefullt och relevant till frågeställningen'?>" href="<?= $this->di->url->create('questions/upvote/' . $id) ?>" class="fa fa-caret-up fa-2x"></a></p>
			<?php elseif( isset($upvoted) && $upvoted): ?>
				<p class="compact center up"><a title="Du har röstat upp den här. Klicka för att ångra." href="<?= $this->di->url->create('questions/upvote/' . $id . '/undo') ?>" class="fa fa-caret-up fa-2x green"></a></p>
			<?php else: ?>
				<p class="compact center up"><span class="fa fa-caret-up fa-2x disabled"></span></p>
			<?php endif; ?>
			<p class="compact center rating<?= (isset($rating) ? ($rating < 0? ' orange' : ($rating != 0 ? ' green' : '')) : '') ?>"><?= isset($rating) ? $rating : 0 ?></p>
			<?php if(isset($downvote) && $downvote): ?>
				<p class="compact center down"><a title="Rösta ned <?=$type == 'QUERY' ? 'frågor som är dåligt ställda, saknar viktig information eller handlar om fel saker' : 'svar som inte tillför något till konversationen'?>" href="<?= $this->di->url->create('questions/downvote/' . $id) ?>" class="fa fa-caret-down fa-2x"></a></p>
			<?php elseif( isset($downvoted) && $downvoted): ?>
				<p class="compact center up"><a title="Du har röstat ned den här. Klicka för att ångra." href="<?= $this->di->url->create('questions/downvote/' . $id . '/undo') ?>" class="fa fa-caret-down fa-2x orange"></a></p>
			<?php else: ?>
				<p class="compact center down"><span class="fa fa-caret-down fa-2x disabled"></span></p>
			<?php endif; ?>

			<?php if(isset($accept) && $accept && !$accepted): ?>			
			<p class="compact center accept"><a title="" href="<?= $this->di->url->create('questions/accept/' . $id) ?>" class="fa fa-check"></a></p>
			<?php elseif(isset($accept) && $accept && $accepted): ?>
			<p class="compact center accept"><a title="" href="<?= $this->di->url->create('questions/accept/' . $id . '/undo') ?>" class="fa fa-check green"></a></p>
			<?php elseif($accepted): ?>
			<p class="compact center accept"><span title="Det här är det accepterade svaret" class="fa fa-check green"></span></p>
			<?php endif; ?>	

			<?php if(isset($close) && $close && !$closed): ?>
			<p class="compact center close"><a title="Stäng frågan utan att acceptera ett svar" href="<?= $this->di->url->create('questions/close/' . $id) ?>" class="fa fa-close red"></a></p>
			<?php elseif(isset($close) && $close && $closed): ?>
			<p class="compact center close"><a title="Ångra stängningen av frågan" href="<?= $this->di->url->create('questions/close/' . $id . '/undo') ?>" class="fa fa-undo"><span class="fa fa-close"></span></a></p>
			<?php endif; ?>
	
		</div>
		<div class="ns-content">
				<div class="ns-user-block" style="float:right;">
			<img src="<?=$item->avatar32?>" />
			<div>
				av <a href="<?=$item->user_url?>"><?=$item->acronym?></a> <?=$item->userActivityBadge?> <?=$item->userReputationBadge?><br />
				<small><date datetime="<?= $item->created ?>" class="js-relative"><?= $item->created ?></date></small>
			</div>
		</div>
			<?= $text ?>
		</div>
	</div>

	<?php if(isset($tags) && $type == 'QUERY'): ?>
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
				<?php if(isset($commentform)):  ?>
					<?= $commentform ?>
				<?php endif; ?>
				<?php if(isset($comments)):  ?>
					<div class="ns-comment-list">
					<?php foreach($comments as $comment): ?>
						<div id="c<?=$comment->id?>" class="ns-comment">
							<img src="<?=$comment->avatar?>" /> 
							<?= str_replace('</p>', '', str_replace('<p>', '', $this->filter->doFilter($comment->body, 'markdown')))  ?>
							<span class="ns-comment-meta">
							- <?=$comment->acronym?>
							<date datetime="<?=$comment->created?>" class="js-relative"><?=$comment->created?></date>

							</span>
						</div>
					<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
<?php endif; ?>	
</div>
