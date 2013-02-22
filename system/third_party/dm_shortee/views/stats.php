<?=form_open($action_url, array('name'=>'filterform', 'id'=>'filterform'))?>
<fieldset style="margin-bottom:10px">
<h3><a href="<?=$short_url?>"><?=$short_url?></a></h3>
<h3><?=$this->lang->line('dm_total_views')?>: <?=$views?></h3>
<p><?=$this->lang->line('dm_long_url')?>: <a href="<?=$url?>"><?=$url?></a></p>
<p>QR code: <a class="dm_fancy" href="<?=$qrlink?>">View</a> | <a href="<?=$qrlink?>&download=true">Download</a></p>
<p><?=$this->lang->line('dm_link_created_on')?> <?=$date_added?></p>

<p>
	<label><?=lang('dm_show_for')?>:
	<?=$traffic_select?>
	</label>
</p>
</fieldset>

<fieldset style="margin-bottom:10px">
	<p><button type="submit" class="submit">Download statistics</button></p>
</fieldset>

<fieldset style="margin-bottom:10px">
<h3><?=$this->lang->line('dm_traffic_by_date')?></h3>

<div id="traffic_table">
	<?=$traffic_table?>
</div>
</fieldset>

<fieldset>
<h3><?=lang('dm_traffic_by_location')?></h3>

<div id="country_table">
	<?=$country_table?>
</div>
</fieldset>
<?=form_close()?>

<script type="text/javascript">
$(function(){
	$("#traffic_select").bind("change keyup",function(){
		$.get(
			$('#filterform').attr("action"),
			{
				date_range : $("#traffic_select").val()
			},
			function(data) {
				$("#traffic_table").html(data.traffic_table);
				$("#country_table").html(data.country_table);
			},
			'json'
		);
	})
})
</script>