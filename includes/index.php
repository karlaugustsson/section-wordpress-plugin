
<?php 
global $ka_section;
global $ka_pages;
if($_GET['order_by_section'] == null):
$ka_result_section = $ka_section->getSections();
else:
	$ka_result_section = $ka_pages->getSectionsByPagePostname($_GET['order_by_section']);

endif?>

	<div class="wrap">
	<?php print_add_new_post_button();?>
	<?php if($_GET['order_by_section'] != null): ?>

	<?php print_back_link()?>

	<?php  endif;?>
	<table class="wp-list-table widefat fixed striped posts">
		<thead>
			<tr>
				<td>Name of section</td>
				<?php if($_GET['order_by_section'] == null): ?>
				<td>Visible on</td>
				<?php else:?>
				<td>Result for page:</td>
				<?php endif?>
			</tr>
		</thead>
		<tbody>
			<tr>
				<?php foreach ($ka_result_section as $section): ?>
					
					<tr>
						<td>
							<?php ka_print_edit_and_remove_post_link($section)?>
							
						</td>
						<?php if($_GET['order_by_section'] == null): ?>

							<td><?php ka_print_section_pages($section)?></td>

						<?else:?>

							<td><?php print $ka_pages->get_page_title_by_post_name($_GET['order_by_section']) ?></td>
						
						<?php endif?>
						
					</tr>

			<?php endforeach ?>
			</tr>
		</tbody>
	</table>		
	</div>

