<?php
/*
Plugin Name: SP Responsive header image slider
Plugin URL: http://sptechnolab.com
Description: A simple Responsive header image slider
Version: 1.0
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
		'publicly_queryable'=> true,
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
		 
		<img src="<?php if (has_post_thumbnail( $post->ID ) ): ?>
				<?php $image = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'single-post-thumbnail' ); 
				 echo $image[0]; endif; ?>"  alt="">
		
		 
		
		
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
		if ($autoplayspeed == '' )
		{
			$autoplaydefultspeed = 2000;
		} else { $autoplaydefultspeed = $autoplayspeed;
		}
		
		if ($pausehover == '' || $pausehover == '0') 
		{
			$pausedefulthover = 'true';
		} else { $pausedefulthover = 'false';
		}
	
	
	?>
	<script type="text/javascript">
	 jQuery(function() {
      jQuery('#slides').slidesjs({
        width: <?php echo $sliderdefultwidth ; ?>,
        height: <?php echo $sliderdefultheight ; ?>,		
        play: {
          active: <?php echo $pausedefulthover; ?>,
          auto: false,
          interval: <?php echo $autoplaydefultspeed; ?>,
          swap: true
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
            'Set auto play interval', // ID
            'Auto play speed', // Title 
            array( $this, 'auto_speed_callback' ), // Callback
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
		
		 if( isset( $input['auto_speed'] ) )
            $new_input['auto_speed'] = absint( $input['auto_speed'] );

     
		
		

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
	
	public function auto_speed_callback()
    {
        printf(
            '<input type="text" id="auto_speed" name="responsiveslider_option[auto_speed]" value="%s" />',
            isset( $this->options['auto_speed'] ) ? esc_attr( $this->options['auto_speed']) : ''
        );
		printf(' ie 500, 1000 milliseconds delay');
    }
	

	

}

if( is_admin() )
    $my_settings_page = new Responsiveimageslidersetting();