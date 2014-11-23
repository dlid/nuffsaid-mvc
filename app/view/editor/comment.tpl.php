<form id="<?=$form_id?>" method="post" action="<?= isset($form_action) ? $form_action : '?'; ?>">

	<?= $formDigest ?>
	<input type="hidden" name="reply_to" value="<?=$reply_to?>" />
	<input type="hidden" name="is_comment" value="true" />
	<input type="hidden" name="type" value="<?=$type?>" />
	<input type="hidden" name="form" value="<?=$form?>" />
	
	<!--<a href=""><span class="fa fa-comment-o"></span> Add comment</a>-->
	
	<input type="text" name="text" required size="60" placeholder="Skriv en kommentar"  />
	<button name="submit" value="yes" type="submit">Skicka</button>


</form>