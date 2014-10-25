	<div id="comments" class='comments'>
<?php if (is_array($comments) && count($comments) > 0) : ?>
<hr>
	<h1><?= count($comments) . " kommentar" . (count($comments)!=1 ? "er" : "") ?></h1>
	<?php foreach ($comments as $obj) : 
	$comment = $obj->getProperties();
		
	?>
		<div id="comment<?= $comment['id'] ?>" class="comment">
			<div class="pull-left">
				<img src="http://www.gravatar.com/avatar/<?=md5($comment['email']);?>.jpg?s=40"  />
			</div>
			<div class="comment-body">
				<div>
					<a href="<?= $this->url->create('comment/edit/' . $comment['id']) ?>" class="id">#<?=$comment['id']?></a>
					<span class="author"><?=htmlentities($comment['name'], null, 'utf-8')?></span>
					<time datetime="<?= date("Y-m-d H:i", $comment['created']); ?>">- <?= $this->comments->getTimeAgo($comment['created']) ?></time>
					<?php if( $comment['web'] ): ?>
							<a href="<?=$comment['web']?>" class="web"><?=$comment['web']?></a>
					<?php endif; ?>
					(<a class="remove-comment" href="<?= $this->url->create('comment/remove/' . $comment['id']) ?>" title="Radera kommentar">ta bort</a>)
					<?php if( isset($all) ): ?>
					<p class="commented-page">för sidan: <a href="<?=$comment['url'] ?>"><?=$comment['url'] ?></a></p>
					<?php endif; ?>
				</div>
				<p><?=$comment['comment']?></p>
			</div>	
		</div>
	<?php endforeach; ?>

<?php endif; ?>
</div>