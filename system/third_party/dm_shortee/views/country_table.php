<? if(isset($rows) && is_array($rows) && count($rows) ) : ?>

<table cellspacing="0" cellpadding="0" border="0" class="mainTable">
	<thead>
		<tr>
			<th><?=lang('dm_country')?></th>
			<th width="200"><?=lang('dm_views')?></th>
		</tr>
	</thead>
	<tbody>
		<?for($i = 0 ; $i < count($rows) ; $i++) :
			$class = ($i%2) ? 'even' : 'odd';
		?>
		<tr class="<?=$class?>">
			<td><?=$countries[$rows[$i]['country_code']]?></td>
			<td><?=$rows[$i]['views']?></td>
		</tr>
		<? endfor; ?>
	</tbody>
</table>

<? else :?>
<p><?=lang('dm_no_views')?></p>
<? endif;?>