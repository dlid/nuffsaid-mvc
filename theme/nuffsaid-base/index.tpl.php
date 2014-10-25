<!doctype html>
<html class='no-js' lang='<?=$lang?>'>
<head>
<meta charset='utf-8'/>
<meta name="viewport" content="width=device-width, initial-scale=1,maximum-scale=1, user-scalable=no">
<title><?=$title . $title_append?></title>
<?php if(isset($favicon)): ?><link rel='icon' href='<?=$this->url->asset($favicon)?>'/><?php endif; ?>
<?php foreach($stylesheets as $stylesheet): ?>
<link rel='stylesheet' type='text/css' href='<?=$this->url->asset($stylesheet)?>'/>
<?php endforeach; ?>
<?php if(isset($style)): ?><style><?=$style?></style><?php endif; ?>
<script src='<?=$this->url->asset($modernizr)?>'></script>
</head>

<body>

<nav id="toolbar">	
	<div class="row">
		mg
	</div>
</nav>

<div id='wrapper'>
	
	<header id="header">
		<div class="col-logo">
				<?php if(isset($header)) echo $header?>
				<?php $this->views->render('header')?>
		</div>
		<div class="col-navbar">
				<?php if ($this->views->hasContent('navbar')) : ?>
				<?php $this->views->render('navbar')?>
				<?php endif; ?>
		</div>
	</header>


	<?php if ($this->views->hasContent('flash-info') || $this->views->hasContent('flash-success') || $this->views->hasContent('flash-warning') || $this->views->hasContent('flash-danger')) : ?>
		<div id="flash">
			<?php if ($this->views->hasContent('flash-info')) : ?>
			<div class="row content-flash flash-info">
				<div><?php $this->views->render('flash-info')?></div>
			</div>
			<?php endif; ?>

			<?php if ($this->views->hasContent('flash-success')) : ?>
			<div class="row content-flash flash-success">
				<div ><?php $this->views->render('flash-success')?></div>
			</div>
			<?php endif; ?>

			<?php if ($this->views->hasContent('flash-warning')) : ?>
			<div class="row content-flash flash-warning">
				<div ><?php $this->views->render('flash-warning')?></div>
			</div>
			<?php endif; ?>

			<?php if ($this->views->hasContent('flash-danger')) : ?>
			<div class="row content-flash flash-danger">
				<div><?php $this->views->render('flash-danger')?></div>
			</div>
			<?php endif; ?>
		</div>
	<?php endif; ?>


	


	<?php if ($this->views->hasContent('featured-1', 'featured-2', 'featured-3')) : ?>
	<div class="row content-block">
	    <div id='triptych-1'><?php $this->views->render('featured-1')?></div>
	    <div id='featured-2'><?php $this->views->render('featured-2')?></div>
	    <div id='featured-3'><?php $this->views->render('featured-3')?></div>
	</div>
	<?php endif; ?>


	<?php if ($this->views->hasContent('main-header')) : ?>
		<div class="row content-block content-main">
			<div class="col-wide">
				<?php $this->views->render('main-header')?>
			</div>
		</div>
	<?php endif; ?>


	<div class="row content-block content-main">
		<div id='main'<?= !$this->views->hasContent('sidebar') ? ' class="wide"' : null; ?>>
		<?php if(isset($main)) echo $main?>
			<?php $this->views->render('main')?>
		</div>
		<?php if($this->views->hasContent('sidebar')): ?>
			<div id='sidebar'>
			<?php $this->views->render('sidebar')?>
			</div>
		<?php endif; ?>
	</div>

	<?php if ($this->views->hasContent('main-footer')) : ?>
		<div class="row content-block content-main">
			<div class="col-wide">
				<?php $this->views->render('main-footer')?>
			</div>
		</div>
	<?php endif; ?>



</div>

	<footer id='footer'>
		<?php if ($this->views->hasContent('footer-col-1', 'footer-col-2', 'footer-col-3','footer-col-4')) : ?>
		<div  class="wrapper">
			<div class="row">
				<div id='footer-col-1'><?php $this->views->render('footer-col-1')?></div>
				<div id='footer-col-2'><?php $this->views->render('footer-col-2')?></div>
				<div id='footer-col-3'><?php $this->views->render('footer-col-3')?></div>
				<div id='footer-col-4'><?php $this->views->render('footer-col-4')?></div>
			</div>	
		</div>
		<?php endif; ?>
		<div class="bottom wrapper">
			<div class="row">
				<div class="col-wide">
					<?php if(isset($footer)) echo $footer?>
					<?php $this->views->render('footer')?>
				</div>
			</div>	
		</div>
	</footer>	

<?php if(isset($jquery)):?><script src='<?=$this->url->asset($jquery)?>'></script><?php endif; ?>

<?php if(isset($javascript_include)): foreach($javascript_include as $val): ?>
<script src='<?=$this->url->asset($val)?>'></script>
<?php endforeach; endif; ?>

<?php if(isset($google_analytics)): ?>
<script>
  var _gaq=[['_setAccount','<?=$google_analytics?>'],['_trackPageview']];
  (function(d,t){var g=d.createElement(t),s=d.getElementsByTagName(t)[0];
  g.src=('https:'==location.protocol?'//ssl':'//www')+'.google-analytics.com/ga.js';
  s.parentNode.insertBefore(g,s)}(document,'script'));
</script>
<?php endif; ?>

</body>
</html>
