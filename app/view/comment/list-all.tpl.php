<h1><?=$title?></h1>
 
<?php foreach ($items as $comment) : ?>
 
<pre><?=var_dump($comment->getProperties())?></pre>
 
<?php endforeach; ?>
 
<p><a href='<?=$this->url->create('')?>'>Home</a></p>