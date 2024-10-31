<?php
/*
Plugin Name: RATCAT Triple Slider
Plugin URI: http://teamratcat.biz/ratcat-triple-slider
Description: RATCAT Triple Panel Slider is jQuery image slider with a 3D look and swipe-like transitions.
Author: Team RATCAT
Author URI: http://teamratcat.biz
Version: 1.0.0
*/

define('RATCAT_TRIPLE_PANEL_SLIDER', WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) . '/' );

function ratcat_triple_panel_slider_jquery() {
	wp_enqueue_script('jquery');
}
add_action('init', 'ratcat_triple_panel_slider_jquery');

wp_enqueue_script('ratcat-triple-panel-slider-script-modern', RATCAT_TRIPLE_PANEL_SLIDER.'js/modernizr.custom.26887.js', array('jquery'));
wp_enqueue_script('ratcat-triple-panel-slider-imgslider', RATCAT_TRIPLE_PANEL_SLIDER.'js/jquery.imgslider.js', array('jquery'));
wp_enqueue_style('ratcat-triple-panel-slider-style', RATCAT_TRIPLE_PANEL_SLIDER.'css/style.css');

add_theme_support( 'post-thumbnails', array( 'post', 'triple-slider-items' ) );
add_image_size( 'triple-thumb', 340, 500 );

add_action( 'init', 'ratcat_triple_panel_slider_post' );
function ratcat_triple_panel_slider_post() {

	register_post_type( 'triple-slider-items',
		array(
			'labels' => array(
				'name' => __( 'Triple Slider' ),
				'singular_name' => __( 'Slider Item' ),
				'add_new_item' => __( 'Add New' )
			),
			'public' => true,
			'supports' => array('thumbnail', 'title', 'editor', 'custom-fields'),
			'has_archive' => true,
			'rewrite' => array('slug' => 'triple-slider'),
		)
	);
		

}

function triple_slider_taxonomy() {
	register_taxonomy(
		'triple_slider',  //The name of the taxonomy. Name should be in slug form (must not contain capital letters or spaces).
		'triple-slider-items',                  //post type name
		array(
			'hierarchical'          => true,
			'label'                         => 'All Categories',  //Display name
			'query_var'             => true,
			'show_admin_column'			=> true,
			'rewrite'                       => array(
				'slug'                  => 'triple-slider-category', // This controls the base slug that will display before each term
				'with_front'    => true // Don't display the category base before
				)
			)
	);
}
add_action( 'init', 'triple_slider_taxonomy');   

/* Loop */
function ratcat_get_triple( $category ){

	$tripleslider= '<div class="fs-slider fs-slider2" id="">';
	// $tripleslider.=	print_r($catt);
	$efs_query= "post_type=triple-slider-items&triple_slider=$category&posts_per_page=-1";
	query_posts($efs_query);
	if (have_posts()) : while (have_posts()) : the_post(); 
		$thumb = get_the_post_thumbnail( $post->ID, 'triple-thumb' );
		$tit = get_the_title($post->ID, 'triple-thumb');
		$url = get_post_meta(get_the_ID(), 'link', true);
		$cont = get_the_content($post->ID, 'triple-thumb');	
		$tripleslider.='<figure>'.$thumb.'<figcaption><h3><a href='.$url.'>'.$tit.'</a></h3><p>'.$cont.'</p></figcaption></figure>';	
	endwhile; endif; wp_reset_query();
	$tripleslider.= '</div>';
	return $tripleslider;
}

/**add the shortcode for the slider- for use in editor**/
function ratcat_triple_slider_last($atts, $content=null){
	extract( shortcode_atts( array(
		'category' => '',
	), $atts ) );
	$tripleslider= ratcat_get_triple($category);
	return $tripleslider;
}
add_shortcode('ratcat_triple', 'ratcat_triple_slider_last');


/* Theme options  START*/

function add_ratcattriple_options_framwrork()  
{  
	add_options_page('Triple Slider Options', 'Triple Slider Options', 'manage_options', 'ratcattripleslider-settings','ratcattripleslider_options_framwrork');  
}  
add_action('admin_menu', 'add_ratcattriple_options_framwrork');

// Default options values
$ratcattripleslider_options = array(
	'autoplay' => true,
	'interval' => 5000
);

if ( is_admin() ) : // Load only if we are viewing an admin page

function ratcattripleslider_register_settings() {
	// Register settings and call sanitation functions
	register_setting( 'ratcattripleslider_p_options', 'ratcattripleslider_options', 'ratcattripleslider_validate_options' );
}

add_action( 'admin_init', 'ratcattripleslider_register_settings' );


// Store layouts views in array
$autoplay = array(
	'auto_hide_yes' => array(
		'value' => 'true',
		'label' => 'Activate auto play'
	),
	'auto_hide_no' => array(
		'value' => 'false',
		'label' => 'Deactivate auto play'
	),
);


// Function to generate options page
function ratcattripleslider_options_framwrork() {
	global $ratcattripleslider_options, $autoplay;

	if ( ! isset( $_REQUEST['updated'] ) )
		$_REQUEST['updated'] = false; // This checks whether the form has just been submitted. ?>

	<div class="wrap" id="kanicon">

	<h2>Triple Slider Options</h2>

	<?php if ( false !== $_REQUEST['updated'] ) : ?>
	<div class="updated fade"><p><strong><?php _e( 'Options saved' ); ?></strong></p></div>
	<?php endif; // If the form has just been submitted, this shows the notification ?>

	<form method="post" action="options.php">

	<?php $settings = get_option( 'ratcattripleslider_options', $ratcattripleslider_options ); ?>
	
	<?php settings_fields( 'ratcattripleslider_p_options' );
	/* This function outputs some hidden fields required by the form,
	including a nonce, a unique number used to ensure the form has been submitted from the admin page
	and not somewhere else, very important for security */ ?>

	
	<table class="form-table"><!-- Grab a hot cup of coffee, yes we're using tables! -->
		
		<tr valign="top">
			<th scope="row"><label for="interval">Time interval for slides</label></th>
			<td>
				<input id="interval" type="text" name="ratcattripleslider_options[interval]" value="<?php echo stripslashes($settings['interval']); ?>" /><p class="description">Put slider speed here in milisecond (example: 5000). Default value is 5000.</p>
			</td>
		</tr>
		
		<tr valign="top">
			<th scope="row"><label for="autoplay">Autoplay</label></th>
			<td>
				<?php foreach( $autoplay as $activate ) : ?>
				<input type="radio" id="<?php echo $activate['value']; ?>" name="ratcattripleslider_options[autoplay]" value="<?php esc_attr_e( $activate['value'] ); ?>" <?php checked( $settings['autoplay'], $activate['value'] ); ?> />
				<label for="<?php echo $activate['value']; ?>"><?php echo $activate['label']; ?></label><br />
				<?php endforeach; ?>
			</td>
		</tr>		

			
	</table>

	<p class="submit"><input type="submit" class="button-primary" value="Save Options" /></p>

	</form>

	</div>

	<?php
}

function ratcattripleslider_validate_options( $input ) {
	global $ratcattripleslider_options, $autoplay;

	$settings = get_option( 'ratcattripleslider_options', $ratcattripleslider_options );
	
	// We strip all tags from the text field, to avoid vulnerablilties like XSS

	$input['interval'] = wp_filter_post_kses( $input['interval'] );

	
	// We select the previous value of the field, to restore it in case an invalid entry has been given
	$prev = $settings['layout_only'];
	// We verify if the given value exists in the layouts array
	if ( !array_key_exists( $input['layout_only'], $autoplay ) )
		$input['layout_only'] = $prev;	
		
		
	
	return $input;
}

endif;  // EndIf is_admin()

function ratcat_triple_panel_slider_active() { ?>
<?php global $ratcattripleslider_options; $ratcattripleslider_settings = get_option( 'ratcattripleslider_options', $ratcattripleslider_options ); ?>
<script type="text/javascript">
	jQuery(function() {
		jQuery( '.fs-slider2' ).imgslider({
			interval	: "<?php echo $ratcattripleslider_settings['interval']; ?>",
			autoplay	: <?php echo $ratcattripleslider_settings['autoplay']; ?>
		});
	});
</script>
<?php
}
add_action('wp_head', 'ratcat_triple_panel_slider_active');

/* Theme options  END*/
?>