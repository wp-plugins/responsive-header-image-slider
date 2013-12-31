<?php
/*
Plugin Name: SP Responsive header image slider
Plugin URL: http://sptechnolab.com
Description: A simple Responsive header image slider
Version: 1.1
Author: SP Technolab
Author URI: http://sptechnolab.com
Contributors: SP Technolab
*/
/*
 * Register CPT sp_responsiveslider
 *
 */
function sp_responsiveslider_setup_post_types() {

	$responsiveslider_labels =  apply_filters( 'sp_responsiveslider_labels', array(
		'name'                => 'Responsive header image slider',
		'singular_name'       => 'Responsive header image slider',
		'add_new'             => __('Add New', 'sp_responsiveslider'),
		'add_new_item'        => __('Add New Image', 'sp_responsiveslider'),
		'edit_item'           => __('Edit Image', 'sp_responsiveslider'),
		'new_item'            => __('New Image', 'sp_responsiveslider'),
		'all_items'           => __('All Image', 'sp_responsiveslider'),
		'view_item'           => __('View Image', 'sp_responsiveslider'),
		'search_items'        => __('Search Image', 'sp_responsiveslider'),
		'not_found'           => __('No Image found', 'sp_responsiveslider'),
		'not_found_in_trash'  => __('No Image found in Trash', 'sp_responsiveslider'),
		'parent_item_colon'   => '',
		'menu_name'           => __('Responsive image slider', 'sp_responsiveslider'),
		'exclude_from_search' => true
	) );


	$responsiveslider_args = array(
		'labels' 			=> $responsiveslider_labels,
		'public' 			=> true,
		'publicly_queryable'		=> true,
		'show_ui' 			=> true,
		'show_in_menu' 		=> true,
		'query_var' 		=> true,
		'capability_type' 	=> 'post',
		'has_archive' 		=> true,
		'hierarchical' 		=> false,
		'supports' => array('title','thumbnail')
		
	);
	register_post_type( 'sp_responsiveslider', apply_filters( 'sp_faq_post_type_args', $responsiveslider_args ) );

}
add_action('init', 'sp_responsiveslider_setup_post_types');


add_action( 'admin_init', 'rsris_add_metaboxes' );
function rsris_add_metaboxes() {

  // This will register our metabox for all post types
  $post_types = get_post_types();
  // This will remove the meta box from our slides post type
  unset($post_types['rsris_slides']);
      foreach ( $post_types as $post_type ){
        // Box for your posts for inserting your slider element.
	 add_meta_box('rsris_slide_link_box', 'LINK URL', 'rsris_slide_link_box', $post_type , 'normal', 'core');
        //add_meta_box('rsris_multipeselect_metabox', 'LINK URL', 'rsris_multipeselect_metabox', $post_type, 'normal', 'core');
      }
  // Box for inserting the link the slide should link to.
  add_meta_box('rsris_slide_link_box', 'Slide link', 'rsris_slide_link_box', 'rsris_slides', 'normal', 'core');
  add_meta_box('rsris_slide_embed_box', 'Youtube Share link', 'rsris_slide_embed_box', 'rsris_slides', 'normal', 'core');
}
 
// Our metabox for choosing the slides
function rsris_multipeselect_metabox() {
   global $post;
   
   wp_nonce_field( plugin_basename( __FILE__ ), 'rsris_ms_metabox_nonce' );
   
   $rsris_ms_posts = get_posts( array(
   'post_type' => 'rsris_slides',
   'numberposts' => -1

   ));
   $rsris_slides = get_post_meta( $post->ID, 'rsris_slide', true );

   $rsris_ms_output = '<div class="rsris-select-wrapper"><div class="rsris-select-left"><div class="rsris-search-field-wrapper">Link Url:<input type="text" id="rsris-search-field" placeholder="http://"></div><ul class="rsris-items">';
   
   $rsris_ms_output .= '</ul></div></div>';
   $rsris_ms_output .= '<div style="clear:both;"></div>';
   echo $rsris_ms_output;
}
 
// Save data from meta box
add_action('save_post', 'rsris_checkbox_metabox_save');
function rsris_checkbox_metabox_save($post_id) {
  // verify nonce
  if ( !wp_verify_nonce( $_POST['rsris_ms_metabox_nonce'], plugin_basename( __FILE__ ) ) )
        return;
 
  // check autosave
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)
        return;
 
  // check permissions
  if (!current_user_can('edit_post', $post_id))
    return;
 
        $old['rsris_slide'] = get_post_meta( $post_id, 'rsris_slide', true );
        $new['rsris_slide'] = $_POST['rsris_slide'];
       
        if ( $new['rsris_slide'] && $new['rsris_slide'] != $old['rsris_slide'] ) {
          update_post_meta($post_id, 'rsris_slide', $new['rsris_slide']);
        } elseif ( '' == $new['rsris_slide'] && $old['rsris_slide'] ) {
          delete_post_meta($post_id, 'rsris_slide', $old['rsris_slide']);
        }
}


/**
* Register meta boxes for inserting a links and embeds
*/
function rsris_slide_link_box() {
  global $post;
  $rsris_slide_link = get_post_meta( $post->ID, 'rsris_slide_link', true );
  
  wp_nonce_field( plugin_basename( __FILE__ ), 'rsris_slide_link_box_nounce' );
  
  $rsris_slide_link_output .= '<input type="text" name="rsris_slide_link" id="rsris_slide_link" class="widefat" value="'.$rsris_slide_link.'" />';
  	echo $rsris_slide_link_output;
}

function rsris_slide_embed_box() {
  global $post;
  $rsris_slide_embed = get_post_meta( $post->ID, 'rsris_slide_embed', true );
  
  wp_nonce_field( plugin_basename( __FILE__ ), 'rsris_slide_embed_box_nounce' );
  
  $rsris_slide_embed_output = rsris_embed_video( $post->ID, 260, 120);
  $rsris_slide_embed_output .= '<label for="rsris_slide_embed"><span class="howto">Copy and paste the link to your YouTube video</span></label>';
  $rsris_slide_embed_output .= '<input type="text" name="rsris_slide_embed" id="rsris_slide_embed" class="widefat" value="'.$rsris_slide_embed.'" />';
  echo $rsris_slide_embed_output;
}



add_action( 'save_post', 'rsris_link_save' );  
function rsris_link_save( $post_id )  
{  
    // Bail if we're doing an auto save  
    if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return; 
     
    // verify nonce
    if ( !wp_verify_nonce( $_POST['rsris_slide_link_box_nounce'], plugin_basename( __FILE__ ) ) )
        return; 
     
    // if our current user can't edit this post, bail  
    if( !current_user_can( 'edit_post' ) ) return; 

    if( isset( $_POST['rsris_slide_link'] ) )  
        update_post_meta( $post_id, 'rsris_slide_link', wp_kses( $_POST['rsris_slide_link']) );

    if( isset( $_POST['rsris_slide_embed'] ) )  
        update_post_meta( $post_id, 'rsris_slide_embed', wp_kses( $_POST['rsris_slide_embed']) );

}


/*
 * Add [sp_responsiveslider limit="-1"] shortcode
 *
 */
function sp_responsiveslider_shortcode( $atts, $content = null ) {
	
	extract(shortcode_atts(array(
		"limit" => ''
	), $atts));
	
	// Define limit
	if( $limit ) { 
		$posts_per_page = $limit; 
	} else {
		$posts_per_page = '-1';
	}
	
	ob_start();

	// Create the Query

	$post_type 		= 'sp_responsiveslider';
	$orderby 		= 'post_date';
	$order 			= 'DESC';
				
	$query = new WP_Query( array ( 
								'post_type'      => $post_type,
								'posts_per_page' => $posts_per_page,
								'orderby'        => $orderby, 
								'order'          => $order,
								'no_found_rows'  => 1
								) 
						);
	//Get post type count
	$post_count = $query->post_count;
	$i = 1;
	
	// Displays Custom post info
	
	
	
	if( $post_count > 0) :
	?>
	
	  <div id="slides">
	<?php
		
		// Loop 
		while ($query->have_posts()) : $query->the_post();

		?>
		<?php $respslideroption = 'responsiveslider_option';
	$respslideroptionadmin = get_option( $respslideroption, $default ); 
	$link = $respslideroptionadmin['link'];  
		 	if ($link == '' || $link == '0'  )
		{?>
		<a href="<?php echo get_post_meta( get_the_ID(),'rsris_slide_link', true ) ?>" target="_blank">
		<?php } ?>
		<img src="<?php if (has_post_thumbnail( $post->ID ) ): ?>
				<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); 
				 echo $image[0]; endif; ?>"  alt="">
				<?php	if ($link == '' || $link == '0'  )
		{?> 
				 </a>
			<?php } ?>
		<?php
		$i++;
		endwhile; ?>
		</div>
		
<?php	else : ?>
 <div id="slides">
	 <img src="<?php echo  plugin_dir_url( __FILE__ ); ?>/img/1.png"  alt="">
	  <img src="<?php echo  plugin_dir_url( __FILE__ ); ?>/img/2.png"  alt="">
	   <img src="<?php echo  plugin_dir_url( __FILE__ ); ?>/img/3.png"  alt="">
	</div>
	<?php
	endif;
	// Reset query to prevent conflicts
	wp_reset_query();
	
	?>
	
	<?php
	
	return ob_get_clean();

}
	add_shortcode("sp_responsiveslider", "sp_responsiveslider_shortcode");

	wp_register_style( 'respslidercss', plugin_dir_url( __FILE__ ) . 'css/responsiveimgslider.css' );
	wp_register_script( 'respsliderjs', plugin_dir_url( __FILE__ ) . 'js/jquery.slides.min.js', array( 'jquery' ) );	

	wp_enqueue_style( 'respslidercss' );
	wp_enqueue_script( 'respsliderjs' );

	function sp_responsiveslider_script() {
	
	$respslideroption = 'responsiveslider_option';
	$respslideroptionadmin = get_option( $respslideroption, $default ); 
	$sliderwidth = $respslideroptionadmin['slider_width']; 
	$sliderheight = $respslideroptionadmin['slider_height'];	
	$autoplayspeed = $respslideroptionadmin['auto_speed'];
	$pausehover = $respslideroptionadmin['hover_pause'];	
		$auto_play = $respslideroptionadmin['auto_play'];
		$pagination = $respslideroptionadmin['pagination'];
		
		if ($sliderwidth == '' )
		{
			$sliderdefultwidth = 980;
		} else { $sliderdefultwidth = $sliderwidth;
		}
		if ($sliderheight == '' )
		{
			$sliderdefultheight = 300;
		} else { $sliderdefultheight = $sliderheight;
		}
		
		if ($pausehover == '' || $pausehover == '0') 
		{
			$pausedefulthover = 'true';
		} else { $pausedefulthover = 'false';
		}
		
		if ($autoplayspeed == '' )
		{
			$autoplaydefultspeed = 2000;
		} else { $autoplaydefultspeed = $autoplayspeed;
		}
		
		if ($auto_play == '' || $auto_play == '0') 
		{
			$autopalytrue = 'true';
		} else { $autopalytrue = 'false';
		}
		
		if ($pagination == '' || $pagination == '0') 
		{
			$paginationtrue = 'true';
		} else { $paginationtrue = 'false';
		}
	
	
	?>
	<script type="text/javascript">
	 jQuery(function() {
      jQuery('#slides').slidesjs({
        width: <?php echo $sliderdefultwidth ; ?>,
        height: <?php echo $sliderdefultheight ; ?>,		
        play: {
          active: <?php echo $pausedefulthover; ?>,
          auto: <?php echo $autopalytrue; ?>,
          interval: <?php echo $autoplaydefultspeed; ?>,
          swap: true
        },
		 pagination: {
      active: <?php echo $paginationtrue; ?>
    }

      });
    });
	</script>
	<?php
	}
add_action('wp_head', 'sp_responsiveslider_script'); 
class Responsiveimageslidersetting
{
    /**
     * Holds the values to be used in the fields callbacks
     */
    private $options;

    /**
     * Start up
     */
    public function __construct()
    {
        add_action( 'admin_menu', array( $this, 'res_header_image_slider' ) );
        add_action( 'admin_init', array( $this, 'resppage_init' ) );
    }

    /**
     * Add options page
     */
    public function res_header_image_slider()
    {
        // This page will be under "Settings"
        add_options_page(
            'Settings Admin', 
            'Responsive slider Settings', 
            'manage_options', 
            'responsive-slider-setting-admin', 
            array( $this, 'create_admin_page' )
        );
    }

    /**
     * Options page callback
     */
    public function create_admin_page()
    {
        // Set class property
        $this->options = get_option( 'responsiveslider_option' );
        ?>
        <div class="wrap">
            <?php screen_icon(); ?>
            <h2>Responsive header image slider Setting</h2>           
            <form method="post" action="options.php">
            <?php
                // This prints out all hidden setting fields
                settings_fields( 'respslider_option_group' );   
                do_settings_sections( 'responsive-slider-setting-admin' );
                submit_button(); 
		
            ?>
            </form>
        </div>
        <?php
    }

    /**
     * Register and add settings
     */
    public function resppage_init()
    {        
        register_setting(
            'respslider_option_group', // Option group
            'responsiveslider_option', // Option name
            array( $this, 'sanitize' ) // Sanitize
        );

        add_settings_section(
            'setting_section_id', // ID
            'My Custom Slider Settings', // Title
            array( $this, 'print_section_info' ), // Callback
            'responsive-slider-setting-admin' // Page
        );  

        add_settings_field(
            'slider_width', // ID
            'Slider Width', // Title 
            array( $this, 'slider_width_callback' ), // Callback
            'responsive-slider-setting-admin', // Page
            'setting_section_id' // Section           
        );      

        add_settings_field(
            'slider_height', 
            'Slider Height', 
            array( $this, 'slider_height_callback' ), 
            'responsive-slider-setting-admin', 
            'setting_section_id'
        );     
		
	add_settings_field(
            'hover_pause', 
            'Auto Play button', 
            array( $this, 'hover_pause_callback' ), 
            'responsive-slider-setting-admin', 
            'setting_section_id'
        );  
		add_settings_field(
            'auto_play', 
            'Auto Play ', 
            array( $this, 'auto_play_callback' ), 
            'responsive-slider-setting-admin', 
            'setting_section_id'
        );  
		
		 add_settings_field(
            'auto_speed', // ID
            'Auto play speed', // Title 
            array( $this, 'auto_speed_callback' ), // Callback
            'responsive-slider-setting-admin', // Page
            'setting_section_id' // Section           
        );  
		
		 add_settings_field(
            'pagination', // ID
            'Pagination', // Title 
            array( $this, 'pagination_callback' ), // Callback
            'responsive-slider-setting-admin', // Page
            'setting_section_id' // Section           
        );  
		
		add_settings_field(
            'link', // ID
            'Custom link to image', // Title 
            array( $this, 'link_callback' ), // Callback
            'responsive-slider-setting-admin', // Page
            'setting_section_id' // Section           
        );     		
			
    }

    /**
     * Sanitize each setting field as needed
     *
     * @param array $input Contains all settings fields as array keys
     */
    public function sanitize( $input )
    {
        $new_input = array();
        if( isset( $input['slider_width'] ) )
            $new_input['slider_width'] = absint( $input['slider_width'] );

        if( isset( $input['slider_height'] ) )
            $new_input['slider_height'] = sanitize_text_field( $input['slider_height'] );
			
		 if( isset( $input['hover_pause'] ) )
            $new_input['hover_pause'] = absint( $input['hover_pause'] );

 if( isset( $input['auto_play'] ) )
            $new_input['auto_play'] = absint( $input['auto_play'] );		

 if( isset( $input['pagination'] ) )
            $new_input['pagination'] = absint( $input['pagination'] );			
		
		 if( isset( $input['auto_speed'] ) )
            $new_input['auto_speed'] = absint( $input['auto_speed'] );	
			
	 if( isset( $input['link'] ) )
            $new_input['link'] = absint( $input['link'] );
        return $new_input;
    }

    /** 
     * Print the Section text
     */
    public function print_section_info()
    {
        print 'Enter your settings below:';
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function slider_width_callback()
    {
        printf(
            '<input type="text" id="slider_width" name="responsiveslider_option[slider_width]" value="%s" />',
            isset( $this->options['slider_width'] ) ? esc_attr( $this->options['slider_width']) : ''
        );
			printf('px');
    }

    /** 
     * Get the settings option array and print one of its values
     */
    public function slider_height_callback()
    {
        printf(
            '<input type="text" id="slider_height" name="responsiveslider_option[slider_height]" value="%s" />',
            isset( $this->options['slider_height'] ) ? esc_attr( $this->options['slider_height']) : ''
        );
			printf('px');
    }
	
	public function hover_pause_callback()
    {
        printf(
            '<input type="text" id="hover_pause" name="responsiveslider_option[hover_pause]" value="%s" />',
            isset( $this->options['hover_pause'] ) ? esc_attr( $this->options['hover_pause']) : ''
        );
		printf(' Enter "0" for <b>True</b> and "1" for <b>False</b>');
    }
	
		public function auto_play_callback()
    {
        printf(
            '<input type="text" id="auto_play" name="responsiveslider_option[auto_play]" value="%s" />',
            isset( $this->options['auto_play'] ) ? esc_attr( $this->options['auto_play']) : ''
        );
		printf(' Enter "0" for <b>True</b> and "1" for <b>False</b>');
    }
	
	
	public function pagination_callback()
    {
        printf(
            '<input type="text" id="pagination" name="responsiveslider_option[pagination]" value="%s" />',
            isset( $this->options['pagination'] ) ? esc_attr( $this->options['pagination']) : ''
        );
		printf(' Enter "0" for <b>True</b> and "1" for <b>False</b>');
    }
	
	public function auto_speed_callback()
    {
        printf(
            '<input type="text" id="auto_speed" name="responsiveslider_option[auto_speed]" value="%s" />',
            isset( $this->options['auto_speed'] ) ? esc_attr( $this->options['auto_speed']) : ''
        );
		printf(' ie 500, 1000 milliseconds delay');
    }
	
	
	
		public function link_callback()
    {
        printf(
            '<input type="text" id="link" name="responsiveslider_option[link]" value="%s" />',
            isset( $this->options['link'] ) ? esc_attr( $this->options['link']) : ''
        );
		printf(' Enter "0" for <b>True</b> and "1" for <b>False</b>');
    }
}

if( is_admin() )
    $my_settings_page = new Responsiveimageslidersetting();
