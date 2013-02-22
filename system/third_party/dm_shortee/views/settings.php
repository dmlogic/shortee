<?=form_open($action_url)?>

<h3><?=$this->lang->line('dm_shortee_settings')?></h3>

<p><label for="short_domain"><?=$this->lang->line('dm_short_domain')?></label> (<?=$this->lang->line('dm_short_domain_inst')?>)</p>
<p><input type="text" id="short_domain" name="short_domain" value="<?=$short_domain?>" /></p>

<p><label for="short_domain"><?=$this->lang->line('dm_last_number')?></label></p>
<p><input type="text" id="last_number" name="last_number" value="<?=$last_number?>" /></p>
<p><?=$this->lang->line('dm_last_number_explain')?></p>

<p><input type="submit" class="submit" value="<?=$this->lang->line('dm_save')?>" name="submit"></p>

<?=form_close()?>

<h3><?=$this->lang->line('dm_action_id')?></h3>
<p><?=$this->lang->line('dm_htaccess')?> <strong><?=$action_id?></strong>.</p>