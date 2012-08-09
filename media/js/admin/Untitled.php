<?php
/**
 * Template Name: Main - Inner
 */

get_header(inner); ?>

<div class="container_24 inner-image">
	<img src="<?php 
		if(class_exists('TemplateImages') && mti_image_exists('first-image')) {
	    echo mti_get_image_url('first-image');}
	?>" width="961" height="213"/>
</div>
<div class="clear"></div>

<div class="container_24 inner-content">
	<div class="grid_17 alpha">
		<?php get_template_part( 'loop', 'page' );?>
	</div>
	hello
	<div class="grid_1">&nbsp;</div>
	<div class="grid_6 omega">
		<?php get_sidebar(); ?>
	</div>
	<div class="clear"></div>

</div>
<?php get_footer(main); ?>
