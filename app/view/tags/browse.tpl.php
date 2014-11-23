<h1>Tags</h1>
<p>Här kan du söka reda på taggar som används</p>

<hr />

<form action="?" method="get">
	Sök efter taggar: <input type="text" name="q" value="<?=$query?>" /> <button>Sök</button>
</form>

<hr />

<?php foreach($tags as $tag): ?>
	<span class="ns-tag-block">
		<a href="<?=$this->url->create('questions/tagged/' . $tag->slug)?>" class="ns-tag"><?=htmlentities($tag->name, null, 'utf-8')?></a> (<?=$tag->count ?>)
	</span>
<?php endforeach; ?>