<?php

/**
 * Vin Decoder Wordpress Plugin
 * 
 * @package           Vin Decoder Wordpress Plugin Package
 * @author            Denis Dyachenko
 * @version           1.0.0
 * @copyright         
 * @license   
 * @link        
 *
 * @wordpress-plugin
 * Plugin Name:       Vin Decoder Wordpress Plugin
 * Plugin URI:        
 * Description:       
 * Version:           1.0.0
 * Requires at least: 5.6
 * Requires PHP:      8.0.1
 * Author:            Denis Dyachenko
 * Author URI:        
 * Text Domain:       
 * License:           
 * License URI:       
 */

class VinDecoder {
    private $vindecoder_options;

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'vindecoder_add_plugin_page' ) );
        add_action( 'admin_init', array( $this, 'vindecoder_page_init' ) );
        add_shortcode('vin-shortcode', array( $this, 'vindecoder_shortcode' ) );
    }

    public function vindecoder_add_plugin_page() {
        add_menu_page(
            'VinDecoder', 
            'VinDecoder', 
            'manage_options', 
            'vindecoder', 
            array( $this, 'vindecoder_create_admin_page' ), 
            'dashicons-admin-generic', 
            99 
        );
    }

    public function vindecoder_create_admin_page() {
        $this->vindecoder_options = get_option( 'vindecoder_option_name' ); ?>

        <div class="wrap">
            <h2>VinDecoder</h2>
            <p>Lorem ipsum</p>
            <?php settings_errors(); ?>

            <form method="post" action="options.php">
                <?php
                    settings_fields( 'vindecoder_option_group' );
                    do_settings_sections( 'vindecoder-admin' );
                    submit_button();
                ?>
            </form>         
        </div>
    <?php }

    public function vindecoder_page_init() {
        register_setting(
            'vindecoder_option_group', 
            'vindecoder_option_name', 
            array( $this, 'vindecoder_sanitize' ) 
        );

        add_settings_section(
            'vindecoder_setting_section', 
            'Settings', 
            array( $this, 'vindecoder_section_info' ), 
            'vindecoder-admin' 
        );

        add_settings_field(
            'vin', 
            'Введите vin код', 
            array( $this, 'vin_callback' ), 
            'vindecoder-admin',
            'vindecoder_setting_section' 
        );
    }

    public function vindecoder_sanitize($input) {
        $sanitary_values = array();
        if ( isset( $input['vin'] ) ) {
            $sanitary_values['vin'] = sanitize_text_field( $input['vin'] );
        }

        return $sanitary_values;
    }

    public function vindecoder_section_info() {
        
    }

    public function vin_callback() {
        printf(
            '<input class="regular-text" type="text" name="vindecoder_option_name[vin]" id="vin" value="%s">',
            isset( $this->vindecoder_options['vin'] ) ? esc_attr( $this->vindecoder_options['vin']) : ''
        );
    }

    public function vindecoder_shortcode(){   
        $my_vin_code = get_option('vindecoder_option_name');
        $postdata = http_build_query(
        array(
                'format' => 'json',
                'data' => $my_vin_code['vin']
            )
    );      

    $opts = array('http' =>
        array(
            'method' => 'POST',
            'content' => $postdata,
            'header' => "Content-type: application/x-www-form-urlencoded\r\n" .
                       "Content-Length: " . strlen ( $postdata ) . "\r\n" 
        )
    );
    $apiURL = "https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVINValuesBatch/";
    $context = stream_context_create($opts);
    $fp = fopen($apiURL, 'rb', false, $context);
    $line_of_text = fgets($fp);
    $json = json_decode($line_of_text, true);
    fclose($fp);

    ?>

<table class="table">
  <thead>
    <tr><th colspan="2">SPECIFICATIONS</th></tr>
 </thead>
 <tbody>
        
    <?php

    foreach ($json['Results'][0] as $k => $v){
        if(!empty($v)){
            echo "<tr> 
                    <td>{$k}</td><td>$v</td>                    
                  </tr>";
        }
    }

    ?>
</tbody>
</table>
    <?php 
        return;
    }

}

new VinDecoder();

