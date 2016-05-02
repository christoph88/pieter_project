<?php

/**
 *
 *
 * @author Sergey Burkov, http://www.wp3dprinting.com
 * @copyright 2015
 */

function p3d_activate() {
	global $wpdb;

	if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die ('Woocommerce is not installed!');
	}

	$current_version = get_option( '3dp_version');

	$check_query = $wpdb->get_row( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies" );

	$attr = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '%s'", 'p3d_printer' ) );
	if ( strlen( $attr->attribute_id )==0 ) {

		$attribute=array( 'attribute_name'=>'p3d_printer',
			'attribute_label'=>__( 'Printer', '3dprint' ),
			'attribute_type'=>'text',
			'attribute_orderby'=>'menu_order',
			'attribute_public'=>'0' );
		if ( !isset( $check_query->attribute_public ) ) unset( $attribute['attribute_public'] );
		$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );

		do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );
		delete_transient( 'wc_attribute_taxonomies' );


	}
	$attr = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '%s'", 'p3d_material' ) );
	if ( strlen( $attr->attribute_id )==0 ) {
		$attribute=array( 'attribute_name'=>'p3d_material',
			'attribute_label'=>__( 'Material', '3dprint' ),
			'attribute_type'=>'text',
			'attribute_orderby'=>'menu_order',
			'attribute_public'=>'0' );
		if ( !isset( $check_query->attribute_public ) ) unset( $attribute['attribute_public'] );
		$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );

		do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );
		delete_transient( 'wc_attribute_taxonomies' );

	}

	$attr = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '%s'", 'p3d_coating' ) );
	if ( strlen( $attr->attribute_id )==0 ) {
		$attribute=array( 'attribute_name'=>'p3d_coating',
			'attribute_label'=>__( 'Coating', '3dprint' ),
			'attribute_type'=>'text',
			'attribute_orderby'=>'menu_order',
			'attribute_public'=>'0' );
		if ( !isset( $check_query->attribute_public ) ) unset( $attribute['attribute_public'] );
		$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );

		do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );
		delete_transient( 'wc_attribute_taxonomies' );

	}

	$attr = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '%s'", 'p3d_model' ) );
	if ( strlen( $attr->attribute_id )==0 ) {
		$attribute=array( 'attribute_name'=>'p3d_model',
			'attribute_label'=>__( 'Model', '3dprint' ),
			'attribute_type'=>'text',
			'attribute_orderby'=>'menu_order',
			'attribute_public'=>'0' );
		if ( !isset( $check_query->attribute_public ) ) unset( $attribute['attribute_public'] );
		$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );

		do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );
		delete_transient( 'wc_attribute_taxonomies' );

	}

	$attr = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '%s'", 'p3d_unit' ) );
	if ( strlen( $attr->attribute_id )==0 ) {
		$attribute=array( 'attribute_name'=>'p3d_unit',
			'attribute_label'=>__( 'Unit', '3dprint' ),
			'attribute_type'=>'text',
			'attribute_orderby'=>'menu_order',
			'attribute_public'=>'0' );
		if ( !isset( $check_query->attribute_public ) ) unset( $attribute['attribute_public'] );
		$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );

		do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );
		delete_transient( 'wc_attribute_taxonomies' );

	}

	$attr = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}woocommerce_attribute_taxonomies WHERE attribute_name = '%s'", 'p3d_infill' ) );
	if ( strlen( $attr->attribute_id )==0 ) {
		$attribute=array( 'attribute_name'=>'p3d_infill',
			'attribute_label'=>__( 'Infill', '3dprint' ),
			'attribute_type'=>'text',
			'attribute_orderby'=>'menu_order',
			'attribute_public'=>'0' );
		if ( !isset( $check_query->attribute_public ) ) unset( $attribute['attribute_public'] );
		$wpdb->insert( $wpdb->prefix . 'woocommerce_attribute_taxonomies', $attribute );

		do_action( 'woocommerce_attribute_added', $wpdb->insert_id, $attribute );
		delete_transient( 'wc_attribute_taxonomies' );

	}

	if (!empty($current_version) && version_compare($current_version, '1.5.8', '<')) {
		$postlist = get_posts(array(
			'posts_per_page'=> -1,
			'post_type'  => 'product'));
		foreach ( $postlist as $post ) {
			if ( p3d_is_p3d( $post->ID ) ) {
				p3d_delete_p3d( $post->ID );
				$_POST['post_ID']=$post->ID;
				$_POST['_3dprinting']='on';
				p3d_save_post($post->ID);

			}
		}
	}


	$charset_collate = $wpdb->get_charset_collate();

	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );


	$sql = "CREATE TABLE ".$wpdb->prefix."p3d_printers (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  name varchar(64) DEFAULT '' NOT NULL,
		  width smallint(6) DEFAULT 0 NOT NULL,
		  length smallint(6) DEFAULT 0 NOT NULL,
		  height smallint(6) DEFAULT 0 NOT NULL,
		  price varchar(128) DEFAULT '0' NOT NULL,
		  materials varchar(64) DEFAULT '' NOT NULL,
		  infills varchar(64) DEFAULT '0,10,20,30,40,50,60,70,80,90,100' NOT NULL,
		  default_infill varchar(3) DEFAULT '20' NOT NULL,
		  layer_height float DEFAULT 0.1 NOT NULL,
		  wall_thickness float DEFAULT 0.8 NOT NULL,
		  nozzle_size float DEFAULT 0.4 NOT NULL,
		  price_type varchar(32) DEFAULT 'box_volume' NOT NULL,
		  sort_order smallint(6) DEFAULT 0 NOT NULL,
		  group_name varchar(64) DEFAULT '' NOT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";


	dbDelta( $sql );

	$cols = $wpdb->get_col($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."p3d_printers LIMIT 1", null ) );

	if ( empty($cols) ){
		if (!empty($current_version) && version_compare($current_version, '1.5.0', '<')) {
			$current_printers=get_option('3dp_printers');
			foreach ($current_printers as $printer) {
				$printer['materials'] = '';
				$wpdb->insert( $wpdb->prefix."p3d_printers", $printer );
			}
		}
		else {
			$default_printers[]=array(
				'name' => 'Default Printer',
				'width' => '300',
				'length' => '400',
				'height' => '300',
				'price' => '0.02',
				'layer_height' => '0.1',
				'wall_thickness' => '0.8',
				'nozzle_size' => '0.4',
				'infills' => '0,10,20,30,40,50,60,70,80,90,100',
				'default_infill' => '20',
				'materials' => "",
				'price_type' => 'box_volume',
				'group_name' => '',
				'sort_order' => '10'
			);
			foreach ($default_printers as $printer) {
				$wpdb->insert( $wpdb->prefix."p3d_printers", $printer );
			}
	
		}
	
	}


	$sql = "CREATE TABLE ".$wpdb->prefix."p3d_materials (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  name varchar(64) DEFAULT '' NOT NULL,
		  type varchar(64) DEFAULT 'filament' NOT NULL,
		  length smallint(6) DEFAULT 0 NOT NULL,
		  density float DEFAULT 0 NOT NULL,
		  diameter float DEFAULT 0 NOT NULL,
		  weight float DEFAULT 0 NOT NULL,
		  price varchar(128) DEFAULT '0' NOT NULL,
		  roll_price float DEFAULT 0 NOT NULL,
		  price_type varchar(32) DEFAULT 'cm3' NOT NULL,
		  color varchar(7) DEFAULT '' NOT NULL,
		  sort_order smallint(6) DEFAULT 0 NOT NULL,
		  group_name varchar(64) DEFAULT '' NOT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";


	dbDelta( $sql );


	$cols = $wpdb->get_col($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."p3d_materials LIMIT 1", null ) );

	if ( empty($cols) ){
		if (!empty($current_version) && version_compare($current_version, '1.5.0', '<')) {
			$current_materials=get_option('3dp_materials');
			foreach ($current_materials as $material) {
				$wpdb->insert( $wpdb->prefix."p3d_materials", $material );
			}
		}
		else {
			$default_materials[]=array(
				'name' => 'PLA - Green',
				'type' => 'filament',
				'density' => '1.26',
				'length' => '330',
				'diameter' => '1.75',
				'weight' => '1',
				'price' => '0.03',
				'price_type' => 'gram',
				'roll_price' => '20',
				'group_name' => 'PLA',
				'color' => '#08c101'
			);
			$default_materials[]=array(
				'name' => 'ABS - Red',
				'type' => 'filament',
				'density' => '1.41',
				'length' => '100',
				'diameter' => '3',
				'weight' => '1',
				'price' => '0.04',
				'price_type' => 'gram',
				'roll_price' => '25',
				'group_name' => 'ABS',
				'color' => '#dd3333'
			);

			foreach ($default_materials as $material) {
				$wpdb->insert( $wpdb->prefix."p3d_materials", $material );
			}
	
		}
	
	}

	$sql = "CREATE TABLE ".$wpdb->prefix."p3d_coatings (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  name varchar(64) DEFAULT '' NOT NULL,
		  price varchar(128) DEFAULT '0' NOT NULL,
		  color varchar(7) DEFAULT '' NOT NULL,
		  materials varchar(64) DEFAULT '' NOT NULL,
		  sort_order smallint(6) DEFAULT 0 NOT NULL,
		  group_name varchar(64) DEFAULT '' NOT NULL,
		  UNIQUE KEY id (id)
		) $charset_collate;";


	dbDelta( $sql );

	$cols = $wpdb->get_col($wpdb->prepare("SELECT * FROM ".$wpdb->prefix."p3d_coatings LIMIT 1", null ));

	if ( empty($cols) ){
		if (!empty($current_version) && version_compare($current_version, '1.5.0', '<')) {
			$current_coatings=get_option('3dp_coatings');
			if (count($current_coatings)>0) {
				foreach ($current_coatings as $coating) {
					$wpdb->insert( $wpdb->prefix."p3d_coatings", $coating );
				}
			}
		}

	}



	$current_settings = get_option( '3dp_settings' );
	$current_layout = $current_settings['attributes_layout'];

	$settings=array(
		'pricing' => (isset($current_settings['checkout']) ? $current_settings['checkout'] : 'checkout'),
		'canvas_width' => (isset($current_settings['canvas_width']) ? $current_settings['canvas_width'] : '512'),
		'cookie_expire' => (isset($current_settings['cookie_expire']) ? $current_settings['cookie_expire'] : '0'),
		'canvas_height' => (isset($current_settings['canvas_height']) ? $current_settings['canvas_height'] : '384'),
		'background1' => (isset($current_settings['background1']) ? $current_settings['background1'] : '#FFFFFF'),
		'background2' => (isset($current_settings['background2']) ? $current_settings['background2'] : '#1e73be'),
		'plane_color' => (isset($current_settings['plane_color']) ? $current_settings['plane_color'] : '#FFFFFF'),
		'printer_color' => (isset($current_settings['printer_color']) ? $current_settings['printer_color'] : '#dd9933'),
		'button_color1' => (isset($current_settings['button_color1']) ? $current_settings['button_color1'] : '#1d9650'),
		'button_color2' => (isset($current_settings['button_color2']) ? $current_settings['button_color2'] : '#148544'),
		'button_color3' => (isset($current_settings['button_color3']) ? $current_settings['button_color3'] : '#0e7138'),
		'button_color4' => (isset($current_settings['button_color4']) ? $current_settings['button_color4'] : '#fff'),
		'button_color5' => (isset($current_settings['button_color5']) ? $current_settings['button_color5'] : '#fff'),
		'zoom' => (isset($current_settings['zoom']) ? $current_settings['zoom'] : '2'),
		'angle_x' => (isset($current_settings['angle_x']) ? $current_settings['angle_x'] : '-90'),
		'angle_y' => (isset($current_settings['angle_y']) ? $current_settings['angle_y'] : '25'),
		'angle_z' => (isset($current_settings['angle_z']) ? $current_settings['angle_z'] : '0'),
		'show_canvas_stats' => (isset($current_settings['show_canvas_stats']) ? $current_settings['show_canvas_stats'] : 'on'),
		'show_model_stats' => (isset($current_settings['show_model_stats']) ? $current_settings['show_model_stats'] : 'on'),
		'show_printers' => (isset($current_settings['show_printers']) ? $current_settings['show_printers'] : 'on'),
		'show_materials' => (isset($current_settings['show_materials']) ? $current_settings['show_materials'] : 'on'),
		'show_coatings' => (isset($current_settings['show_coatings']) ? $current_settings['show_coatings'] : 'on'),
		'show_infills' => (isset($current_settings['show_infills']) ? $current_settings['show_infills'] : ''),
		'show_scale' => (isset($current_settings['show_scale']) ? $current_settings['show_scale'] : 'on'),
		'file_extensions' => (isset($current_settings['file_extensions']) ? $current_settings['file_extensions'] : 'stl,obj,zip'),
		'file_max_size' => (isset($current_settings['max_size']) ? $current_settings['max_size'] : '30'),
		'file_max_days' => (isset($current_settings['max_days']) ? $current_settings['max_days'] : ''),
		'api_repair' => (isset($current_settings['api_repair']) ? $current_settings['api_repair'] : ''),
		'api_analyse' => (isset($current_settings['api_analyse']) ? $current_settings['api_analyse'] : ''),
		'api_login' => (isset($current_settings['api_login']) ? $current_settings['api_login'] : ''),
		'api_subscription_login' => (isset($current_settings['api_subscription_login']) ? $current_settings['api_subscription_login'] : ''),
		'api_subscription_key' => (isset($current_settings['api_subscription_key']) ? $current_settings['api_subscription_key'] : ''), 
		'cookie_expire' => (isset($current_settings['cookie_expire']) ? $current_settings['cookie_expire'] : '2'),
		'printers_layout' => (isset($current_settings['printers_layout']) ? $current_settings['printers_layout'] : 'dropdowns'),
		'materials_layout' => (isset($current_settings['materials_layout']) ? $current_settings['materials_layout'] : $current_settings['attributes_layout']),
		'coatings_layout' => (isset($current_settings['coatings_layout']) ? $current_settings['coatings_layout'] : $current_settings['attributes_layout']),
		'infills_layout' => (isset($current_settings['infills_layout']) ? $current_settings['infills_layout'] : 'dropdowns')

	);

	update_option( '3dp_settings', $settings );

	add_option( '3dp_price_requests', '' );
	update_option( '3dp_servers',  array(0=>'http://srv1.wp3dprinting.com', 1=>'http://srv2.wp3dprinting.com') );


	$p3d_attr_prices=get_option( '3dp_attr_prices' );

	if (is_array($p3d_attr_prices) && count($p3d_attr_prices)) {
		foreach ($p3d_attr_prices as $key=>$value) {
			foreach ($value as $key1=>$info) {
				if ($info['price_type']=='pct' && $info['pct_type']=='') {
					$p3d_attr_prices[$key][$key1]['pct_type']='total';
				}
			}
		}
		update_option('3dp_attr_prices', $p3d_attr_prices);
	}


	$upload_dir = wp_upload_dir();
	if ( !is_dir( $upload_dir['basedir'].'/p3d/' ) ) {
		mkdir( $upload_dir['basedir'].'/p3d/' );
	}

	if ( !file_exists( $upload_dir['basedir'].'/p3d/index.html' ) ) {
		$fp = fopen( $upload_dir['basedir'].'/p3d/index.html', "w" );
		fclose( $fp );
	}

	$htaccess_contents='
<FilesMatch "\.(php([0-9]|s)?|s?p?html|cgi|py|pl|exe)$">
	Order Deny,Allow
	Deny from all
</FilesMatch>
AddType application/octet-stream obj
AddType application/octet-stream stl
<ifmodule mod_deflate.c>
	AddOutputFilterByType DEFLATE application/octet-stream
</ifmodule>
<ifmodule mod_expires.c>
	ExpiresActive on
	ExpiresDefault "access plus 365 days"
</ifmodule>
<ifmodule mod_headers.c>
	Header set Cache-Control "max-age=31536050"
</ifmodule>
	';
	
	if ( !file_exists( $upload_dir['basedir'].'/p3d/.htaccess' ) || version_compare($current_version, '1.5.0', '<')) {
		file_put_contents( $upload_dir['basedir'].'/p3d/.htaccess', $htaccess_contents );
	}

	add_option( 'p3d_do_activation_redirect', true );

	update_option( '3dp_version', '1.5.8.4' );

	do_action( '3dprint_activate' );
}

function p3d_get_option ($option) {
	global $wpdb;
	switch ($option) {
		case '3dp_printers' :
			$db_printers = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."p3d_printers", ARRAY_A );;
			foreach ($db_printers as $printer) {
				$printers[$printer['id']]=$printer;
			}
			return $printers;
		break;

		case '3dp_materials' :
			$db_materials = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."p3d_materials", ARRAY_A );
			foreach ($db_materials as $material) {
				$materials[$material['id']]=$material;
			}
			return $materials;
		break;

		case '3dp_coatings' :
			$db_coatings = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."p3d_coatings", ARRAY_A );
			foreach ($db_coatings as $coating) {
				$coatings[$coating['id']]=$coating;
			}
			return $coatings;
		break;

		default :
			return get_option($option);
		break;
	
	}
}

function p3d_add_option ($option, $data) {
	global $wpdb;
	switch ($option) {
		case '3dp_printers' :
			$wpdb->insert( $wpdb->prefix . 'p3d_printers', $data );
		break;

		case '3dp_materials' :
			$wpdb->insert( $wpdb->prefix . 'p3d_materials', $data );
		break;

		case '3dp_coatings' :
			$wpdb->insert( $wpdb->prefix . 'p3d_coatings', $data );
		break;

		default :
			add_option($data);
		break;
	
	}
}

function p3d_update_option ($option, $data) {
	global $wpdb;
	switch ($option) {
		case '3dp_printers' :
			$wpdb->replace( $wpdb->prefix . 'p3d_printers', $data );
		break;

		case '3dp_materials' :
			$wpdb->replace( $wpdb->prefix . 'p3d_materials', $data );
		break;

		case '3dp_coatings' :
			$wpdb->replace( $wpdb->prefix . 'p3d_coatings', $data );
		break;

		default :
			update_option($data);
		break;
	
	}
}

add_action( 'plugins_loaded', 'p3d_load_textdomain' );
function p3d_load_textdomain() {
	load_plugin_textdomain( '3dprint', false, dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/' );
}

function p3d_filter_update_checks($queryArgs) {
	$settings = get_option('3dp_settings');
	if ( !empty($settings['api_login']) ) {
		$queryArgs['login'] = $settings['api_login'];
	}
	return $queryArgs;
}


function p3d_enqueue_scripts_backend() {
	global $wp_scripts;
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script( 'jquery-ui' );
	wp_enqueue_script( 'jquery-ui-tabs' );
	wp_enqueue_script( 'js/3dprint-backend.js', plugin_dir_url( __FILE__ ).'js/3dprint-backend.js', array( 'jquery' ) );
	wp_enqueue_script( 'jquery.sumoselect.min.js',  plugin_dir_url( __FILE__ ).'ext/sumoselect/jquery.sumoselect.min.js', array( 'jquery' ) );
	wp_enqueue_style( 'sumoselect.css', plugin_dir_url( __FILE__ ).'ext/sumoselect/sumoselect.css' );
#	wp_enqueue_script( 'tipso.js',  plugin_dir_url( __FILE__ ).'ext/tipso/tipso.js', array( 'jquery' ) );
#	wp_enqueue_style( 'tipso.css', plugin_dir_url( __FILE__ ).'ext/tipso/tipso.css' );
	wp_enqueue_style( 'jquery-ui.min.css', plugin_dir_url( __FILE__ ).'css/jquery-ui.min.css' );
}

function p3d_enqueue_scripts_frontend() {
	global $post;
	if ( is_shop() ) return false;
	$product = new WC_Product_Variable( get_the_ID() );
	if ( !method_exists( $product, 'get_available_variations' ) ) return false;
	$available_variations=$product->get_available_variations();
	if ( isset( $available_variations[0]['attributes']['attribute_pa_p3d_printer'] ) && isset( $available_variations[0]['attributes']['attribute_pa_p3d_material'] ) && isset( $available_variations[0]['attributes']['attribute_pa_p3d_model'] ) && isset( $available_variations[0]['attributes']['attribute_pa_p3d_unit'] ) ) {
		wp_enqueue_style( '3dprint-frontend.css', plugin_dir_url( __FILE__ ).'css/3dprint-frontend.css' );
		wp_enqueue_style( 'component.css', plugin_dir_url( __FILE__ ).'ext/ProgressButtonStyles/css/component.css' );
		wp_enqueue_style( 'nouislider.min.css', plugin_dir_url( __FILE__ ).'ext/noUiSlider/nouislider.min.css' );
		wp_enqueue_style( 'easyaspie-main.css', plugin_dir_url( __FILE__ ).'ext/easyaspie/assets/css/main.css' );
		wp_enqueue_script( 'modernizr.custom.js',  plugin_dir_url( __FILE__ ).'ext/ProgressButtonStyles/js/modernizr.custom.js', array( 'jquery' ) );
		wp_enqueue_script( 'jsc3d.js',  plugin_dir_url( __FILE__ ).'ext/jsc3d/jsc3d.js' );
		wp_enqueue_script( 'jsc3d.touch.js',  plugin_dir_url( __FILE__ ).'ext/jsc3d/jsc3d.touch.js' );
		wp_enqueue_script( 'jsc3d.console.js',  plugin_dir_url( __FILE__ ).'ext/jsc3d/jsc3d.console.js' );
		wp_enqueue_script( 'jsc3d.webgl.js',  plugin_dir_url( __FILE__ ).'ext/jsc3d/jsc3d.webgl.js' );
		wp_enqueue_script( 'plupload.full.min.js',  plugin_dir_url( __FILE__ ).'ext/plupload/plupload.full.min.js' );
		wp_enqueue_script( 'classie.js',  plugin_dir_url( __FILE__ ).'ext/ProgressButtonStyles/js/classie.js' );
		wp_enqueue_script( 'progressButton.js',  plugin_dir_url( __FILE__ ).'ext/ProgressButtonStyles/js/progressButton.js' );
		wp_enqueue_script( 'event-manager.js',  plugin_dir_url( __FILE__ ).'ext/event-manager/event-manager.js' );
		wp_enqueue_script( 'accounting.js',  plugin_dir_url( __FILE__ ).'ext/accounting/accounting.min.js' );
		wp_enqueue_script( 'nouislider.min.js',  plugin_dir_url( __FILE__ ).'ext/noUiSlider/nouislider.min.js' );
		wp_enqueue_script( 'easyaspie.superfish.js',  plugin_dir_url( __FILE__ ).'ext/easyaspie/assets/js/superfish.js' );
		wp_enqueue_script( 'easyaspie.js',  plugin_dir_url( __FILE__ ).'ext/easyaspie/assets/js/easyaspie.js' );
		wp_enqueue_script( '3dprint-frontend.js',  plugin_dir_url( __FILE__ ).'js/3dprint-frontend.js' );

		$plupload_langs=array( 'ku_IQ', 'pt_BR', 'sr_RS', 'th_TH', 'uk_UA', 'zh_CN', 'zh_TW' );
		$current_locale = get_locale() ;
		list ( $lang, $LANG ) = explode( '_', $current_locale );
		if ( in_array( $current_locale, $plupload_langs ) ) $plupload_locale=$current_locale;
		else $plupload_locale=$lang;

		wp_enqueue_script( "$plupload_locale.js",  plugin_dir_url( __FILE__ )."ext/plupload/i18n/$plupload_locale.js" );

		$settings=get_option( '3dp_settings' );

		$min_price = $product->price;

		$upload_dir = wp_upload_dir();
		wp_localize_script( 'jquery', 'p3d',
			array(
				'url' => admin_url( 'admin-ajax.php' ),
				'upload_url' => $upload_dir['baseurl'].'/p3d/',
				'plugin_url' => plugin_dir_url( dirname( __FILE__ ) ),
				'error_box_fit' => __( '<span id=\'printer_fit_error\'><b>Error:</b> The model does not fit into the selected printer</span>', '3dprint' ),
				'warning_box_fit' => __( '<span id=\'printer_fit_warning\'><b>Warning:</b> The model might not fit into the selected printer</span>', '3dprint' ),
				'warning_cant_triangulate' => __( '<b>Warning:</b> Can\'t triangulate', '3dprint' ),
				'text_repairing_model' => __( 'Repairing model..', '3dprint' ),
				'text_model_repaired' => __( 'Repairing model.. done!', '3dprint' ),
				'text_model_repaired' => __( 'Repairing model.. fail!', '3dprint' ),
				'text_analysing_model' => __( 'Analysing model', '3dprint' ),
				'text_model_analysed' => __( 'Analysing model.. done!', '3dprint' ),
				'text_model_analyse_failed' => __( 'Analysing model.. fail!', '3dprint' ),
				'text_cant_process_obj' => __( 'Can\'t analyse obj files at the moment, please use the Request a Quote form.', '3dprint' ),
				'text_printer' => __( 'Printer', '3dprint' ),
				'text_material' => __( 'Material', '3dprint' ),
				'text_coating' => __( 'Coating', '3dprint' ),
				'text_infill' => __( 'Infill', '3dprint' ),
				'pricing' => $settings['pricing'],
				'attributes_layout' => $settings['attributes_layout'],
				'background1' => $settings['background1'],
				'background2' => $settings['background2'],
				'plane_color' => str_replace( '#', '0x', $settings['plane_color'] ),
				'printer_color' => str_replace( '#', '0x', $settings['printer_color'] ),
				'zoom' => $settings['zoom'],
				'angle_x' => $settings['angle_x'],
				'angle_y' => $settings['angle_y'],
				'angle_z' => $settings['angle_z'],
				'file_max_size' => $settings['file_max_size'],
				'file_extensions' => $settings['file_extensions'],
				'currency_symbol' => get_woocommerce_currency_symbol(),
				'currency_position' => get_option( 'woocommerce_currency_pos' ),
				'thousand_sep' => get_option( 'woocommerce_price_thousand_sep', ',' ),
				'decimal_sep' => get_option( 'woocommerce_price_decimal_sep', '.' ),
				'price_num_decimals' => wc_get_price_decimals(),
				'min_price' => $product->price,
//				'api_repair' => $settings['api_repair'],
				'api_repair' => '',
				'api_analyse' => $settings['api_analyse'],
				'cookie_expire' => $settings['cookie_expire']
			)
		);

		$custom_css = "
			.progress-button[data-perspective] .content { 
			 	background: ".$settings['button_color1'].";
			}

			.progress-button .progress { 
				background: ".$settings['button_color2']."; 
			}

			.progress-button .progress-inner { 
				background: ".$settings['button_color3']."; 
			}
			.progress-button {
				color: ".$settings['button_color4'].";
			}
			.progress-button .content::before,
			.progress-button .content::after  {
				color: ".$settings['button_color5'].";
			}
		";
		wp_add_inline_style( 'component.css', $custom_css );
	}
}


add_action( 'admin_init', 'p3d_plugin_redirect' );
function p3d_plugin_redirect() {
	if ( get_option( 'p3d_do_activation_redirect', false ) ) {
		delete_option( 'p3d_do_activation_redirect' );
		if ( !isset( $_GET['activate-multi'] ) ) {
			wp_redirect( admin_url( 'admin.php?page=3dprint' ) );exit;
		}
	}
}
add_action( 'woocommerce_order_item_line_item_html', 'p3d_order_item_html' );
function p3d_order_item_html( $item_id ) {
	$order = wc_get_order( $_GET['post'] );
        if ( !is_object($order) || !method_exists($order, 'get_item_meta') ) return false;
	$item_meta = $order->get_item_meta( $item_id );
	$upload_dir = wp_upload_dir();
	if ( !empty( $item_meta['pa_p3d_model'][0] ) ) {
		$p3dmodel=$item_meta['pa_p3d_model'][0];

		$link = $upload_dir['baseurl']."/p3d/$p3dmodel";
		$image = $link.".png";
		if (file_exists($upload_dir['basedir']."/p3d/$p3dmodel.zip")) {
			$p3dmodel_file = "$p3dmodel.zip";
			$link = $upload_dir['baseurl']."/p3d/$p3dmodel_file";
		}
		else $p3dmodel_file = $p3dmodel;

		$p3dmodel_file = urldecode($p3dmodel_file);
		$original_file = str_replace('_resized','', $p3dmodel_file);
		if (strstr($original_file,'_fixed')) {
			$original_file = str_replace('_fixed','', $original_file);
			$original_file = str_replace('.obj','.stl', $original_file);
		}
		$original_link = $upload_dir['baseurl']."/p3d/".urlencode($original_file);
		if (strstr($p3dmodel_file, '_fixed') && file_exists($upload_dir['basedir']."/p3d/".$original_file)) {
			echo '<tr><td></td><td><img width="50" src="'.$image.'"></td><td>'.__( '<b>Download repaired:</b>', '3dprint' ).' <a target="_blank" href="'.$link.'">'.urldecode($p3dmodel_file).'</a></td></tr>';
			echo '<tr><td></td><td><img width="50" src="'.$image.'"></td><td>'.__( '<b>Download original:</b>', '3dprint' ).' <a target="_blank" href="'.$original_link.'">'.urldecode($original_file).'</a></td></tr>';
		}
		else {
			echo '<tr><td></td><td><img width="50" src="'.$image.'"></td><td>'.__( '<b>Download:</b>', '3dprint' ).' <a target="_blank" href="'.$link.'">'.urldecode($p3dmodel_file).'</a></td></tr>';
		}



		if (file_exists($upload_dir['basedir']."/p3d/$original_file.zip")) {
			$p3dmodel_file = "$original_file.zip";
			$link = $upload_dir['baseurl']."/p3d/".urlencode($p3dmodel_file);
			echo '<tr><td></td><td></td><td>'.__( '<b>Zip File:</b>', '3dprint' ).' <a target="_blank" href="'.$link.'">'.urldecode($p3dmodel_file).'</a> '.__('(Replace the model inside the archive with the resized file above)', '3dprint').'</td></tr>';
		}
	}
}


function p3d_deactivate() {
	global $wpdb;
/*
	$postlist = get_posts(array('post_type'  => 'product'));
	foreach ( $postlist as $post ) {
		if ( p3d_is_p3d( $post->ID ) ) p3d_delete_p3d( $post->ID );
	}
*/

	do_action( '3dprint_deactivate' );
}

function p3d_delete_p3d( $post_id ) {
	$children = get_posts( array(
			'post_parent'  => $post_id,
			'posts_per_page'=> -1,
			'post_type'  => 'product_variation',
			'fields'   => 'ids',
			'post_status' => 'publish'
		) );
	if ( count( $children ) ) {
		foreach ( $children as $child_id ) {
			$child_meta=get_post_meta( $child_id );
			if ( isset( $child_meta['attribute_pa_p3d_printer'] ) && isset( $child_meta['attribute_pa_p3d_material'] ) && isset( $child_meta['attribute_pa_p3d_model'] ) && isset( $child_meta['attribute_pa_p3d_model'] ) && isset( $child_meta['attribute_pa_p3d_unit'] ) ) {
				wp_delete_post( $child_id );
			}
		}
	}
}

function p3d_is_p3d( $post_id ) {
	$children = get_posts( array(
			'post_parent'  => $post_id,
			'posts_per_page'=> -1,
			'post_type'  => 'product_variation',
			'fields'   => 'ids',
			'post_status' => 'publish'
		) );

	if ( count( $children ) ) {

		foreach ( $children as $child_id ) {
			$child_meta=get_post_meta( $child_id );
			if ( isset( $child_meta['attribute_pa_p3d_printer'] ) && isset( $child_meta['attribute_pa_p3d_material'] ) && isset( $child_meta['attribute_pa_p3d_model'] ) && isset( $child_meta['attribute_pa_p3d_model'] ) && isset( $child_meta['attribute_pa_p3d_unit'] ) ) {
				return true;
			}
		}
	}
	return false;
}


add_filter( 'product_type_options', 'p3d_product_type_options' );
function p3d_product_type_options( $options ) {

	if ( isset( $_REQUEST['post'] ) ) {
		$post_id=(int)$_REQUEST['post'];
		if ( p3d_is_p3d( $post_id ) ) $default='yes';
		else $default='no';
	}
	else $default='no';

	$new_option=array( '3dprinting' => array(
			'id'            => '_3dprinting',
			'wrapper_class' => 'show_if_variable',
			'label'         => __( '3D Printing Product', '3dprint' ),
			'description'   => __( '3D Printing Product', '3dprint' ),
			'default'       => $default
		) );
	$options['3dprinting']=$new_option['3dprinting'];
	return $options;
}

//3D printing product checked
function p3d_save_post( $post_id ) {

	if ( wp_is_post_revision( $post_id ) )
		return;
	if ( isset( $_POST['post_ID'] ) && $_POST['post_ID']==$post_id ) {
		if ( isset( $_POST['_3dprinting'] ) && $_POST['_3dprinting']=='on' ) {
			if ( p3d_is_p3d( $post_id ) ) return false;
			wp_set_object_terms( $post_id, 'all', 'pa_p3d_printer' , false );
			wp_set_object_terms( $post_id, 'all', 'pa_p3d_material' , false );
			wp_set_object_terms( $post_id, 'all', 'pa_p3d_coating' , false );
			wp_set_object_terms( $post_id, 'all', 'pa_p3d_model' , false );
			wp_set_object_terms( $post_id, 'all', 'pa_p3d_unit' , false );
			wp_set_object_terms( $post_id, 'all', 'pa_p3d_infill' , false );

			$attrs = array(
				'pa_p3d_printer'=>array(
					'name'=>'pa_p3d_printer',
					'value'=>'',
					'is_visible' => '0',
					'is_variation' => '1',
					'is_taxonomy' => '1'
				),
				'pa_p3d_material'=>array(
					'name'=>'pa_p3d_material',
					'value'=>'',
					'is_visible' => '0',
					'is_variation' => '1',
					'is_taxonomy' => '1'
				),
				'pa_p3d_coating'=>array(
					'name'=>'pa_p3d_coating',
					'value'=>'',
					'is_visible' => '0',
					'is_variation' => '1',
					'is_taxonomy' => '1'
				),

				'pa_p3d_model'=>array(
					'name'=>'pa_p3d_model',
					'value'=>'',
					'is_visible' => '0',
					'is_variation' => '1',
					'is_taxonomy' => '1'
				),
				'pa_p3d_unit'=>array(
					'name'=>'pa_p3d_unit',
					'value'=>'',
					'is_visible' => '0',
					'is_variation' => '1',
					'is_taxonomy' => '1'
				),
				'pa_p3d_infill'=>array(
					'name'=>'pa_p3d_infill',
					'value'=>'',
					'is_visible' => '0',
					'is_variation' => '1',
					'is_taxonomy' => '1'
				)
			);

			update_post_meta( $post_id, '_product_attributes', $attrs );
			update_post_meta( $post_id, '_price', '1' );
			update_post_meta( $post_id, '_visibility', 'visible' );
			update_post_meta( $post_id, '_stock_status', 'instock' );

			$new_post = array(
				'post_title'=> "Variation #".( $post_id+1 )." of $post_id",
				'post_name' => 'product-' . $post_id . '-variation',
				'post_status' => 'publish',
				'post_parent' => $post_id,
				'post_type' => 'product_variation',
				'guid'=>home_url() . '/?product_variation=product-' . $post_id . '-variation'
			);
			$variation_id = wp_insert_post( $new_post );

			update_post_meta( $post_id, '_min_regular_price_variation_id', $variation_id );
			update_post_meta( $post_id, '_max_regular_price_variation_id', $variation_id );
			update_post_meta( $post_id, '_min_price_variation_id', $variation_id );
			update_post_meta( $post_id, '_max_price_variation_id', $variation_id );

			update_post_meta( $post_id, '_min_variation_price', 1 );
			update_post_meta( $post_id, '_max_variation_price', 1 );
			update_post_meta( $post_id, '_min_variation_regular_price', 1 );
			update_post_meta( $post_id, '_max_variation_regular_price', 1 );

			update_post_meta( $variation_id, '_price', '1' );
			update_post_meta( $variation_id, '_regular_price', '1' );
			update_post_meta( $variation_id, '_stock_status', 'instock' );

			update_post_meta( $variation_id, 'attribute_pa_p3d_printer', '' );
			update_post_meta( $variation_id, 'attribute_pa_p3d_material', '' );
			update_post_meta( $variation_id, 'attribute_pa_p3d_coating', '' );
			update_post_meta( $variation_id, 'attribute_pa_p3d_model', '' );
			update_post_meta( $variation_id, 'attribute_pa_p3d_unit', '' );
			update_post_meta( $variation_id, 'attribute_pa_p3d_infill', '' );

			wp_set_object_terms( $variation_id, '1', 'pa_p3d_printer' , false );
			wp_set_object_terms( $variation_id, '1', 'pa_p3d_material' , false );
			wp_set_object_terms( $variation_id, '1', 'pa_p3d_coating' , false );
			wp_set_object_terms( $variation_id, '1', 'pa_p3d_model' , false );
			wp_set_object_terms( $variation_id, '1', 'pa_p3d_unit' , false );
			wp_set_object_terms( $variation_id, '1', 'pa_p3d_infill' , false );

			wp_update_post( array( 'ID'=>$post_id, 'post_status'=>'publish' ) );
		}
		else if ( p3d_is_p3d( $post_id ) ) {
				p3d_delete_p3d( $post_id );
			}
	}

}
add_action( 'save_post', 'p3d_save_post' );

function p3d_unassigned_warning() {
	$class = 'notice notice-error is-dismissible';

	$unassigned_materials = p3d_get_unassigned_materials(p3d_get_option('3dp_printers'), p3d_get_option('3dp_materials'));
	if (count($unassigned_materials) > 0) {
		$message = sprintf(__( 'You have %s unassigned materials. They will not be displayed on the frontend.', '3dprint' ), count($unassigned_materials));
		printf( '<div class="%1$s"><b>3DPrint</b><p>%2$s</p></div>', $class, $message ); 
	}
}
add_action( 'admin_notices', 'p3d_unassigned_warning' );

function p3d_get_unassigned_materials($db_printers, $db_materials) {
	$assigned_materials = array();
	foreach ($db_printers as $printer) {
		if ($printer['materials']=='') {
			return array(); //all assigned
		}

		$assigned_materials = array_merge($assigned_materials, explode(',', $printer['materials']));
		
	}

	$unassigned_materials = array_diff(array_keys($db_materials), $assigned_materials );
	return $unassigned_materials;
}

function p3d_get_assigned_materials($db_printers, $db_materials) {
	$assigned_materials = array();
	foreach ($db_printers as $printer) {
		if ($printer['materials']=='') {
			return array_keys($db_materials); //all assigned
		}

		$assigned_materials = array_merge($assigned_materials, explode(',', $printer['materials']));
		
	}

	return $assigned_materials;
}




function p3d_clear_cookies() {
	if ( count( $_COOKIE ) ) {
		foreach ( $_COOKIE as $key=>$value ) {
			if ( strpos( $key, 'p3d' )===0 ) {
				setcookie( $key, "", time()-3600*24*30 );
			}
		}
	}
}


function p3d_init() {
	global $woocommerce_loop, $woocommerce;
	WC()->session->set_customer_session_cookie( true );
	return true;
}

if (isset($_POST['action']) && $_POST['action'] == 'editedtag') {
$p3d_attr_prices = get_option('3dp_attr_prices');
	if (isset($_POST['p3d_attr_prices']) && count($_POST['p3d_attr_prices'])>0) {
		foreach($_POST['p3d_attr_prices'] as $taxonomy_name => $taxonomy_data) {
			$p3d_attr_prices[$taxonomy_name][$_POST['slug']]['price'] = $taxonomy_data['price'];
			$p3d_attr_prices[$taxonomy_name][$_POST['slug']]['price_type'] = $taxonomy_data['price_type'];
			$p3d_attr_prices[$taxonomy_name][$_POST['slug']]['pct_type'] = $taxonomy_data['pct_type'];
		}
		update_option('3dp_attr_prices', $p3d_attr_prices);
	}
}


add_action( 'edit_tag_form_fields', 'p3d_edit_tag_form_fields' );
function p3d_edit_tag_form_fields($tag) {
	if ($_GET['post_type']=='product') {
		$p3d_attr_prices=get_option('3dp_attr_prices');
?>
		<tr class="form-field term-slug-wrap">
			<th scope="row"><label><?php _e('Price mod.' ,'3dprint');?></label></th>
			<td>
				<input type="text" style="width:100px;" size="4" value="<?php echo ( isset($p3d_attr_prices[$_GET['taxonomy']][$tag->slug]) ? $p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['price'] : "" ) ;?>" name="p3d_attr_prices[<?php echo $_GET['taxonomy'];?>][price]">
				<select onchange="if(this.value=='pct') jQuery('#pct_type').css('visibility', 'visible'); else jQuery('#pct_type').css('visibility', 'hidden');" name="p3d_attr_prices[<?php echo $_GET['taxonomy'];?>][price_type]">
					<option <?php if (isset($p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['price_type']) && $p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['price_type'] == 'flat') echo 'selected';?> value="flat"><?php echo get_woocommerce_currency_symbol();?>
					<option <?php if (isset($p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['price_type']) && $p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['price_type'] == 'pct') echo 'selected';?> value="pct">%
				</select> 
				<div id="pct_type" style="margin-left:10px;display:inline;<?php if ($p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['price_type'] != 'pct') echo 'visibility:hidden;';  ?>">
					<?php _e('of', '3dprint');?>
					<select name="p3d_attr_prices[<?php echo $_GET['taxonomy'];?>][pct_type]">
						<option <?php if (isset($p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['pct_type']) && $p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['pct_type'] == 'total') echo 'selected';?> value="total"><?php _e('Total Price');?>
						<option <?php if (isset($p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['pct_type']) && $p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['pct_type'] == 'printer') echo 'selected';?> value="printer"><?php _e('Printer Price');?>
						<option <?php if (isset($p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['pct_type']) && $p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['pct_type'] == 'material') echo 'selected';?> value="material"><?php _e('Material Price');?>
						<option <?php if (isset($p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['pct_type']) && $p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['pct_type'] == 'coating') echo 'selected';?> value="coating"><?php _e('Coating Price');?>
						<option <?php if (isset($p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['pct_type']) && $p3d_attr_prices[$_GET['taxonomy']][$tag->slug]['pct_type'] == 'material_amount') echo 'selected';?> value="material_amount"><?php _e('Material Amount');?>
					</select>
				</div>
			</td>
		</tr>

<?php	
	}
}


add_action( 'admin_enqueue_scripts', 'p3d_add_color_picker' );
function p3d_add_color_picker( $hook ) {

	if ( is_admin() ) {
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'custom-script-handle', plugins_url( 'js/3dprint-backend.js', __FILE__ ), array( 'wp-color-picker' ), false, true );
	}
}

function p3d_sort_by_order($a, $b) {
	return $a['sort_order'] - $b['sort_order'];
}

function p3d_sort_by_group_order ($array) {
	$sort = array();
	foreach($array as $key=>$value) {
		$sort['group_name'][$key] = $value['group_name'];
		$sort['sort_order'][$key] = $value['sort_order'];
	}

	array_multisort($sort['group_name'], SORT_ASC, $sort['sort_order'], SORT_ASC, $array);
	return $array;
}

add_filter( 'wc_get_template', 'p3d_get_template', 10, 2 );
function p3d_get_template( $located, $template_name ) {
	$p3d_templates=array( 'single-product/price.php', 'single-product/product-image.php', 'single-product/add-to-cart/variable.php' );

	if ( p3d_is_p3d( get_the_ID() ) ) {
		if ( in_array( $template_name, $p3d_templates ) && !strstr( $located, '3dprint' ) ) {
			$p3d_dir = p3d_plugin_path();
			$located = $p3d_dir."/woocommerce/$template_name";
		}
	}
	return $located;
}

add_filter( 'woocommerce_locate_template', 'p3d_woocommerce_locate_template', 10, 3 );
function p3d_woocommerce_locate_template( $template, $template_name, $template_path ) {
	$_template = $template;

	if ( p3d_is_p3d( get_the_ID() ) ) {
		if ( ! $template_path ) $template_path = $woocommerce->template_url;
		$plugin_path  = p3d_plugin_path() . '/woocommerce/';

		$template = locate_template(
			array(
				$template_path . $template_name,
				$template_name
			)
		);

		if ( ! $template && file_exists( $plugin_path . $template_name ) )
			$template = $plugin_path . $template_name;
	}
	else {

	}

	if ( ! $template )
		$template = $_template;

	return $template;
}

function p3d_plugin_path() {
	return untrailingslashit( dirname( plugin_dir_path( __FILE__ ) ) );
}


add_action( 'template_redirect', 'p3d_redirect' );
function p3d_redirect() {
	p3d_init();
	if ( isset( $_GET['p3d_buynow'] ) && $_GET['p3d_buynow']=='1' ) {
		foreach ( $_GET as $key=>$value ) {
			if ( $key!='p3d_buynow' ) $variation[$key]=$value;
		}
		$product = new WC_Product_Variable( get_the_ID() );
		$available_variations=$product->get_available_variations();
		WC()->cart->add_to_cart( get_the_ID(), 1, $available_variations[0]['variation_id'], $variation );
		p3d_clear_cookies();

		wp_safe_redirect( WC()->cart->get_cart_url() );
		exit;
	}
	else if ( isset($_POST['action']) && $_POST['action']=='request_price' ) {
		p3d_clear_cookies();
	}
}

function p3d_save_thumbnail( $data, $filename ) {
	$link = '';
	if ( !empty($data) ) {
		$new_filename=$filename.'.png';
		$upload_dir = wp_upload_dir();
		$file_path=$upload_dir['basedir'].'/p3d/'.$new_filename;
		file_put_contents( $file_path, base64_decode( $data ) );
		$link = $upload_dir['baseurl'].'/p3d/'.$new_filename;
	}
	return $link;
}

//Show the screenshot of the product
add_filter( 'woocommerce_cart_item_thumbnail', 'p3d_cart_item_thumbnail', 10, 3 );
function p3d_cart_item_thumbnail( $img, $cart_item, $cart_item_key ) {
	if ( isset( $cart_item['3dp_options'] ) && is_array( $cart_item['3dp_options'] ) && !empty( $cart_item['3dp_options']['model_name'] ) ) {
		$upload_dir = wp_upload_dir();
		$file_path=$upload_dir['basedir'].'/p3d/'.$cart_item['3dp_options']['model_name'].'.png';

		if ( file_exists( $file_path ) ) {
			$link = $upload_dir['baseurl'].'/p3d/'.urlencode($cart_item['3dp_options']['model_name']).'.png';
			$img = preg_replace( '@src="([^"]+)"@' , 'style="width:100px;" src="'.$link.'"', $img );
		}
	}
	return $img;
}

//price for mini-cart, etc
add_filter( 'woocommerce_cart_item_price', 'p3d_cart_item_price', 10, 3 );
function p3d_cart_item_price( $price, $cart_item, $cart_item_key ) {
	if ( isset( $cart_item['3dp_options'] ) && is_array( $cart_item['3dp_options'] ) && !empty( $cart_item['3dp_options']['product-price'] ) ) {
		$price=$cart_item['3dp_options']['product-price'];
	}

	return $price;
}


add_action( 'woocommerce_before_calculate_totals', 'p3d_add_custom_price' );
function p3d_add_custom_price( $cart_object ) {
	$settings = get_option('3dp_settings');
	foreach ( $cart_object->cart_contents as $key => $value ) {
		if ( isset( $value['3dp_options']['product-price'] ) ) $value['data']->price=$value['3dp_options']['product-price'];
		if ( isset( $value['3dp_options']['printer_name'] ) ) $cart_object->cart_contents[$key]['variation']['attribute_pa_p3d_printer']=$value['3dp_options']['printer_name'];
		if ( isset( $value['3dp_options']['infill'] ) ) $cart_object->cart_contents[$key]['variation']['attribute_pa_p3d_infill']=$value['3dp_options']['infill'];
		if ( isset( $value['3dp_options']['material_name'] ) ) $cart_object->cart_contents[$key]['variation']['attribute_pa_p3d_material']=$value['3dp_options']['material_name'];
		if ( isset( $value['3dp_options']['coating_name'] ) ) $cart_object->cart_contents[$key]['variation']['attribute_pa_p3d_coating']=$value['3dp_options']['coating_name'];
		if ( isset( $value['3dp_options']['model_name'] ) ) $cart_object->cart_contents[$key]['variation']['attribute_pa_p3d_model']=urlencode($value['3dp_options']['model_name']);
		if ( ($settings['api_analyse'])!='on' ) unset ($cart_object->cart_contents[$key]['variation']['attribute_pa_p3d_infill']);

	}
}

add_filter( 'woocommerce_add_cart_item_data', 'p3d_add_cart_item_data', 10, 2 );
function p3d_add_cart_item_data( $cart_item_meta, $product_id ) {
	global $woocommerce;

	if ( isset( $_REQUEST['attribute_pa_p3d_material'] ) && isset( $_REQUEST['attribute_pa_p3d_printer'] ) && isset( $_REQUEST['attribute_pa_p3d_model'] ) && isset( $_REQUEST['attribute_pa_p3d_unit'] ) ) {
		$thumbnail_data=$_REQUEST['p3d_thumb'];
		$thumbnail_url=p3d_save_thumbnail( $thumbnail_data, $_REQUEST['attribute_pa_p3d_model'] );

		$material_id=(int)$_REQUEST['attribute_pa_p3d_material'];
		if (is_numeric($_REQUEST['attribute_pa_p3d_coating'])) $coating_id=(int)$_REQUEST['attribute_pa_p3d_coating'];
		else $coating_id="";
		$printer_id=(int)$_REQUEST['attribute_pa_p3d_printer'];
		$materials_array = p3d_get_option( '3dp_materials' );
		$material=$materials_array[$material_id];
		$scale = (float)$_REQUEST['p3d_resize_scale'];
		if (empty($scale)) $scale = 1;

		$infill=(int)$_REQUEST['attribute_pa_p3d_infill'];

		$coatings_array = p3d_get_option( '3dp_coatings' );
		if (is_numeric($coating_id)) $coating=$coatings_array[$coating_id];
		if ($_REQUEST['attribute_pa_p3d_unit']=='inch')
			$unit='inch';
		else
			$unit='mm';

		$printers_array = p3d_get_option( '3dp_printers' );
		$printer=$printers_array[$printer_id];

		$model_file=p3d_basename($_REQUEST['attribute_pa_p3d_model']);

		if ( $thumbnail_url ) $cart_item_meta['3dp_options']['thumbnail']=$thumbnail_url;
		$cart_item_meta['3dp_options']['printer_name']=$printer_id.'.'.str_replace( array( '&', '?', '#', '/', '\\' ), '_', strip_tags( __($printer['name'], '3dprint') ) );
		$cart_item_meta['3dp_options']['infill']=$infill.'%';
		$cart_item_meta['3dp_options']['material_name']=$material_id.'.'.str_replace( array( '&', '?', '#', '/', '\\' ), '_', strip_tags( __($material['name'], '3dprint') ) );
		if (isset($coating) && count($coating)>0) $cart_item_meta['3dp_options']['coating_name']=$coating_id.'.'.str_replace( array( '&', '?', '#', '/', '\\' ), '_', strip_tags( __($coating['name'], '3dprint') ) );
		$cart_item_meta['3dp_options']['model_name']=str_replace( array( '&', '?', '#', '/', '\\' ), '_',  p3d_basename( $model_file ) );

		if ( isset( $_GET['p3d_buynow'] ) && $_GET['p3d_buynow']==1 ) {
			$p3d_price_requests=p3d_get_option( '3dp_price_requests' );
			$product_key=$product_id.'_'.$printer_id.'_'.$material_id.'_'.$coating_id.'_'.$infill.'_'.base64_encode( p3d_basename( $model_file ) );

			$price_error=false;
			foreach ( $_GET as $key=>$value ) {
				if ( strstr( $key, 'attribute' ) && $p3d_price_requests[$product_key]['attributes'][$key]!=$value ) $price_error=true;
			}
			if ( $price_error ) die( __( 'Attribute error' , '3dprint' ) );
			$p3d_price=$p3d_price_requests[$product_key]['price'];
		}
		else {
			$product_key=$product_id.'_'.$printer_id.'_'.$material_id.'_'.$coating_id.'_'.$infill.'_'.base64_encode( p3d_basename( $model_file ) );
			$upload_dir = wp_upload_dir();
			$file_path=$upload_dir['basedir'].'/p3d/'.p3d_basename( $model_file );
			if ( !file_exists( $file_path ) ) return false;

			$model_stats['model'] = p3d_get_model_stats( $file_path, $unit, $scale, $printer_id, $infill );
			$path_parts = pathinfo($model_file);
			$resized_file_path = $upload_dir['basedir'].'/p3d/'.$path_parts['filename'].'_resized.'.$path_parts['extension'];

			if (file_exists($resized_file_path)) {
				$model_file=p3d_basename($resized_file_path);
				$cart_item_meta['3dp_options']['model_name']=str_replace( array( '&', '?', '#', '/', '\\' ), '_',  p3d_basename( $model_file ) );
				copy($upload_dir['basedir'].'/p3d/'.$path_parts['filename'].'.'.$path_parts['extension'].'.png', $resized_file_path.'.png');

			}

			$printing_price = p3d_calculate_printing_cost( $printer_id, $material_id, $coating_id, $model_stats, $_REQUEST );

			$printing_price = round( $printing_price, 2 );


			$product = new WC_Product( $product_id );
			$min_price = $product->price;

			if ( $printing_price>$min_price ) {
				$p3d_price = $printing_price;
			}
			else {
				$p3d_price = $min_price;
			}

		}

		$cart_item_meta['3dp_options']['product-price'] = $p3d_price;
	}
	return $cart_item_meta;
}

add_action( 'woocommerce_add_to_cart', 'p3d_add_to_cart' );
function p3d_add_to_cart( $cart_item_key ) {
	$product=WC()->cart->cart_contents[$cart_item_key];
	$variation=$product['variation'];
	$settings=p3d_get_option( '3dp_settings' );
	if ( isset( $variation['attribute_pa_p3d_printer'] ) && isset( $variation['attribute_pa_p3d_material'] ) && isset( $variation['attribute_pa_p3d_model'] ) ) {
		p3d_clear_cookies();
	}

}



function p3d_query_string( $params, $name=null ) {
	$ret = "";
	foreach ( $params as $key=>$val ) {
		if ( is_array( $val ) ) {
			if ( $name==null ) $ret .= queryString( $val, $key );
			else $ret .= queryString( $val, $name."[$key]" );
		} else {
			if ( $name!=null )
				$ret.=$name."[$key]"."=$val&";
			else $ret.= "$key=$val&";
		}
	}
	return $ret;
}


function p3d_basename($file) {
    return end(explode('/',$file));
} 

if ( ! wp_next_scheduled( 'p3d_housekeeping' ) ) {
  wp_schedule_event( time(), 'daily', 'p3d_housekeeping' );
}

add_action( 'p3d_housekeeping', 'p3d_do_housekeeping' );
function p3d_do_housekeeping() {
	$uploads = wp_upload_dir( 'p3d' );
	$files = glob($uploads['path']."*");
	$now   = time();
	$settings = p3d_get_option( '3dp_settings' );
	if ((int)$settings['file_max_days']>0) {
		foreach ($files as $file) {
			$filename = p3d_basename($file);
			if (is_file($file) && $filename != '.htaccess' && $filename != 'index.html') {
				if ($now - filemtime($file) >= 60 * 60 * 24 * $settings['file_max_days']) {
					unlink($file);
				}
			}
		}
	}
}


function p3d_find_all_files($dir) {
	$root = scandir($dir);
	foreach($root as $value) {
	if($value === '.' || $value === '..') {continue;}
		if(is_file("$dir/$value")) {$result[]="$dir/$value";continue;}
		foreach(p3d_find_all_files("$dir/$value") as $value) {
			$result[]=$value;
		}
	}
return $result;
} 

function p3d_handle_upload() {

	error_reporting( 0 );
	set_time_limit( 5*60 );
	ini_set( 'memory_limit', '-1' );
	$allowed_extensions=array('stl', 'obj', 'mtl', 'png', 'jpg', 'jpeg', 'gif', 'tga', 'bmp');

	$product_id = (int)$_REQUEST['product_id'];
	$printer_id = (int)$_REQUEST['printer_id'];
	$material_id = (int)$_REQUEST['material_id'];

	if (is_numeric($_REQUEST['coating_id'])) {
		$coating_id = (int)$_REQUEST['coating_id'];
	}
	else {
		$coating_id = '';
	}

	if ( $_REQUEST['unit'] == 'inch' ) {
		$unit = "inch";
	}
	else {
		$unit = "mm";
	}
	$model_stats = array();
	$settings = p3d_get_option( '3dp_settings' );


	$targetDir = get_temp_dir();

	$cleanupTargetDir = true; // Remove old files
	$maxFileAge = 5 * 3600; // Temp file age in seconds


	// Create target dir
	if ( !file_exists( $targetDir ) ) {
		@mkdir( $targetDir );
	}

	// Get a file name
	if ( isset( $_REQUEST["name"] ) ) {
		$fileName = $_REQUEST["name"];
	} elseif ( !empty( $_FILES ) ) {
		$fileName = $_FILES["file"]["name"];
	} else {
		$fileName = uniqid( "file_" );
	}
	//$fileName = sanitize_file_name( $fileName );

	$fileName = str_replace('_fixed','',$fileName);
	$fileName = str_replace('_resized','',$fileName);
	$filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;

	// Chunking might be enabled
	$chunk = isset( $_REQUEST["chunk"] ) ? intval( $_REQUEST["chunk"] ) : 0;
	$chunks = isset( $_REQUEST["chunks"] ) ? intval( $_REQUEST["chunks"] ) : 0;


	// Remove old temp files
	if ( $cleanupTargetDir ) {
		if ( !is_dir( $targetDir ) || !$dir = opendir( $targetDir ) ) {
			die( '{"jsonrpc" : "2.0", "error" : {"code": 100, "message": '.__( "Failed to open temp directory.", '3dprint' ).'}, "id" : "id"}' );
		}

		while ( ( $file = readdir( $dir ) ) !== false ) {
			$tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;

			// If temp file is current file proceed to the next
			if ( $tmpfilePath == "{$filePath}.part" ) {
				continue;
			}

			// Remove temp file if it is older than the max age and is not the current file
			if ( preg_match( '/\.part$/', $file ) && ( filemtime( $tmpfilePath ) < time() - $maxFileAge ) ) {
				@unlink( $tmpfilePath );
			}
		}
		closedir( $dir );
	}


	// Open temp file
	if ( !$out = @fopen( "{$filePath}.part", $chunks ? "ab" : "wb" ) ) {
		die( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "'.__( 'Failed to open output stream.', '3dprint' ).'"}, "id" : "id"}' );
	}

	if ( !empty( $_FILES ) ) {
		if ( $_FILES["file"]["error"] || !is_uploaded_file( $_FILES["file"]["tmp_name"] ) ) {
			die( '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "'.__( 'Failed to move uploaded file.', '3dprint' ).'"}, "id" : "id"}' );
		}

		// Read binary input stream and append it to temp file
		if ( !$in = @fopen( $_FILES["file"]["tmp_name"], "rb" ) ) {
			die( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "'.__( 'Failed to open input stream.', '3dprint' ).'"}, "id" : "id"}' );
		}
	} else {
		if ( !$in = @fopen( "php://input", "rb" ) ) {
			die( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "'.__( 'Failed to open input stream.', '3dprint' ).'"}, "id" : "id"}' );
		}
	}

	while ( $buff = fread( $in, 4096 ) ) {
		fwrite( $out, $buff );
	}

	@fclose( $out );
	@fclose( $in );

	// Check if file has been uploaded
	if ( !$chunks || $chunk == $chunks - 1 ) {
		// Strip the temp .part suffix off

		rename( "{$filePath}.part", $filePath );


		$uploads = wp_upload_dir( 'p3d' );

		//$wp_filename =  wp_unique_filename( $uploads['path'], urlencode(p3d_basename( $filePath ) ) );
		$wp_filename =  time().'_'.urlencode( p3d_basename( $filePath ) ) ;


		$new_file = $uploads['path'] . "$wp_filename";
		

		$path_parts = pathinfo($new_file);
		$extension = strtolower($path_parts['extension']);
		$basename = $path_parts['basename'];


		if ($extension=='zip') {
			if (class_exists('ZipArchive')) {

				$zip = new ZipArchive;
				$res = $zip->open( $filePath );
				if ( $res === TRUE ) {
					for( $i = 0; $i < $zip->numFiles; $i++ ) {
						$file_to_extract = p3d_basename( $zip->getNameIndex($i) );
						$f2e_path_parts = pathinfo($file_to_extract);
						$f2e_extension = mb_strtolower($f2e_path_parts['extension']);
						if (!in_array(mb_strtolower($f2e_path_parts['extension']), $allowed_extensions)) continue;
						if ( $f2e_extension == 'obj' || $f2e_extension == 'stl' ) {
							
							$file_found = true;
							$wp_filename =  time().'_'.urlencode( p3d_basename( $file_to_extract ) ) ;
							$file_to_extract = $wp_filename;
						}

						$zip->extractTo( "$targetDir/$wp_filename", array( $zip->getNameIndex($i) ) );
                                                $files = p3d_find_all_files("$targetDir/$wp_filename");
						foreach ($files as $filename) {
							rename($filename, $uploads['path'].$file_to_extract);
						}

					}

					$zip->close();
					if ( !$file_found ) {
						die( '{"jsonrpc" : "2.0", "error" : {"code": 104, "message": "'.__( 'Model file not found.', '3dprint' ).'"}, "id" : "id"}' );
					}
					rename($filePath, $uploads['path'].$wp_filename.'.zip');
				}
				else {
					die( '{"jsonrpc" : "2.0", "error" : {"code": 105, "message": "'.__( 'Could not extract the file.', '3dprint' ).'"}, "id" : "id"}' );
				}
			}
			else {
				die( '{"jsonrpc" : "2.0", "error" : {"code": 106, "message": "'.__( 'The server does not support zip archives.', '3dprint' ).'"}, "id" : "id"}' );
			}
		} elseif ($extension == 'stl' || $extension == 'obj') {
			rename( $filePath, $new_file );
		}

		$output['jsonrpc'] = "2.0";
		$output['filename'] = $wp_filename;

		if (filesize($uploads['path'].$wp_filename) > ((int)$settings['file_max_size'] * 1048576)) {
			unlink($uploads['path'].$wp_filename);
			die( '{"jsonrpc" : "2.0", "error" : {"code": 113, "message": "'.__( 'Extracted file is too large.', '3dprint' ).'"}, "id" : "id"}' );
		}

		$output = apply_filters( '3dprint_upload', $output, $product_id, $printer_id, $material_id, $coating_id );
		wp_die( json_encode( $output ) );

	}

}


function p3d_handle_repair() {
	error_reporting( 0 );
	set_time_limit( 5*60 );
	ini_set( 'memory_limit', '-1' );

	do_action('p3d_handle_repair_begin');

	$settings = p3d_get_option( '3dp_settings' );

	$servers = p3d_get_option( '3dp_servers' );
	shuffle($servers);
	$repair_url = $servers[0]."/repair.php";

	$uploads = wp_upload_dir( 'p3d' );

	$basename = p3d_basename( $_POST['filename'] );
	$file_to_upload = $uploads['path'] . $basename;
	$upload = true;

	//todo check extension

	if ( !file_exists ( $file_to_upload ) ) wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 107, "message": "'.__( 'The file does not exist.', '3dprint' ).'"}, "id" : "id"}' );

	if ( !function_exists('curl_version') ) wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 108, "message": "'.__( 'The server does not support curl.', '3dprint' ).'"}, "id" : "id"}' );

	//check if file already exists on a remote server
	$post = array( 'login' => $settings['api_login'], 'file_name' => $basename, 'file_key' => md5_file ( $file_to_upload ), 'check_existence' => 1);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "$server/check.php");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec ($ch);
	curl_close ($ch);

	if($errno = curl_errno($ch)) {
		$error_message = curl_strerror($errno);		
	}
	if ( $errno ) {
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 109, "message": "'.__( 'Error: '.$error_message, '3dprint' ).'"}, "id" : "id"}' );
	}
	$response = json_decode($result, true);
	if ($response['file_exists'] == '1') $upload = false;

	if ($upload) {
		if (class_exists('ZipArchive') && filesize($file_to_upload) >= 1048576 ) { 
			$zip_file = "$file_to_upload.tmp.zip";
			$zip = new ZipArchive();
			
			if ($zip->open($zip_file, ZipArchive::CREATE)!==TRUE) {
			    wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 112, "message": "'.__( 'Could not create a zip archive.', '3dprint' ).'"}, "id" : "id"}' );
			}
			$zip->addFile($file_to_upload, $basename);
			$zip->close();
			$file_to_upload = $zip_file;

		}
	}

	//wp_schedule_single_event
	$post = array( 'login' => $settings['api_login'], 'file_name' => $basename, 'file_key' => md5_file ( $file_to_upload ), 'file_contents'=>'@'.$file_to_upload );
	if (!$upload) unset($post['file_contents']);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$repair_url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec ($ch);
	curl_close ($ch);

	if($errno = curl_errno($ch)) {
		$error_message = curl_strerror($errno);		
	}
	if (file_exists($zip_file)) unlink($zip_file);
	if ( $errno ) {
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 109, "message": "'.__( 'Error: '.$error_message, '3dprint' ).'"}, "id" : "id"}' );
	}

	$response = json_decode($result, true);

	if ( $response['status']=='1' && !empty ( $response['url'] ) ) {
		//download repaired file

		$ch = curl_init($response['url']);
		$repaired_file = $response['filename']; 
		$fp = fopen($uploads['path'].$repaired_file, 'wb'); 
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);
		$output['jsonrpc'] = "2.0";
		$output['status'] = "1"; //completed
		$output['filename'] = $repaired_file;

		wp_die( json_encode( $output ) );
	}
	elseif ( $response['status']=='0' ) {
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 110, "message": "'.__( $response['message'], '3dprint' ).'"}, "id" : "id"}' );
	}
	else {
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 111, "message": "'.__( 'Unknown error.', '3dprint' ).'"}, "id" : "id"}' );
	}

}

function p3d_handle_analyse() {
	error_reporting( 0 );
	set_time_limit( 5*60 );
	ini_set( 'memory_limit', '-1' );

	do_action('p3d_handle_analyse_begin');

	$settings = p3d_get_option( '3dp_settings' );
	$servers = p3d_get_option( '3dp_servers' );
	shuffle($servers);
	$server = $servers[0];

	$api_url = $server."/analyse.php";
	$uploads = wp_upload_dir( 'p3d' );
	$basename = p3d_basename( $_POST['filename'] );
	$file_to_upload = $uploads['path'] . $basename;
	$layer_height = (float)$_POST['layer_height'];
	$wall_thickness = (float)$_POST['wall_thickness'];
	$nozzle_size = (float)$_POST['nozzle_size'];
	$filament_diameter = (float)$_POST['filament_diameter'];
	$infill = (int)$_POST['infill'];
	$cookie = WC()->session->get_session_cookie();
	$session_id = md5($_SERVER['REMOTE_ADDR'].$cookie[0]);
	$scale = (float)$_POST['scale'];
	$unit = $_POST['unit'];
	$upload = true;

	//todo check extension
	if ( !file_exists ( $file_to_upload ) ) wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 107, "message": "'.__( 'The file does not exist.', '3dprint' ).'"}, "id" : "id"}' );

	if ( !function_exists('curl_version') ) wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 108, "message": "'.__( 'The server does not support curl.', '3dprint' ).'"}, "id" : "id"}' );


	//check if file already exists on a remote server
	$post = array( 'login' => $settings['api_login'], 'file_name' => $basename, 'file_key' => md5_file ( $file_to_upload ), 'check_existence' => 1);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "$server/check.php");
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec ($ch);
	curl_close ($ch);

	if($errno = curl_errno($ch)) {
		$error_message = curl_strerror($errno);		
	}
	if ( $errno ) {
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 109, "message": "'.__( 'Error: '.$error_message, '3dprint' ).'"}, "id" : "id"}' );
	}
	$response = json_decode($result, true);
	if ($response['file_exists'] == '1') $upload = false;

	if ($upload) {
		if ( class_exists('ZipArchive') && filesize($file_to_upload) >= 1048576 ) {
			$zip_file = "$file_to_upload.tmp.zip";
			$zip = new ZipArchive();
			
			if ($zip->open($zip_file, ZipArchive::CREATE)!==TRUE) {
				wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 112, "message": "'.__( 'Could not create a zip archive.', '3dprint' ).'"}, "id" : "id"}' );
			}
			$zip->addFile($file_to_upload, $basename);
			$zip->close();
			$file_to_upload = $zip_file;

		}
	}

	//wp_schedule_single_event
	$post = array(  'login' => $settings['api_login'], 
			'subscription_login' => $settings['api_subscription_login'], 
			'subscription_key' => $settings['api_subscription_key'],
			'layer_height' => $layer_height,
			'wall_thickness' => $wall_thickness,
			'nozzle_size' => $nozzle_size,
			'filament_diameter' => $filament_diameter,
			'infill' => $infill,
			'file_name' => $basename, 
			'file_key' => md5_file ( $file_to_upload ), 
			'file_contents' => '@'.$file_to_upload, 
			'session_id' => $session_id,
			'scale' => $scale,
			'unit' => $unit
			);
	if (!$upload) unset($post['file_contents']);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$api_url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec ($ch);
	curl_close ($ch);

	if($errno = curl_errno($ch)) {
		$error_message = curl_strerror($errno);		
	}
	if (file_exists($zip_file)) unlink($zip_file);
	if ( $errno ) {
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 109, "message": "'.__( 'Error: '.$error_message, '3dprint' ).'"}, "id" : "id"}' );
	}

	$response = json_decode($result, true);


	if ( $response['status']=='2' ) {
		//analyse process is slow, it goes to background and we retrieve the status later with p3d_handle_check
		$output['jsonrpc'] = "2.0";
		$output['status'] = $response['status']; //2 - in progress
		$output['server'] = $server;
		wp_die( json_encode( $output ) );
	}
	elseif ( $response['status']=='0' ) {
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 110, "message": "'.__( $response['message'], '3dprint' ).'"}, "id" : "id"}' );
	}
	else {
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 111, "message": "'.__( 'Unknown error.', '3dprint' ).'"}, "id" : "id"}' );
	}


}

function p3d_handle_analyse_check() {
	error_reporting( 0 );
	set_time_limit( 5*60 );
	ini_set( 'memory_limit', '-1' );
	do_action('p3d_handle_check_begin');
	$uploads = wp_upload_dir( 'p3d' );
	$layer_height = (float)$_POST['layer_height'];
	$wall_thickness = (float)$_POST['wall_thickness'];
	$nozzle_size = (float)$_POST['nozzle_size'];
	$infill = (int)$_POST['infill'];
	$scale = (float)$_POST['scale'];
	$unit = $_POST['unit'];

	$settings = p3d_get_option( '3dp_settings' );
	$servers = p3d_get_option( '3dp_servers' );
	$basename = p3d_basename( $_POST['filename'] );
	if (!empty($_POST['server']) && in_array($_POST['server'], $servers)) $server = $_POST['server'];
	else 
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 114, "message": "'.__( 'Server not found.', '3dprint' ).'"}, "id" : "id"}' );

	$api_url = $server."/check.php";

	$cookie = WC()->session->get_session_cookie();
	$session_id = md5($_SERVER['REMOTE_ADDR'].$cookie[0]);

	$post = array( 'login' => $settings['api_login'], 'file_name' => $basename, 'session_id' => $session_id );
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$api_url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
	curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$result=curl_exec ($ch);
	curl_close ($ch);

	if($errno = curl_errno($ch)) {
		$error_message = curl_strerror($errno);		
	}
	if ( $errno ) {
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 109, "message": "'.__( 'Error: '.$error_message, '3dprint' ).'"}, "id" : "id"}' );
	}
	$response = json_decode($result, true);
	if ( $response['status']=='1' )  {
		$output['jsonrpc'] = "2.0";
		$output['status'] = $response['status']; 
		$output['progress'] = $response['progress']; 
		$output['model_filament'] = $response['model_filament']; 
		$p3d_cache = get_option('3dp_cache');
		$p3d_cache[md5_file($uploads['path'].$basename)][$layer_height."_".$wall_thickness."_".$nozzle_size."_".$infill."_".$scale."_".$unit]=$response['model_filament'];
		update_option('3dp_cache', $p3d_cache);
		wp_die( json_encode( $output ) );
	}
	if ( $response['status']=='2' )  {
		$output['jsonrpc'] = "2.0";
		$output['progress'] = $response['progress']; 
		$output['status'] = $response['status']; //2 - in progress
		$output['file'] = $response['file']; //2 - in progress
		wp_die( json_encode( $output ) );
	}
	elseif ( $response['status']=='0' ) {
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 110, "message": "'.__( $response['message'], '3dprint' ).'"}, "id" : "id"}' );
	}
	else {
		wp_die( '{"jsonrpc" : "2.0", "error" : {"code": 111, "message": "'.__( 'Unknown error.', '3dprint' ).'"}, "id" : "id"}' );
	}

}
?>
