<div class="grid_12 alpha">
	
	<p class="pagination">
	
		<?php if ($first_page !== FALSE): ?>
			<a href="<?php echo $page->url($first_page) ?>"><?php echo __('First') ?></a>
		<?php else: ?>
			<?php echo __('First') ?>
		<?php endif ?>
	
		<?php if ($previous_page !== FALSE): ?>
			<a href="<?php echo $page->url($previous_page) ?>"><?php echo __('Previous') ?></a>
		<?php else: ?>
			<?php echo __('Previous') ?>
		<?php endif ?>
	
		<?php for ($i = 1; $i <= $total_pages; $i++): ?>
	
			<?php if ($i == $current_page): ?>
				<strong>[<?php echo $i ?>]</strong>
			<?php else: ?>
				<a href="<?php echo $page->url($i) ?>"><?php echo $i ?></a>
			<?php endif ?>
	
		<?php endfor ?>
	
		<?php if ($next_page !== FALSE): ?>
			<a href="<?php echo $page->url($next_page) ?>"><?php echo __('Next') ?></a>
		<?php else: ?>
			<?php echo __('Next') ?>
		<?php endif ?>
	
		<?php if ($last_page !== FALSE): ?>
			<a href="<?php echo $page->url($last_page) ?>"><?php echo __('Last') ?></a>
		<?php else: ?>
			<?php echo __('Last') ?>
		<?php endif ?>
	
	</p><!-- .pagination -->
	
</div>

<div class="grid_4 omega tr">

	<?php 
		$options = Kohana::config('ecommerce.admin_list_options');
		$selected = Session::instance()->get('admin_list_option', Kohana::config('ecommerce.default_admin_list_option'));
	?>
	
	Showing
	<select id="admin-items-per-page">
	<?php foreach ($options as $option): ?>
		<option value="<?php echo $option; ?>" <?php if ($option == $selected) echo 'selected'; ?>><?php echo $option ?></option>
	<?php endforeach; ?>	
	</select>
	items per page

</div>

<div class="clear"></div>