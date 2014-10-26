
<div class="tabs-container">
    <ul class="tabs">
      <li class="text">yo yo</li>
      <li class="last"><h5><a href="">Mest uppskattade</a></h5></li>
      <li><h5><a href="">Aktiva</a></h5></li>
      <li class="active"><h5><a href="">Nyaste</a></h5></li>
    </ul>
  </div>

<p></p>

<div class="ns-contribution-list">
<?php foreach( $items as $item): ?>
<div class="ns-contribution-item">
	<div class="row">
	<div class="ns-panel">
			<?= $item->acronym ?>
	</div>
	<div class="ns-content">
		<a class="title" href="<?= $item->calculatedUrl ?>"><?=$item->title?></a>
		<date datetime="<?= $item->created ?>" class="js-relative"><?= $item->created ?></date>
	</div>
	</div>
	<div class="row">
		<div class="ns-footer">
			<?php foreach( $item->tags as $tag): ?>
				<a href="<?=$tag->url?>" class="ns-tag"><?= $tag->name ?></a>
			<?php endforeach; ?>
		</div>
	</div>
</div>
<?php endforeach; ?>
</div>