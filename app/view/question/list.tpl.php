<?php if(isset($tag)): ?>
<p><a href="<?=$this->url->create('questions')?>"><span class="fa fa-close"></span> ta bort filter på tag</a></p>
<?php endif; ?>
<div class="tabs-container">
  <ul class="tabs">
    <li class="text">Sortering:</li>
    <li class="last<?= ($this->request->getGet('order') == 'rating' ? ' active':null)?>"><h5><a href="<?=$baseUrl?>?order=rating" title="Visa frågor som fått flest uppröster">Mest uppskattade</a></h5></li>
    <li<?= ($this->request->getGet('order') == 'answers' ? ' class="active"':null)?>><h5><a href="<?=$baseUrl?>?order=answers" title="Visa frågor som saknar svar">Svar</a></h5></li>
    <li<?= ($this->request->getGet('order') == 'activity' ? ' class="active"':null)?>><h5><a href="<?=$baseUrl?>?order=activity" title="Visa frågor som nyligen haft aktivitet">Aktivitet</a></h5></li>
    <li<?= (!$this->request->getGet('order') ? ' class="active"':null)?>><h5><a href="<?=$baseUrl?>" title="Visa frågor som nyligen ställts">Nyaste</a></h5></li>
  </ul>
</div>

<p></p>

<div class="ns-contribution-list" >

<?php foreach( $items as $item): ?>


<div class="row" style="border-bottom: 1px solid #eee; padding-bottom: 1em;padding-top: 1em;">
	<div class="col-8">
		<a class="title" href="<?= $item->calculatedUrl ?>" style="font-size: 1em;"><?=$item->title?></a>
		<div class="ns-item-status">
			<?php if( $item->rating != 0): ?>
				<small title="En indikation på hur uppskattad denna fråga är" class="ns-item-rating<?=$item->rating > 0 ? ' green' : ' orange'?>"><span class="fa fa-caret-<?=$item->rating > 0 ? 'up' : 'down'?>"></span> <?=$item->rating?></small>
			<?php endif; ?>
			<?php if( $item->accepted_answer != 0): ?>
				<small title="Denna fråga är besvarad"><span class="ns-item-answered fa fa-check green"></span></small>
			<?php elseif( $item->closed ): ?>
				<small title="Denna fråga blev stängd utan att få ett korrekt svar"><span class="ns-item-closed fa fa-close"></span></small>
			<?php endif; ?>
			<small><?= $item->calcAnswerCount ?> svar</small>
			<small>aktiv <date datetime="<?= $item->thread_updated ?>" class="js-relative"><?= $item->thread_updated ?></date></small>
		</div>
		<div class="ns-item-tags">
		<?php foreach( $item->tags as $tag): ?>
				<a href="<?=$tag->url?>" class="ns-tag" ><?= $tag->name ?></a>
			<?php endforeach; ?>
		</div>

	</div>
	<div class="col-4">
		<div class="ns-user-block">
			<img src="<?=$item->avatar32?>" />
			<div>
				av <a href="<?=$item->user_url?>"><?=$item->acronym?></a> <?=$item->userActivityBadge?> <?=$item->userReputationBadge?><br />
				<small><date datetime="<?= $item->created ?>" class="js-relative"><?= $item->created ?></date></small>
			</div>
		</div>
	</div>
</div>


<!--
<div style="display: table-row; width: 100%; ">
	<div style="display: table-cell; width: 80px; padding-bottom: 0.85em;text-align:center;border-bottom: 1px solid #eee;">
		<small style="font-size: .9em;"><?= $item->calcAnswerCount ?> svar</small>
		<?php if( $item->accepted_answer != 0): ?>
			<small title="Denna fråga är besvarad"><span class="ns-item-answered fa fa-check green"></span></small>
		<?php elseif( $item->closed ): ?>
			<small title="Denna fråga blev stängd utan att få ett korrekt svar"><span class="ns-item-closed fa fa-close"></span></small>
		<?php endif; ?>
		<?php if( $item->rating != 0): ?>
			<small title="En indikation på hur uppskattad denna fråga är" class="ns-item-rating<?=$item->rating > 0 ? ' green' : ' orange'?>"><span class="fa fa-caret-<?=$item->rating > 0 ? 'up' : 'down'?>"></span> <?=$item->rating?></small>
		<?php endif; ?>
	</div>
	<div style="display: table-cell;border-bottom: 1px solid #eee;padding-bottom: 0.85em;">
		<a class="title" href="<?= $item->calculatedUrl ?>" style="font-size: 1em;"><?=$item->title?></a>
			<?php foreach( $item->tags as $tag): ?>
				<a href="<?=$tag->url?>" class="ns-tag" style="font-size: .75em;"><?= $tag->name ?></a>
			<?php endforeach; ?>
		< !- -<p style="margin: 0; padding: 0; font-size: .85em"><?= $item->excerpt ?></p>- - >
	</div>		

	<div>
			<a href="<?=$this->url->create('users/view/' . $item->acronym)?>"><?=$item->name ? $item->name : $item->acronym?> <img src="<?=$item->avatar?>" width="16" height="16" alt="avatar"/></a>
			<date style=" font-size: .85em; color: #888" datetime="<?= $item->created ?>" class="js-relative"><?= $item->created ?></date>
			<br /> aktiv <date style=" font-size: .85em; color: #888" datetime="<?= $item->thread_updated ?>" class="js-relative"><?= $item->thread_updated ?></date>
	</div>
	</div>
-->




<!--
<div class="ns-contribution-item">
	<div class="row">
	<div class="ns-panel">
			<img src="<?= $item->avatar; ?>" />
	</div>
	<div class="ns-content">
		<a class="title" href="<?= $item->calculatedUrl ?>"><?=$item->title?></a>
		
		<p><?= $item->excerpt ?></p>
		av <a href=""><?= $item->acronym ?></a> <date datetime="<?= $item->created ?>" class="js-relative"><?= $item->created ?></date>
	</div>
	</div>
	<div class="row">
		<div class="ns-footer">
			<?php foreach( $item->tags as $tag): ?>
				<a href="<?=$tag->url?>" class="ns-tag"><?= $tag->name ?></a>
			<?php endforeach; ?>
		</div>
	</div>
</div>-->

<?php endforeach; ?>
</div>