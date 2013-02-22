<?=form_open($action_url)?>

<div id="shortee_form">

<h3><?=$this->lang->line('dm_long_url')?></h3>

<div id="url_wrapper">

	<p id="long_url_enter"><input type="text" id="long_url" name="long_url" class="shortee-input" value="<?=$long_url?>" /></p>

	<p class="offscreen" id="long_url_pages">

		<select class="shortee-input" >
			<option value="0">- <?=$this->lang->line('dm_select_page')?> -</option>
			<?foreach($pages_urls as $url) : ?>
			<option value="<?=$url?>"><?=$url?></option>
			<? endforeach;?>
		</select>
	</p>

	<p class="offscreen" id="long_url_template">

		<select id="template_groups" class="shortee-input" >
			<option value="0">- <?=$this->lang->line('dm_select_template_group')?> -</option>
			<?foreach($template_groups as $id => $group) : ?>
			<option value="<?=$id?>"><?=$group[0]?></option>
			<? endforeach;?>
		</select>

		<select id="templates" class="shortee-input" >
			<option value="0">- <?=$this->lang->line('dm_select_template')?> -</option>
		</select>

	</p>

</div>

<ul id="long_url_choices">
	<li id="luc_enter" class="current"><?=$this->lang->line('dm_enter')?></li>
	<li id="luc_template"><?=$this->lang->line('dm_template')?></li>
	<li id="luc_pages"><?=$this->lang->line('dm_page')?></li>
</ul>


<h3><?=$this->lang->line('dm_short_url')?></h3>

<p>

	<? if(count($domains) < 2 ) : ?>
	<?=$domains[0]?>
	<input type="hidden" name="short_domain" id="short_domain" value="<?=$domains[0]?>" />
	<? else: ?>
	<select name="short_domain" id="short_domain" class="shortee-input">
		<?foreach($domains as $dom) :?>
		<option value="<?=$dom?>"><?=$dom?></option>
		<? endforeach;?>
	</select>
	<? endif; ?>
	/
	<input type="text" id="short_url" name="short_url" class="shortee-input" max_length="20" /> <button type="button" class="submit shortee-button"  id="generate"><?=$this->lang->line('dm_suggest')?></button> </p>

<p><button type="button" class="submit shortee-button" id="shortee_submit"><?=$this->lang->line('dm_save_url')?></button></p>

<?=form_close()?>

</div>

<div id="shortee_feedback">

	<h3>message</h3>

	<div id="final_url">
		<p><label for="full_short_url"><?=$this->lang->line('dm_url_is')?></label></p>
		<p><input type="text" id="full_short_url" class="shortee-input" /></p>
		<p><?=$this->lang->line('dm_test_long')?>: <a class="shortee-link" id="long_link" href="#">link</a></p>
		<p><?=$this->lang->line('dm_test_short')?>: <a class="shortee-link"  id="short_link" href="#">link</a></p>
		<p>QR code: <a class="dm_fancy" id="qr_view" href="<?=$qrlink?>">View</a> | <a id="qr_dl" href="<?=$qrlink?>">Download</a></p>
	</div>

	<p><button type="button" class="submit shortee-button" id="shortee_return"><?=$this->lang->line('dm_return')?></button></p>

</div>