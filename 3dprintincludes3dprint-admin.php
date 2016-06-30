<?php
/**
 *
 *
 * @author Sergey Burkov, http://www.wp3dprinting.com
 * @copyright 2015
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}



add_action( 'admin_menu', 'register_3dprint_menu_page' );
function register_3dprint_menu_page() {
	add_menu_page( '3DPrint', '3DPrint', 'manage_options', '3dprint', 'register_3dprint_menu_page_callback' );
}

function register_3dprint_menu_page_callback() {
	global $wpdb;
	if ( $_GET['page'] != '3dprint' ) return false;
	if ( isset( $_POST['action'] ) ) {
		update_option('3dp_cache', array());
	}
	if ( isset( $_POST['action'] ) && $_POST['action']=='remove_printer' ) {
		$wpdb->delete( $wpdb->prefix."p3d_printers", array('id'=>$_POST['printer_id']) );
	}

	if ( isset( $_POST['action'] ) && $_POST['action']=='remove_material' ) {
		$wpdb->delete( $wpdb->prefix."p3d_materials", array('id'=>$_POST['material_id']) );
	}

	if ( isset( $_POST['action'] ) && $_POST['action']=='remove_coating' ) {
		$wpdb->delete( $wpdb->prefix."p3d_coatings", array('id'=>$_POST['coating_id']) );
	}

	if ( isset( $_POST['action'] ) && $_POST['action']=='remove_request' ) {
		$price_requests=p3d_get_option( '3dp_price_requests' );
		unset( $price_requests[$_POST['request_id']] );
		update_option( '3dp_price_requests', $price_requests );
	}

	if ( isset( $_POST['3dp_printer_name'] ) && count( $_POST['3dp_printer_name'] )>0 ) {
		foreach ( $_POST['3dp_printer_name'] as $printer_id => $printer ) {
			if (empty($_POST['3dp_printer_name'][$printer_id])) continue;
			$printers[$printer_id]['id']=$printer_id;
			$printers[$printer_id]['name']=sanitize_text_field( $_POST['3dp_printer_name'][$printer_id] );
			$printers[$printer_id]['width']=(float)( $_POST['3dp_printer_width'][$printer_id] );
			$printers[$printer_id]['length']=(float)( $_POST['3dp_printer_length'][$printer_id] );
			$printers[$printer_id]['height']=(float)( $_POST['3dp_printer_height'][$printer_id] );
			$printers[$printer_id]['layer_height']=(float)( $_POST['3dp_printer_layer_height'][$printer_id] );
			$printers[$printer_id]['wall_thickness']=(float)( $_POST['3dp_printer_wall_thickness'][$printer_id] );
			$printers[$printer_id]['nozzle_size']=(float)( $_POST['3dp_printer_nozzle_size'][$printer_id] );
			$printers[$printer_id]['price']= $_POST['3dp_printer_price'][$printer_id] ;
			$printers[$printer_id]['price_type']=$_POST['3dp_printer_price_type'][$printer_id];

			if ( isset($_POST['3dp_printer_materials']) && count( $_POST['3dp_printer_materials'][$printer_id] )>0 ) {
				$printers[$printer_id]['materials']=implode(',',$_POST['3dp_printer_materials'][$printer_id]);
			}

			if ( isset($_POST['3dp_printer_infills']) && count( $_POST['3dp_printer_infills'][$printer_id] )>0 ) {
				$printers[$printer_id]['infills']=implode(',',$_POST['3dp_printer_infills'][$printer_id]);
			}
			else {
				$printers[$printer_id]['infills']='';
			}

			$printers[$printer_id]['default_infill']= $_POST['3dp_printer_default_infill'][$printer_id] ;
			$printers[$printer_id]['sort_order']=(int)$_POST['3dp_printer_sort_order'][$printer_id];
			$printers[$printer_id]['group_name']=trim($_POST['3dp_printer_group_name'][$printer_id]);
		}

		foreach ($printers as $printer) {
			p3d_update_option( '3dp_printers', $printer );
		}

	}

	if ( isset( $_POST['3dp_material_name'] ) && count( $_POST['3dp_material_name'] )>0 ) {

		foreach ( $_POST['3dp_material_name'] as $material_id => $material ) {

			if (empty($_POST['3dp_material_name'][$material_id])) continue;

			if ( $_POST['3dp_material_type'][$material_id]=='filament' && !empty( $_POST['3dp_material_diameter'][$material_id] ) && !empty( $_POST['3dp_material_length'][$material_id] ) && !empty( $_POST['3dp_material_weight'][$material_id] ) ) {
				$materials[$material_id]['density']=round( ( $_POST['3dp_material_weight'][$material_id]*1000 )/( M_PI*( pow( $_POST['3dp_material_diameter'][$material_id], 2 )/4 )*$_POST['3dp_material_length'][$material_id] ), 2 );
			}
			else {
				$materials[$material_id]['density']=$_POST['3dp_material_density'][$material_id];
			}
			$materials[$material_id]['id'] = $material_id;
			$materials[$material_id]['name']=sanitize_text_field( $_POST['3dp_material_name'][$material_id] );
			$materials[$material_id]['type'] = $_POST['3dp_material_type'][$material_id];
			$materials[$material_id]['diameter']=(float)( $_POST['3dp_material_diameter'][$material_id] );
			$materials[$material_id]['length']=(float)( $_POST['3dp_material_length'][$material_id] );
			$materials[$material_id]['weight']=(float)( $_POST['3dp_material_weight'][$material_id] );
			$materials[$material_id]['price']= $_POST['3dp_material_price'][$material_id] ;
			$materials[$material_id]['price_type']=$_POST['3dp_material_price_type'][$material_id];
			$materials[$material_id]['roll_price']=(float)( $_POST['3dp_material_roll_price'][$material_id] );
			$materials[$material_id]['color']=$_POST['3dp_material_color'][$material_id];                      
			$materials[$material_id]['sort_order']=(int)$_POST['3dp_material_sort_order'][$material_id];
			$materials[$material_id]['group_name']=trim($_POST['3dp_material_group_name'][$material_id]);
		}

		foreach ($materials as $material) {
			p3d_update_option( '3dp_materials', $material );
		}
	}

	if ( isset( $_POST['3dp_coating_name'] ) && count( $_POST['3dp_coating_name'] )>0 ) {

		foreach ( $_POST['3dp_coating_name'] as $coating_id => $coating ) {
			if (empty($_POST['3dp_coating_name'][$coating_id])) continue;
			$coatings[$coating_id]['id']=$coating_id;
			$coatings[$coating_id]['name']=sanitize_text_field( $_POST['3dp_coating_name'][$coating_id] );
			$coatings[$coating_id]['price']=$_POST['3dp_coating_price'][$coating_id];
			$coatings[$coating_id]['color']=$_POST['3dp_coating_color'][$coating_id];
			if ( isset($_POST['3dp_coating_materials']) && count( $_POST['3dp_coating_materials'][$coating_id] )>0 ) {
				$coatings[$coating_id]['materials']=implode(',',$_POST['3dp_coating_materials'][$coating_id]);
			}
			$coatings[$coating_id]['sort_order']=(int)$_POST['3dp_coating_sort_order'][$coating_id];
			$coatings[$coating_id]['group_name']=trim($_POST['3dp_coating_group_name'][$coating_id]);

		}
		foreach ($coatings as $coating) {
			p3d_update_option( '3dp_coatings', $coating );
		}
	}


	if ( isset( $_POST['3dp_settings'] ) && !empty( $_POST['3dp_settings'] ) ) {
		update_option( '3dp_settings', $_POST['3dp_settings'] );
	}


	if ( isset( $_POST['p3d_buynow'] ) && count( $_POST['p3d_buynow'] )>0 ) {

		foreach ( $_POST['p3d_buynow'] as $key=>$price ) {
			list ( $product_id, $printer_id, $material_id, $coating_id, $infill, $base64_filename ) = explode( '_', $key );
			$filename=base64_decode( $base64_filename );
			$product = new WC_Product_Variable( $product_id );
			$price_requests=p3d_get_option( '3dp_price_requests' );

			if ( count( $price_requests ) ) {
				$email=$price_requests[$key]['email'];
				$variation=$price_requests[$key]['attributes'];
				$variation['attribute_pa_p3d_model']=urlencode( $variation['attribute_pa_p3d_model'] );

				$query = parse_url( $product->get_permalink( $product_id ), PHP_URL_QUERY );

				if ( $query ) {
					$product_url=$product->get_permalink( $product_id ).'&'.p3d_query_string( $variation ).'p3d_buynow=1';
				}
				else {
					$product_url=$product->get_permalink( $product_id ).'?'.p3d_query_string( $variation ).'p3d_buynow=1';
				}


				if ( $price ) {
					//echo $product_url;
					$price_requests[$key]['price']=$price;

					$db_printers=p3d_get_option( '3dp_printers' );
					$db_materials=p3d_get_option( '3dp_materials' );
					$db_coatings=p3d_get_option( '3dp_coatings' );

					$upload_dir = wp_upload_dir();
					$link = $upload_dir['baseurl'] ."/p3d/".urlencode($filename);
					$subject=__( "Your model's price" , '3dprint' );

					//$message.=_("Product ID:")." $product_id <br>";
					$message="";
					$message.=__( "Printer" , '3dprint' ).": ".__($db_printers[$printer_id]['name'], '3dprint')." <br>";
					if ($settings['api_analyse']=='on')
						$message.=__( "Infill" , '3dprint' ).": ".$infill."% <br>";
					$message.=__( "Material" , '3dprint' ).": ".__($db_materials[$material_id]['name'], '3dprint')." <br>";
					$message.=__( "Coating" , '3dprint' ).": ".__($db_coatings[$coating_id]['name'], '3dprint')." <br>";
					$message.=__( "Model" , '3dprint' ).": <a href='".$link."'>".$filename."</a> <br>";

					foreach ( $variation as $key => $value ) {
						if ( strpos( $key, 'attribute_' )===0 ) {
							$product_attributes=( $product->get_attributes() );
							$attribute_id=str_replace( 'attribute_', '', $key );
							if ( !strstr( $key, 'p3d_' ) ) $message.=$product_attributes[$attribute_id]['name'].": $value <br>";
						}
					}
					$message.=__( "<b>Price:</b>" , '3dprint' ).wc_price($price)." <br>";
					$message.=__( "<b>Buy Now!:</b>" , '3dprint' )." <a href='".$product_url."'>".$product_url."</a> <br>";
					$message.=__( "<b>Comments:</b>" , '3dprint' )." ".$_POST['p3d_comments']." <br>";

					do_action('3dprint_send_quote', $message);
					$headers = array( 'Content-Type: text/html; charset=UTF-8' );
					if (wp_mail( $email, $subject, $message, $headers )) {
						update_option( '3dp_price_requests', $price_requests );
					}//todo: else show error
				}
				
			}//if ( count( $price_requests ) ) 
		}//foreach ( $_POST['p3d_buynow'] as $key=>$price )
		do_action('3dprint_after_send_quotes');
	}//if ( isset( $_POST['p3d_buynow'] ) && count( $_POST['p3d_buynow'] )>0 )

	$printers=p3d_get_option( '3dp_printers' );
	$materials=p3d_get_option( '3dp_materials' );
	$coatings=p3d_get_option( '3dp_coatings' );
	$settings=p3d_get_option( '3dp_settings' );
	$price_requests=p3d_get_option( '3dp_price_requests' );
	
//	if (!empty($printers)) $printers = p3d_sort_by_group_order($printers);
//	if (!empty($materials)) $materials = p3d_sort_by_group_order($materials);
//	if (!empty($coatings)) $coatings = p3d_sort_by_group_order($coatings);
//	$unassigned_materials = p3d_get_unassigned_materials();
//print_r(p3d_get_unassigned_coatings($coatings, $materials));
//print_r(p3d_get_unassigned_materials($coatings, $materials));

?>
<script language="javascript">
function p3dCalculateFilamentPrice(material_obj) {
	var diameter=parseFloat(jQuery(material_obj).closest('table.material').find('input.3dp_diameter').val());
	var length=parseFloat(jQuery(material_obj).closest('table.material').find('input.3dp_length').val());
	var weight=parseFloat(jQuery(material_obj).closest('table.material').find('input.3dp_weight').val());
	var price=parseFloat(jQuery(material_obj).closest('table.material').find('input.3dp_roll_price').val());
	var price_type=jQuery(material_obj).closest('table.material').find('select.3dp_price_type').val();

	if (price_type=='cm3') {
		if (!diameter || !price || !length) {alert('<?php _e( 'Please input roll price, diameter and length', '3dprint' );?>');return false;}
		var volume=(Math.PI*((diameter*diameter)/4)*(length*1000))/1000;
		var volume_cost=price/volume;
		jQuery(material_obj).closest('table.material').find('input.3dp_price').val(volume_cost.toFixed(2));
	}
	else if (price_type=='gram') {
	
		if (!weight || !price) {alert('<?php _e( 'Please input roll price and weight', '3dprint' );?>');return false;}
		var weight_cost=price/(weight*1000);
		jQuery(material_obj).closest('table.material').find('input.3dp_price').val(weight_cost.toFixed(2));
	}

}
</script>
<div class="wrap">
	<h2><?php _e( '3D printing settings', '3dprint' );?></h2>

	<div id="3dp_tabs">

		<ul>
			<li><a href="#3dp_tabs-0"><?php _e( 'Settings', '3dprint' );?></a></li>
			<li><a href="#3dp_tabs-1"><?php _e( 'Printers', '3dprint' );?></a></li>
			<li><a href="#3dp_tabs-2"><?php _e( 'Materials', '3dprint' );?></a></li>
			<li><a href="#3dp_tabs-3"><?php _e( 'Coatings', '3dprint' );?></a></li>
			<li><a href="#3dp_tabs-4"><?php _e( 'Price Requests', '3dprint' );?></a></li>
		</ul>
		<div id="3dp_tabs-0">
			<form method="post" action="admin.php?page=3dprint#3dp_tabs-0">
				<p><b><?php _e( 'Checkout', '3dprint' );?></b></p>
				<select name="3dp_settings[pricing]">
					<option <?php if ( $settings['pricing']=='checkout' ) echo 'selected';?> value="checkout"><?php _e( 'Calculate price and allow checkout' , '3dprint' );?></option>
					<option <?php if ( $settings['pricing']=='request_estimate' ) echo 'selected';?> value="request_estimate"><?php _e( 'Give an estimate and request price', '3dprint' );?></option>
					<option <?php if ( $settings['pricing']=='request' ) echo 'selected';?> value="request"><?php _e( 'Request price', '3dprint' );?></option>
			 	</select>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="new_option_name,some_other_option,option_etc" />
				<hr>
				<p><b><?php _e( 'Product Viewer', '3dprint' );?></b></p>
				<table>
					<tr>
						<td><?php _e( 'Canvas Resolution', '3dprint' );?></td>
						<td><input size="3" type="text"  placeholder="<?php _e( 'Width', '3dprint' );?>" name="3dp_settings[canvas_width]" value="<?php echo $settings['canvas_width'];?>">px &times; <input size="3"  type="text" placeholder="<?php _e( 'Height', '3dprint' );?>" name="3dp_settings[canvas_height]" value="<?php echo $settings['canvas_height'];?>">px</td>
					</tr>
					<tr>
						<td><?php _e( 'Cookie Lifetime', '3dprint' );?></td>
						<td>
							<select name="3dp_settings[cookie_expire]">
								<option <?php if ( $settings['cookie_expire']=='0' ) echo 'selected';?> value="0">0 <?php _e( '(no cookies)', '3dprint' );?> 
								<option <?php if ( $settings['cookie_expire']=='1' ) echo 'selected';?> value="1">1
								<option <?php if ( $settings['cookie_expire']=='2' ) echo 'selected';?> value="2">2
							</select> <?php _e( 'days', '3dprint' );?> 
						</td>
					</tr>

					<tr>
						<td><?php _e( 'Printers Layout', '3dprint' );?></td>
						<td>
							<select name="3dp_settings[printers_layout]">
								<option <?php if ( $settings['printers_layout']=='lists' ) echo 'selected';?> value="lists"><?php _e( 'List', '3dprint' );?></option>
								<option <?php if ( $settings['printers_layout']=='dropdowns' ) echo 'selected';?> value="dropdowns"><?php _e( 'Dropdown', '3dprint' );?></option>
							</select> 
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Materials Layout', '3dprint' );?></td>
						<td>
							<select name="3dp_settings[materials_layout]">
								<option <?php if ( $settings['materials_layout']=='lists' ) echo 'selected';?> value="lists"><?php _e( 'List', '3dprint' );?></option>
								<option <?php if ( $settings['materials_layout']=='dropdowns' ) echo 'selected';?> value="dropdowns"><?php _e( 'Dropdown', '3dprint' );?></option>
								<option <?php if ( $settings['materials_layout']=='colors' ) echo 'selected';?> value="colors"><?php _e( 'Colors', '3dprint' );?></option>
							</select> 
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Coatings Layout', '3dprint' );?></td>
						<td>
							<select name="3dp_settings[coatings_layout]">
								<option <?php if ( $settings['coatings_layout']=='lists' ) echo 'selected';?> value="lists"><?php _e( 'List', '3dprint' );?></option>
								<option <?php if ( $settings['coatings_layout']=='dropdowns' ) echo 'selected';?> value="dropdowns"><?php _e( 'Dropdown', '3dprint' );?></option>
								<option <?php if ( $settings['coatings_layout']=='colors' ) echo 'selected';?> value="colors"><?php _e( 'Colors', '3dprint' );?></option>
							</select> 
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Infills Layout', '3dprint' );?></td>
						<td>
							<select name="3dp_settings[infills_layout]">
								<option <?php if ( $settings['infills_layout']=='lists' ) echo 'selected';?> value="lists"><?php _e( 'List', '3dprint' );?></option>
								<option <?php if ( $settings['infills_layout']=='dropdowns' ) echo 'selected';?> value="dropdowns"><?php _e( 'Dropdown', '3dprint' );?></option>
							</select> 
						</td>
					</tr>
					<tr>
						<td><?php _e( 'Background 1', '3dprint' );?></td>
						<td><input type="text" class="3dp_color_picker" name="3dp_settings[background1]" value="<?php echo $settings['background1'];?>"></td>
					</tr>
					<tr>
						<td><?php _e( 'Background 2', '3dprint' );?></td>
						<td><input type="text" class="3dp_color_picker" name="3dp_settings[background2]" value="<?php echo $settings['background2'];?>"></td>
					</tr>
					<tr>
						<td><?php _e( 'Plane Color', '3dprint' );?></td>
						<td><input type="text" class="3dp_color_picker" name="3dp_settings[plane_color]" value="<?php echo $settings['plane_color'];?>"></td>
					</tr>
					<tr>
						<td><?php _e( 'Printer Color', '3dprint' );?></td>
						<td><input type="text" class="3dp_color_picker" name="3dp_settings[printer_color]" value="<?php echo $settings['printer_color'];?>"></td>
					</tr>
					<tr>
						<td><?php _e( 'Button Background', '3dprint' );?></td>
						<td><input type="text" class="3dp_color_picker" name="3dp_settings[button_color1]" value="<?php echo $settings['button_color1'];?>"></td>
					</tr>
					<tr>
						<td><?php _e( 'Button Shadow', '3dprint' );?></td>
						<td><input type="text" class="3dp_color_picker" name="3dp_settings[button_color2]" value="<?php echo $settings['button_color2'];?>"></td>
					</tr>
					<tr>
						<td><?php _e( 'Button Progress Bar', '3dprint' );?></td>
						<td><input type="text" class="3dp_color_picker" name="3dp_settings[button_color3]" value="<?php echo $settings['button_color3'];?>"></td>
					</tr>
					<tr>
						<td><?php _e( 'Button Font', '3dprint' );?></td>
						<td><input type="text" class="3dp_color_picker" name="3dp_settings[button_color4]" value="<?php echo $settings['button_color4'];?>"></td>
					</tr>
					<tr>
						<td><?php _e( 'Button Tick', '3dprint' );?></td>
						<td><input type="text" class="3dp_color_picker" name="3dp_settings[button_color5]" value="<?php echo $settings['button_color5'];?>"></td>
					</tr>
					<tr>
						<td><?php _e( 'Zoom', '3dprint' );?></td>
						<td><input size="3" type="text" name="3dp_settings[zoom]" value="<?php echo $settings['zoom'];?>"></td>
					</tr>
					<tr>
						<td><?php _e( 'Angle X', '3dprint' );?></td>
						<td><input size="3" type="text" name="3dp_settings[angle_x]" value="<?php echo $settings['angle_x'];?>">&deg;</td>
					</tr>
					<tr>
						<td><?php _e( 'Angle Y', '3dprint' );?></td>
						<td><input size="3" type="text" name="3dp_settings[angle_y]" value="<?php echo $settings['angle_y'];?>">&deg;</td>
					</tr>
					<tr>
						<td><?php _e( 'Angle Z', '3dprint' );?></td>
						<td><input size="3" type="text" name="3dp_settings[angle_z]" value="<?php echo $settings['angle_z'];?>">&deg;</td>
					</tr>
					<tr>
						<td><?php _e( 'Show Canvas Stats', '3dprint' );?></td>
						<td><input type="checkbox" name="3dp_settings[show_canvas_stats]" <?php if ($settings['show_canvas_stats']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php _e( 'Show Scaling', '3dprint' );?></td>
						<td><input type="checkbox" name="3dp_settings[show_scale]" <?php if ($settings['show_scale']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php _e( 'Show Model Stats', '3dprint' );?></td>
						<td><input type="checkbox" name="3dp_settings[show_model_stats]" <?php if ($settings['show_model_stats']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php _e( 'Show Printers', '3dprint' );?></td>
						<td><input type="checkbox" name="3dp_settings[show_printers]" <?php if ($settings['show_printers']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php _e( 'Show Materials', '3dprint' );?></td>
						<td><input type="checkbox" name="3dp_settings[show_materials]" <?php if ($settings['show_materials']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php _e( 'Show Coatings', '3dprint' );?></td>
						<td><input type="checkbox" name="3dp_settings[show_coatings]" <?php if ($settings['show_coatings']=='on') echo 'checked';?>></td>
					</tr>

				</table>
				<hr>
				<p><b><?php _e( 'File Upload', '3dprint' );?></b></p>
				<table>
					<tr>
						<td><?php _e( 'Max. File Size', '3dprint' );?></td>
						<td><input size="3" type="text" name="3dp_settings[file_max_size]" value="<?php echo $settings['file_max_size'];?>"><?php _e( 'mb' );?> </td>
					</tr>
					<tr>
						<td><?php _e( 'Allowed Extensions', '3dprint' );?></td>
						<td><input size="9" type="text" name="3dp_settings[file_extensions]" value="<?php echo $settings['file_extensions'];?>"></td>
					</tr>
					<tr>
						<td><?php _e( 'Delete files older than', '3dprint' );?></td>
						<td><input size="3" type="text" name="3dp_settings[file_max_days]" value="<?php echo $settings['file_max_days'];?>"><?php _e( 'days', '3dprint' );?> </td>
					</tr>
				</table>
				<hr>
				<p><b><?php _e( 'Plugin Updates', '3dprint' );?></b></p>

				<p><i><?php _e('This login is used for getting plugin updates.', '3dprint');?></i></p>

				<table>
					<tr>
						<td><?php _e( 'Login', '3dprint' );?></td>
						<td><input type="text" placeholder="user@example.com" name="3dp_settings[api_login]" value="<?php echo $settings['api_login'];?>">&nbsp;<?php _e( '(the e-mail you used when you were ordering the plugin)', '3dprint' );?></td>
					</tr>
				</table>

				<hr>
				<p><b><?php _e( 'Analyse API (STL only)', '3dprint' );?></b></p>
				<p><i><?php _e('This is needed for using infill and getting accurate material volume. Paid monthly subscription is required.', '3dprint');?></i></p>
				<p><i><?php _e('Demo is available <a href="http://www.wp3dprinting.com/index.php/product/3d-printing-demo-product/">here.</a>', '3dprint');?></i></p>
				<table>
					<tr>
						<td><?php _e( 'Enable API', '3dprint' );?></td>
						<td><input type="checkbox" name="3dp_settings[api_analyse]" <?php if ($settings['api_analyse']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php _e( 'Show Infills', '3dprint' );?></td>
						<td><input type="checkbox" name="3dp_settings[show_infills]" <?php if ($settings['show_infills']=='on') echo 'checked';?>></td>
					</tr>
					<tr>
						<td><?php _e( 'Login', '3dprint' );?></td>
						<td><input type="text" placeholder="user@example.com" name="3dp_settings[api_subscription_login]" value="<?php echo $settings['api_subscription_login'];?>">&nbsp;<?php _e( '(the e-mail you used when you were ordering the subscription)', '3dprint' );?></td>
					</tr>
					<tr>
						<td><?php _e( 'Key', '3dprint' );?></td>
						<td><input type="text" placeholder="" name="3dp_settings[api_subscription_key]" value="<?php echo $settings['api_subscription_key'];?>">&nbsp;<a target="_blank" href="https://secure.avangate.com/order/checkout.php?PRODS=4678226&QTY=1&CART=1&CARD=1"><?php _e('Buy Now!', '3dprint');?></a></td>
					</tr>
				</table>
				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', '3dprint' ) ?>" />
				</p>
			</form>
		</div>  
		<div id="3dp_tabs-1">
			<form method="post" action="admin.php?page=3dprint#3dp_tabs-1">

<?php 			wp_nonce_field( 'update-options' ); ?>
<?php
			if ( !is_array($printers) || count( $printers )==0 ) {
?>
				<table class="form-table printer">
					<tr>
						<td colspan="2"><hr></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Printer Name', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_name[1]" value="Default Printer" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Build Tray Length', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_length[1]" value="200" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Build Tray Width', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_width[1]" value="200" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Build Tray Height', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_height[1]" value="200" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Layer Height', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_layer_height[1]" value="0.1" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Wall Thickness', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_wall_thickness[1]" value="0.8" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Nozzle Size', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_nozzle_size[1]" value="0.4" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr class="printer_infills" valign="top">
						<th scope="row"><?php _e( 'Infill Options', '3dprint' ); ?></th>
						<td>
							<select autocomplete="off" name="3dp_printer_infills[1][]" multiple="multiple" class="sumoselect">
								<?php 
									for ($j=0; $j<=10; $j++) {
										echo '<option value="'.($j*10).'">'.($j*10).'%';
									}
								?>
							</select> &nbsp;
							<?php _e( 'Default Infill:', '3dprint' ); ?>
							<select name="3dp_printer_default_infill[1]">
								<?php 
									for ($j=0; $j<=10; $j++) {
										echo '<option value="'.($j*10).'">'.($j*10).'%';
									}
								?>
		 					</select>
						</td>
					</tr>



					<tr valign="top">
						<th scope="row"><?php _e( 'Printing Cost', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_price[1]" value="0.05" /><?php echo get_woocommerce_currency_symbol(); ?> <?php _e('per', '3dprint');?>
							<select name="3dp_printer_price_type[1]">
								<option value="box_volume"><?php _e('1 cm3 of Bounding Box Volume', '3dprint');?></option>
								<option value="material_volume"><?php _e('1 cm3 of Material Volume', '3dprint');?></option>
								<option value="gram"><?php _e('1 gram of Material', '3dprint');?></option>
								<option value="sla"><?php _e('sla formula', '3dprint');?></option>
								<option value="sls"><?php _e('sls formula', '3dprint');?></option>
		 					</select>
						</td>
					</tr>

					<tr class="printer_materials" valign="top">
						<th scope="row"><?php _e( 'Materials', '3dprint' ); ?></th>
						<td>
							<select autocomplete="off" name="3dp_printer_materials[1][]" multiple="multiple" class="sumoselect">
								<?php 
									foreach ($materials as $j => $material) {
										echo '<option value="'.$materials[$j]['id'].'">'.$materials[$j]['name'];
									}
								?>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Group Name', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_group_name[1]" value="" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Sort Order', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_sort_order[1]" value="0" /></td>
					</tr>




				</table>
			<?php } ?>
<?php

	if ( is_array( $printers ) && count( $printers )>0 ) {

		$i=0;
		foreach ( $printers as $printer ) {

?>
				<input type="hidden" name="action" value="update" />
				<table class="form-table printer">
					<tr>
						<td colspan="3"><hr></td>
					</tr>
					<tr>
						<td colspan="3"><span class="item_id"><?php echo "<b>ID #".$printer['id']."</b>";?></span></td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e( 'Printer Name', '3dprint' ); ?>
						</th>
						<td>
							<input type="text" name="3dp_printer_name[<?php echo $printer['id'];?>]" value="<?php echo $printer['name'];?>" />&nbsp;
							<a class="remove_printer" href="javascript:void(0);" onclick="p3dRemovePrinter(<?php echo $printer['id'];?>);return false;">
								<img alt="<?php _e( 'Remove Printer', '3dprint' );?>" title="<?php _e( 'Remove Printer', '3dprint' );?>" src="<?php echo plugins_url( '3dprint/images/remove.png' ); ?>">
							</a>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Build Tray Length', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_length[<?php echo $printer['id'];?>]" value="<?php echo $printer['length'];?>" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Build Tray Width', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_width[<?php echo $printer['id'];?>]" value="<?php echo $printer['width'];?>" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Build Tray Height', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_height[<?php echo $printer['id'];?>]" value="<?php echo $printer['height'];?>" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Layer Height', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_layer_height[<?php echo $printer['id'];?>]" value="<?php echo $printer['layer_height'];?>" /><?php _e( 'mm', '3dprint' );?></td> 
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Wall Thickness', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_wall_thickness[<?php echo $printer['id'];?>]" value="<?php echo $printer['wall_thickness'];?>" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Nozzle Size', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_nozzle_size[<?php echo $printer['id'];?>]" value="<?php echo $printer['nozzle_size'];?>" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr class="printer_infills" valign="top">
						<th scope="row"><?php _e( 'Infill Options', '3dprint' ); ?></th>
						<td>
							<select autocomplete="off" name="3dp_printer_infills[<?php echo $printer['id'];?>][]" multiple="multiple" class="sumoselect">
								<?php 
									for ($j=0; $j<=10; $j++) {
										if (strlen($printer['infills'])>0 && in_array($j*10, explode(',',$printer['infills']))) $selected="selected"; else $selected="";
										echo '<option '.$selected.' value="'.($j*10).'">'.($j*10).'%';
									}
								?>
							</select>&nbsp;
							<?php _e( 'Default Infill:', '3dprint' ); ?>
							<select name="3dp_printer_default_infill[<?php echo $printer['id'];?>]">
								<?php 
									for ($j=0; $j<=10; $j++) {
										if ((int)$printer['default_infill']==($j*10)) $selected="selected"; else $selected="";
										echo '<option '.$selected.' value="'.($j*10).'">'.($j*10).'%';
									}
								?>
		 					</select>
						</td>
					</tr>




					<tr valign="top">
						<th scope="row"><?php _e( 'Printing Cost', '3dprint' ); ?></th>
						<td>
							<input type="text" name="3dp_printer_price[<?php echo $printer['id'];?>]" value="<?php echo $printer['price'];?>" /><?php echo get_woocommerce_currency_symbol(); ?> <?php _e('per', '3dprint');?>
							<select name="3dp_printer_price_type[<?php echo $printer['id'];?>]">
								<option <?php if ( $printer['price_type']=='box_volume' ) echo "selected";?> value="box_volume"><?php _e('1 cm3 of Bounding Box Volume', '3dprint');?></option>
								<option <?php if ( $printer['price_type']=='material_volume' ) echo "selected";?> value="material_volume"><?php _e('1 cm3 of Material Volume', '3dprint');?></option>
								<option <?php if ( $printer['price_type']=='gram' ) echo "selected";?> value="gram"><?php _e('1 gram of Material', '3dprint');?></option>
								<option <?php if ( $printer['price_type']=='sla' ) echo "selected";?> value="sla"><?php _e('sla formula', '3dprint');?></option>
								<option <?php if ( $printer['price_type']=='sls' ) echo "selected";?> value="sls"><?php _e('sls formula', '3dprint');?></option>
							</select>
						</td>
					</tr>

					<tr class="printer_materials" valign="top">
						<th scope="row"><?php _e( 'Materials', '3dprint' ); ?></th>
						<td>

							<select autocomplete="off" name="3dp_printer_materials[<?php echo $printer['id'];?>][]" multiple="multiple" class="sumoselect">
								<?php 
									foreach ($materials as $j => $material) {
										if (strlen($printer['materials'])>0 && in_array($materials[$j]['id'], explode(',',$printer['materials']))) $selected="selected"; else $selected="";
										echo '<option '.$selected.' value="'.$materials[$j]['id'].'">'.$materials[$j]['name'];
									}
								?>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Group Name', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_group_name[<?php echo $printer['id'];?>]" value="<?php echo $printer['group_name'];?>" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Sort Order', '3dprint' ); ?></th>
						<td><input type="text" name="3dp_printer_sort_order[<?php echo $printer['id'];?>]" value="<?php echo (int)$printer['sort_order'];?>" /></td>
					</tr>


				</table>
<?php
			$i++;
		}
	}
?>
				<button id="add_printer_button" class="button-secondary" onclick="p3dAddPrinter();return false;"><?php _e( 'Add Printer', '3dprint' );?></button>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="new_option_name,some_other_option,option_etc" />

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', '3dprint' ) ?>" />
				</p>
			</form>
		</div><!-- 3dp_tabs-1 -->
		<div id="3dp_tabs-2">
			<form method="post" action="admin.php?page=3dprint#3dp_tabs-2">
<?php
			if ( !is_array($materials) || count( $materials )==0 ) {
?>
				<table class="form-table material">
					<tr>
						<td colspan="2"><hr></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Material Name', '3dprint' );?></th>
						<td><input type="text" name="3dp_material_name[1]" value="ABS (1.75mm)" /></td>
					</tr>

				 	<tr valign="top">
						<th scope="row"><?php _e( 'Material Type', '3dprint' );?></th>
						<td>
							<select name="3dp_material_type[1]" onchange="p3dSetMaterialType(this)">
								<option value="filament"><?php _e( 'Filament', '3dprint' );?>
								<option value="other"><?php _e( 'Other', '3dprint' );?>
							</select>
						</td>
					</tr>
	
					<tr valign="top">
						<th scope="row"><?php _e( 'Price', '3dprint' ); ?></th>
						<td>
							<input type="text" class="3dp_price" name="3dp_material_price[1]" value="0.03" /><?php echo get_woocommerce_currency_symbol(); ?> <?php _e('per', '3dprint');?>
							<select class="3dp_price_type" name="3dp_material_price_type[1]">
								<option value="cm3"><?php _e('1 cm3', '3dprint');?></option>
								<option value="gram"><?php _e('1 gram', '3dprint');?></option>
							</select>
							<a class="material_filament" onclick="javascript:p3dCalculateFilamentPrice(this)" href="javascript:void(0)"><?php _e( 'Calculate', '3dprint' );?></a>
					 	</td>
					</tr>

					<tr class="material_other" valign="top">
						<th scope="row"><?php _e( 'Material Density', '3dprint' );?></th>
						<td><input type="text" name="3dp_material_density[1]" value="0" /><?php _e( 'g/cm3', '3dprint' );?></td>
					</tr>

					<tr class="material_filament" valign="top">
						<th scope="row"><?php _e( 'Filament Diameter', '3dprint' );?></th>
						<td><input type="text" class="3dp_diameter" name="3dp_material_diameter[1]" value="1.75" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr class="material_filament" valign="top">
						<th scope="row"><?php _e( 'Filament Length', '3dprint' );?></th>
						<td><input type="text" class="3dp_length" name="3dp_material_length[1]" value="330" /><?php _e( 'm', '3dprint' );?></td>
					</tr>

					<tr class="material_filament" valign="top">
						<th scope="row"><?php _e( 'Roll Weight', '3dprint' );?></th>
						<td><input type="text" class="3dp_weight" name="3dp_material_weight[1]" value="1" /><?php _e( 'kg', '3dprint' );?></td>
					</tr>

					<tr class="material_filament" valign="top">
						<th scope="row"><?php _e( 'Roll Price', '3dprint' );?></th>
						<td><input type="text" class="3dp_roll_price" name="3dp_material_roll_price[1]" value="20" /><?php echo get_woocommerce_currency_symbol(); ?></td>
					</tr>


					<tr valign="top">
						<th scope="row"><?php _e( 'Material Color', '3dprint' );?></th>
						<td class="color_td"><input type="text" class="3dp_color_picker" name="3dp_material_color[1]" value="" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Group Name', '3dprint' );?></th>
						<td><input type="text" name="3dp_material_group_name[1]" value="" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Sort Order', '3dprint' );?></th>
						<td><input type="text" name="3dp_material_sort_order[1]" value="0" /></td>
					</tr>
				</table>
			<?php } ?>
<?php
	if ( is_array( $materials ) && count( $materials )>0 ) {
		$i=0;
		foreach ( $materials as $material ) {
?>
				<table class="form-table material">
					<tr>
						<td colspan="2"><hr></td>
					</tr>
				 	<tr>
						<td colspan="2"><span class="item_id"><?php echo "<b>ID #".$material['id']."</b>";?></span></td>
				 	</tr>

				 	<tr valign="top">
						<th scope="row"><?php _e( 'Material Name', '3dprint' );?></th>
						<td>
							<input type="text" name="3dp_material_name[<?php echo $material['id'];?>]" value="<?php echo $material['name'];?>" />&nbsp;
							<a class="remove_material" href="javascript:void(0);" onclick="p3dRemoveMaterial(<?php echo $material['id'];?>);return false;">
								<img alt="<?php _e( 'Remove Filament', '3dprint' );?>" title="<?php _e( 'Remove Filament', '3dprint' );?>" src="<?php echo plugins_url( '3dprint/images/remove.png' ); ?>">
					 		</a>
						</td>
					</tr>

				 	<tr valign="top">
						<th scope="row"><?php _e( 'Material Type', '3dprint' );?></th>
						<td>
							<select class="select_material" name="3dp_material_type[<?php echo $material['id'];?>]" onchange="p3dSetMaterialType(this)">
								<option <?php if ( $material['type']=='filament' ) echo "selected";?> value="filament"><?php _e( 'Filament', '3dprint' );?>
								<option <?php if ( $material['type']=='other' ) echo "selected";?> value="other"><?php _e( 'Other', '3dprint' );?>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Price', '3dprint' ); ?></th>
						<td>
							<input type="text" class="3dp_price" name="3dp_material_price[<?php echo $material['id'];?>]" value="<?php echo $material['price'];?>" /><?php echo get_woocommerce_currency_symbol(); ?> <?php _e('per', '3dprint');?>
							<select class="3dp_price_type"  name="3dp_material_price_type[<?php echo $material['id'];?>]">
								<option <?php if ( $material['price_type']=='cm3' ) echo "selected";?> value="cm3"><?php _e('1 cm3', '3dprint');?></option>
								<option <?php if ( $material['price_type']=='gram' ) echo "selected";?> value="gram"><?php _e('1 gram', '3dprint');?></option>
							</select>
							<a class="material_filament" onclick="javascript:p3dCalculateFilamentPrice(this)" href="javascript:void(0)"><?php _e( 'Calculate', '3dprint' );?></a>
						</td>
					</tr>

					<tr class="material_other" valign="top">
						<th scope="row"><?php _e( 'Material Density', '3dprint' );?></th>
						<td>
							<input type="text" name="3dp_material_density[<?php echo $material['id'];?>]" value="<?php echo $material['density'];?>" /><?php _e( 'g/cm3', '3dprint' );?>
						</td>
					</tr>

					<tr class="material_filament" valign="top">
						<th scope="row"><?php _e( 'Filament Diameter', '3dprint' );?></th>
						<td><input type="text" class="3dp_diameter" name="3dp_material_diameter[<?php echo $material['id'];?>]" value="<?php echo $material['diameter'];?>" /><?php _e( 'mm', '3dprint' );?></td>
					</tr>

					<tr class="material_filament" valign="top">
						<th scope="row"><?php _e( 'Filament Length', '3dprint' );?></th>
						<td><input type="text" class="3dp_length" name="3dp_material_length[<?php echo $material['id'];?>]" value="<?php echo $material['length'];?>" /><?php _e( 'm', '3dprint' );?></td>
					</tr>

					<tr class="material_filament" valign="top">
						<th scope="row"><?php _e( 'Roll Weight', '3dprint' );?></th>
						<td><input type="text" class="3dp_weight" name="3dp_material_weight[<?php echo $material['id'];?>]" value="<?php echo $material['weight'];?>" /><?php _e( 'kg', '3dprint' );?></td>
					</tr>

					<tr class="material_filament" valign="top">
						<th scope="row"><?php _e( 'Roll Price', '3dprint' );?></th>
						<td><input type="text" class="3dp_roll_price" name="3dp_material_roll_price[<?php echo $material['id'];?>]" value="<?php echo $material['roll_price'];?>" /><?php echo get_woocommerce_currency_symbol(); ?></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Material Color', '3dprint' );?></th>
						<td class="color_td"><input type="text" class="3dp_color_picker" name="3dp_material_color[<?php echo $material['id'];?>]" value="<?php echo $material['color'];?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Group Name', '3dprint' );?></th>
						<td><input type="text" name="3dp_material_group_name[<?php echo $material['id'];?>]" value="<?php echo $material['group_name'];?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Sort Order', '3dprint' );?></th>
						<td><input type="text" name="3dp_material_sort_order[<?php echo $material['id'];?>]" value="<?php echo (int)$material['sort_order'];?>" /></td>
					</tr>
				</table>
<?php
			$i++;
		}
	}
?>
				<button id="add_material_button" class="button-secondary" onclick="p3dAddMaterial();return false;"><?php _e( 'Add Material', '3dprint' );?></button>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="new_option_name,some_other_option,option_etc" />

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', '3dprint' ) ?>" />
				</p>

			</form>

		</div><!-- 3dp_tabs-2 -->

		<div id="3dp_tabs-3">
			<form method="post" action="admin.php?page=3dprint#3dp_tabs-3">
<?php

			if ( !is_array($coatings) || count( $coatings )==0 ) {
?>
				<table class="form-table coating">
					<tr>
						<td colspan="2"><hr></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Coating Name', '3dprint' );?></th>
						<td><input type="text" name="3dp_coating_name[1]" value="" /></td>
					</tr>
	
					<tr valign="top">
						<th scope="row"><?php _e( 'Price', '3dprint' ); ?></th>
						<td>
							<input type="text" class="3dp_price" name="3dp_coating_price[1]" value="" /><?php echo get_woocommerce_currency_symbol(); ?> <?php _e('per', '3dprint');?> <?php _e('cm2 of surface area', '3dprint');?>
					 	</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Coating Color', '3dprint' );?></th>
						<td class="color_td"><input type="text" class="3dp_color_picker" name="3dp_coating_color[1]" value="" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Materials', '3dprint' ); ?></th>
						<td>
							<select autocomplete="off" name="3dp_coating_materials[1][]" multiple="multiple" class="sumoselect">
								<?php 
									foreach ($materials as $j => $material) {
										echo '<option value="'.$materials[$j]['id'].'">'.$materials[$j]['name'];
									}
								?>
							</select>
						</td>
					</tr>


					<tr valign="top">
						<th scope="row"><?php _e( 'Group Name', '3dprint' );?></th>
						<td><input type="text" name="3dp_coating_group_name[1]" value="" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Sort Order', '3dprint' );?></th>
						<td><input type="text" name="3dp_coating_sort_order[1]" value="0" /></td>
					</tr>

				</table>
			<?php } ?>
<?php
	if ( is_array( $coatings ) && count( $coatings )>0 ) {
		$i=0;
		foreach ( $coatings as $coating ) {
?>
				<table class="form-table coating">
					<tr>
						<td colspan="2"><hr></td>
					</tr>
				 	<tr>
						<td colspan="2"><span class="item_id"><?php echo "<b>ID #".$coating['id']."</b>";?></span></td>
				 	</tr>
				 	<tr valign="top">
					<th scope="row"><?php _e( 'Coating Name', '3dprint' );?></th>
						<td>
							<input type="text" name="3dp_coating_name[<?php echo $coating['id'];?>]" value="<?php echo $coating['name'];?>" />&nbsp;
							<a class="remove_coating" href="javascript:void(0);" onclick="p3dRemoveCoating(<?php echo $coating['id'];?>);return false;">
								<img alt="<?php _e( 'Remove Coating', '3dprint' );?>" title="<?php _e( 'Remove Coating', '3dprint' );?>" src="<?php echo plugins_url( '3dprint/images/remove.png' ); ?>">
					 		</a>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Price', '3dprint' ); ?></th>
						<td>
							<input type="text" class="3dp_price" name="3dp_coating_price[<?php echo $coating['id'];?>]" value="<?php echo $coating['price'];?>" /><?php echo get_woocommerce_currency_symbol(); ?> <?php _e('per', '3dprint');?> <?php _e('cm2 of surface area', '3dprint');?>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Coating Color', '3dprint' );?></th>
						<td class="color_td"><input type="text" class="3dp_color_picker" name="3dp_coating_color[<?php echo $coating['id'];?>]" value="<?php echo $coating['color'];?>" /></td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Materials', '3dprint' ); ?></th>
						<td>
							<select autocomplete="off" name="3dp_coating_materials[<?php echo $coating['id'];?>][]" multiple="multiple" class="sumoselect">
								<?php 
									foreach ($materials as $j => $material) {
										if (strlen($coating['materials'])>0 && in_array($materials[$j]['id'], explode(',',$coating['materials']))) $selected="selected"; else $selected="";
										echo '<option '.$selected.' value="'.$materials[$j]['id'].'">'.$materials[$j]['name'];
									}
								?>
							</select>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row"><?php _e( 'Group Name', '3dprint' );?></th>
						<td><input type="text" name="3dp_coating_group_name[<?php echo $coating['id'];?>]" value="<?php echo $coating['group_name'];?>" /></td>
					</tr>
					<tr valign="top">
						<th scope="row"><?php _e( 'Sort Order', '3dprint' );?></th>
						<td><input type="text" name="3dp_coating_sort_order[<?php echo $coating['id'];?>]" value="<?php echo (int)$coating['sort_order'];?>" /></td>
					</tr>
				</table>
<?php
			$i++;
		}
	}
?>
				<button id="add_coating_button" class="button-secondary" onclick="p3dAddCoating();return false;"><?php _e( 'Add Coating', '3dprint' );?></button>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="new_option_name,some_other_option,option_etc" />

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Save Changes', '3dprint' ) ?>" />
				</p>

			</form>

		</div><!-- 3dp_tabs-3 -->


		<div id="3dp_tabs-4">
			<form method="post" action="admin.php?page=3dprint#3dp_tabs-4">
<?php
			if ( is_array( $price_requests ) && count( $price_requests )>0 ) {
?>
				<table class="form-table">
					<tr>
						<td>X</td>
						<td><?php _e( 'Product', '3dprint' );?></td>
						<td><?php _e( 'Customer', '3dprint' );?></td>
						<td><?php _e( 'Details', '3dprint' );?></td>
						<td><?php _e( 'Price', '3dprint' );?></td>
						<td><?php _e( 'Comment', '3dprint' );?></td>
					</tr>
<?php
		$db_printers=p3d_get_option( '3dp_printers' );
		$db_materials=p3d_get_option( '3dp_materials' );
		$db_coatings=p3d_get_option( '3dp_coatings' );

		foreach ( $price_requests as $product_key=>$price_request ) {
			list ( $product_id, $printer_id, $material_id, $coating_id, $infill, $base64_filename ) = explode( '_', $product_key );
			$filename=base64_decode( $base64_filename );
			if ( $price_request['price']=='' ) {
				$product = new WC_Product_Variable( $product_id );


				$attr_st='';

				foreach ( $price_request['attributes'] as $attr_key => $attr_value ) {

					if ( $attr_key=='attribute_pa_p3d_printer' ) {
						$attr_st.=__( "Printer" , '3dprint' )." : ".$price_request['printer']."<br>";
					}
					elseif ( $attr_key=='attribute_pa_p3d_infill' && $settings['api_analyse']=='on') {
						$attr_st.=__( "Infill" , '3dprint' )." : ".$price_request['infill']."<br>";
					}
					elseif ( $attr_key=='attribute_pa_p3d_material' ) {
						$attr_st.=__( "Material" , '3dprint' )." : ".$price_request['material']."<br>";
					}
					elseif ( $attr_key=='attribute_pa_p3d_coating' ) {
						$attr_st.=__( "Coating" , '3dprint' )." : ".$price_request['coating']."<br>";
					}

					elseif ( $attr_key=='attribute_pa_p3d_model' ) {
						$upload_dir = wp_upload_dir();
						
						$link = $upload_dir['baseurl'] ."/p3d/". urlencode($attr_value) ;

						if (file_exists($upload_dir['basedir']."/p3d/$attr_value.zip")) {
							$link="$link.zip";
							$attr_value="$attr_value.zip";
						}

						$attr_st.=__( "Model" , '3dprint' )." : <a href='".$link."'>".p3d_basename( $attr_value )."</a><br>";

						$p3dmodel = str_replace('_resized', '', $attr_value);
						if (file_exists($upload_dir['basedir']."/p3d/$p3dmodel.zip")) {
							$p3dmodel_file = "$p3dmodel.zip";
							$link = $upload_dir['baseurl']."/p3d/$p3dmodel_file";
							$attr_st.=__( 'Zip File:', '3dprint' ).' <a target="_blank" href="'.$link.'">'.urldecode(urldecode($p3dmodel_file)).'</a> '.__('(Replace the model inside the archive with the resized file above)', '3dprint').'<br>';
						}
					}
					elseif ( $attr_key=='attribute_pa_p3d_unit' ) {
						$attr_st.=__( "Unit" , '3dprint' )." : ".__( $attr_value )."<br>";
					}
					else {
						$product_attributes=( $product->get_attributes() );
						$attribute_id=str_replace( 'attribute_', '', $attr_key );
						$attr_st.=$product_attributes[$attribute_id]['name'] ." : $attr_value<br>";
					}
				}
				if (isset($price_request['estimated_price'])) {
					$attr_st.= __('Estimated Price:')."  ".wc_price($price_request['estimated_price'])."<br>";
				}
				echo '
				<tr>
					<td>
						<a class="remove_request" href="javascript:void(0);" onclick="p3dRemoveRequest(\''.$product_key.'\');return false;">
							<img alt="'.__( 'Remove Request', '3dprint' ).'" title="'.__( 'Remove Request', '3dprint' ).'" src="'.plugins_url( '3dprint/images/remove.png' ).'">
						</a>
					</td>
					<td>'.$product->post->post_title.'</td>
					<td>
						'.__( 'Email:', '3dprint' ).' '.$price_request['email'].'<br>
						'.__( 'Comment:', '3dprint' ).' '.$price_request['request_comment'].'
					</td>
					<td>'.$attr_st.'</td>
					<td><span style="color:red;">*</span> <input name="p3d_buynow['.$product_key.']" type="text">'.get_woocommerce_currency_symbol().'</td>
					<td><textarea name="p3d_comments" style="width:250px;height:100px;" placeholder="'.__('Leave a comment.', '3dprint').'"></textarea></td>
				</tr>';
			}
		}
?>
				</table>
<?php
	}

?>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="new_option_name,some_other_option,option_etc" />

				<p class="submit">
					<input type="submit" class="button-primary" value="<?php _e( 'Email Quotes', '3dprint' ) ?>" />
				</p>
			</form>
		</div><!-- 3dp_tabs-4 -->
	</div><!-- 3dp_tabs -->
</div> <!-- wrap -->
<?php

}
?>
