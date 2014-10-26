<style type="text/css">

.ns-control {

}


.ns-control.ns-col {
	display: table-row;

}

.ns-control.ns-col > * {
	display: table-cell;
}

.ns-control.ns-col label {
	font-weight: bold;
	font-family: sans-serif;
	min-width: 70px;
}

.ns-textbox {
	width:97%; 
	padding:8px 6px ! important;

}
.ns-group {
	display: table;
	width: 90%;
}

.ns-message-warning {
	line-height: 1.5em;
	margin: 0.5em 0 0.5em 70px;
	padding: 6px;
	background-color: #fcf8e3;
	color: #414141;
	font-size: .9em;
	margin-right: 70px;
}

.ns-message-warning a {
	color: #000;
}

.ns-message-warning .fa-ul li {
margin-bottom: 0.5em;
}


.ns-message-warning p {
	margin-left: 30px;
}

.ns-message-warning h2 {
	margin: 0;
	font-size: 1.5em;
}

.banned-tag {
	background-color:#ef5025  ;
	padding: 2px 6px;
}

</style>
<form method="post" action="?">

	<?= $formDigest ?>
	<?php if( isset($reply_to)): ?>
	<input type="hidden" name="reply_to" value="<?=$reply_to?>" />
	<?php endif; ?>
	<?php if( isset($type)): ?>
	<input type="hidden" name="type" value="<?=$type?>" />
	<?php endif; ?>
	<?php if( isset($id)): ?>
	<input type="hidden" name="id" value="<?=$id?>" />
	<?php endif; ?>
	<div class="ns-group">
		<div class="ns-control ns-col">
			<label>Rubrik:</label>
			<input name="title" value="<?=$title?>" data-helpbox="helpbox-topic" class="ns-textbox" type="text" placeholder="Vad vill du fråga? Försök sätt en tydlig rubrik" />
			<br />&nbsp;
		</div>
		<div class="ns-control ns-col">
			<label>Taggar:</label>
			<input name="tags" type="hidden" value="<?=$tags?>" data-helpbox="helpbox-tags" id="e1">
		</div>
	</div>
		<?php if(isset($tagmessage) && !empty($tagmessage)): ?>
		<div class="ns-message-warning">
				<?=$tagmessage?>
		</div>
			<?php endif; ?>
	
	<textarea class="markdown" name="text" data-helpbox="helpbox-formatting"><?=$text?></textarea>
	<button name="preview" value="yes">Förhandsgrandska</button>
	<button name="submit" value="yes" type="submit" style="margin-left: 30px;font-weight:bold;">Skicka fråga</button>

	<div></div>

</form>