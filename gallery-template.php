<?php $options = get_option('post-slideshow-options'); ?>




<div id="galleria-p<?php echo get_the_ID(); ?>">
</div> 

<style> 
<!--
#galleria-p<?php echo get_the_ID(); ?>{
	height:<?php echo $options['height']; ?>;
	width:<?php echo $options['width']; ?>;

}
-->
</style>

<script> 


data = <?php echo json_encode($images); ?>;

Galleria.loadTheme('wp-content/plugins/post-slideshow-gallery/galleria/themes/classic/galleria.classic.js');

// Initialize Galleria
jQuery('#galleria-p<?php echo get_the_ID(); ?>').galleria({
	imageCrop: false,
	dataSource: data,
	transition: 'fadeslide',
	debug:false,
});

</script> 