		<div id="filterMenu">
			<fieldset>
				<legend>Search URLs</legend>

			<?=form_open($filter_form_path, array('name'=>'filterform', 'id'=>'filterform'))?>

				<div class="group">
					<?=form_dropdown('date_range', $date_select_options, '', 'id="date_range"').NBS.NBS?>
					<?php
						// JS required theme, so ordering handled by table sorter
						//form_dropdown('order', $order_select_options, $order_selected, 'id="f_select_options"').NBS.NBS
					?>
					<?=form_dropdown('perpage', $perpage_select_options, $perpage, 'id="f_perpage"')?>
				</div>

				<div>
					<?=lang('keywords', 'keywords')?> <?=form_input($keywords, NULL,  'class="field shun" id="keywords"')?><br />
					<button id="search_button" type="button" class="submit">Search</button>
				</div>

			<?=form_close()?>
			</fieldset>
			</div>

			<?php
				$this->table->set_template($cp_table_template);
				$this->table->set_heading($table_headings);

				echo $this->table->generate($entries);
			?>

			<div id="shortee-pagination"><?=$pagination?></div>