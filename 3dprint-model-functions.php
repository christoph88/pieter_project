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


function p3d_detect_model_format( $filepath ) {
	$format="";
	$fp = fopen( $filepath, 'r' );
	$data = fread( $fp, 1024 );
	fclose( $fp );
	$lines=explode( "\n", $data );
	foreach ( $lines as $line ) {
		$line=trim( $line );
		if ( preg_match( '/^v\s+[\-\d\.]+\s+[\-\d\.]+\s+[\-\d\.]+/', $line, $matches ) ) {
			$format="obj";
			break;
		}
	}

	if ( empty( $format ) ) {
		if ( substr( $data, 0, 5 ) === "solid" && preg_match( "/facet[\s]+normal/", $data ) )
			$format = 'stl_ascii';
		else $format = 'stl_bin'; //todo: check for stl bin format
	}


	$format = apply_filters( '3dprint_detect_model_format', $format );

	return $format;
}

function p3d_signed_volume( $p1, $p2, $p3 ) {
	$v321 = $p3[0]*$p2[1]*$p1[2];
	$v231 = $p2[0]*$p3[1]*$p1[2];
	$v312 = $p3[0]*$p1[1]*$p2[2];
	$v132 = $p1[0]*$p3[1]*$p2[2];
	$v213 = $p2[0]*$p1[1]*$p3[2];
	$v123 = $p1[0]*$p2[1]*$p3[2];
	return ( 1.0/6.0 )*( -$v321 + $v231 + $v312 - $v132 - $v213 + $v123 );
}

function p3d_surface_area($p1, $p2, $p3) {
	$ax = $p2[0] - $p1[0];
	$ay = $p2[1] - $p1[1];
	$az = $p2[2] - $p1[2];
	$bx = $p3[0] - $p1[0];
	$by = $p3[1] - $p1[1];
	$bz = $p3[2] - $p1[2];
	$cx = $ay*$bz - $az*$by;
	$cy = $az*$bx - $ax*$bz;
	$cz = $ax*$by - $ay*$bx;
	return 0.5 * sqrt($cx*$cx + $cy*$cy + $cz*$cz);
}    


function p3d_get_model_stats( $filepath, $unit, $scale = 1, $printer_id = 1, $infill = 20 ) {
	$total_volume = 0;
	$surface_area = 0;
	$polygons=0;
	$skip_parse=false;
        $error_message="";
	$db_printers = p3d_get_option('3dp_printers');
	$settings =get_option('3dp_setttings');
	$printer = $db_printers[$printer_id];
	$model_key = $printer['layer_height']."_".$printer['wall_thickness']."_".$printer['nozzle_size']."_".$infill."_".$scale."_".$unit;
	if (is_numeric($scale) && $scale!=1) {
		$path_parts = pathinfo($filepath);
		$extension = $path_parts['extension'];
		$filename = $path_parts['filename'];
		$filepath_write = $path_parts['dirname'].'/'.$filename.'_resized.'.$extension;

		$resize = true; 
	}
	else $resize = false;
	$p3d_cache=p3d_get_option('3dp_cache');


	if (!$resize && is_array($p3d_cache) && count($p3d_cache[md5_file( $filepath )])) {
		$cached_stats_array=$p3d_cache[md5_file( $filepath )];
	}

	if ( is_array( $cached_stats_array ) 
	&& count( $cached_stats_array )>0 
	&& $cached_stats_array['material_volume']>0 
	&& $cached_stats_array['box_volume']>0 
	&& $cached_stats_array['surface_area']>0 
	&& $cached_stats_array['x_dim']>0 
	&& $cached_stats_array['y_dim']>0 
	&& $cached_stats_array['z_dim']>0 
	&& ($settings['api_analyse']=='on' && $cached_stats_array[$model_key]>0)
	&& !$resize ) {

		$skip_parse=true;
		$total_volume=$cached_stats_array['material_volume'];
		$surface_area=$cached_stats_array['surface_area'];
		$box_volume=$cached_stats_array['box_volume'];
		$x_dim=$cached_stats_array['x_dim'];
		$y_dim=$cached_stats_array['y_dim'];
		$z_dim=$cached_stats_array['z_dim'];
		$polygons=$cached_stats_array['polygons'];
		$error_message=$cached_stats_array['error'];

		//always return in cm
		if ( $cached_stats_array['unit']=='inch' ) {
			$total_volume=$total_volume/16.387064;
			$box_volume=$box_volume/16.387064;
			$surface_area=$surface_area/6.4516;
			$x_dim=$x_dim/2.54;
			$y_dim=$y_dim/2.54;
			$z_dim=$z_dim/2.54;
		}
	}

	if ( !$skip_parse ) {
		$file_format=p3d_detect_model_format( $filepath );

		if ( $file_format=='stl_bin' ) {
			$fp = fopen( $filepath, "rb" );
			$section = file_get_contents( $filepath, NULL, NULL, 0, 79 );
			$section1 = file_get_contents( $filepath, NULL, NULL, 0, 80 );

			if ($resize) file_put_contents( "$filepath_write", $section1 );


			if ($resize) $fw = fopen( "$filepath_write", "a" );

			fseek( $fp, 80 );
			if ($resize) fseek( $fw, 81 );
			$data = fread( $fp, 4 );
			if ($resize) fwrite($fw, $data, 4);
			$numOfFacets = unpack( "I", $data );
			for ( $i = 0; $i < $numOfFacets[1]; $i++ ) {
				//Start Normal Vector
				$data = fread( $fp, 4 );
				if ($resize) fwrite($fw, $data, 4);
				$hold = unpack( "f", $data );

				$normalVectorsX = $hold[1];
				$data = fread( $fp, 4 );
				if ($resize) fwrite($fw, $data, 4);
				$hold = unpack( "f", $data );
				$normalVectorsY = $hold[1];
				$data = fread( $fp, 4 );
				if ($resize) fwrite($fw, $data, 4);
				$hold = unpack( "f", $data );
				$normalVectorsZ = $hold[1];
				//End Normal Vector
				//Start Vertex1
				$data = fread( $fp, 4 );
				$hold = unpack( "f", $data );
				$vertex1X = $hold[1]*$scale;

				if ($resize) fwrite($fw, pack("f", $vertex1X), 4);

				$data = fread( $fp, 4 );
				$hold = unpack( "f", $data );
				$vertex1Y = $hold[1]*$scale;

				if ($resize) fwrite($fw, pack("f", $vertex1Y), 4);

				$data = fread( $fp, 4 );
				$hold = unpack( "f", $data );
				$vertex1Z = $hold[1]*$scale;

				if ($resize) fwrite($fw, pack("f", $vertex1Z), 4);

				$p1=array( $vertex1X, $vertex1Y, $vertex1Z );
				//End Vertex1
				//Start Vertex2
				$data = fread( $fp, 4 );
				$hold = unpack( "f", $data );
				$vertex2X = $hold[1]*$scale;

				if ($resize) fwrite($fw, pack("f", $vertex2X), 4);

				$data = fread( $fp, 4 );
				$hold = unpack( "f", $data );
				$vertex2Y = $hold[1]*$scale;

				if ($resize) fwrite($fw, pack("f", $vertex2Y), 4);

				$data = fread( $fp, 4 );
				$hold = unpack( "f", $data );
				$vertex2Z = $hold[1]*$scale;

				if ($resize) fwrite($fw, pack("f", $vertex2Z), 4);

				$p2=array( $vertex2X, $vertex2Y, $vertex2Z );
				//End Vertex2
				//Start Vertex3
				$data = fread( $fp, 4 );
				$hold = unpack( "f", $data );
				$vertex3X = $hold[1]*$scale;

				if ($resize) fwrite($fw, pack("f", $vertex3X), 4);

				$data = fread( $fp, 4 );
				$hold = unpack( "f", $data );
				$vertex3Y = $hold[1]*$scale;

				if ($resize) fwrite($fw, pack("f", $vertex3Y), 4);

				$data = fread( $fp, 4 );
				$hold = unpack( "f", $data );
				$vertex3Z = $hold[1]*$scale;

				if ($resize) fwrite($fw, pack("f", $vertex3Z), 4);

				$p3=array( $vertex3X, $vertex3Y, $vertex3Z );
				//End Vertex3
				//Attribute Byte Count
				$data = fread( $fp, 2 );
				if ($resize) fwrite($fw, $data, 2);
				$hold = unpack( "S", $data );
				$abc[$i] = $hold[1];

				$x_vals = array( $vertex1X, $vertex2X, $vertex3X );
				$y_vals = array( $vertex1Y, $vertex2Y, $vertex3Y );
				$z_vals = array( $vertex1Z, $vertex2Z, $vertex3Z );

				if ( !isset( $x_max ) || max( $x_vals ) > $x_max ) {
					$x_max = max( $x_vals );
				}
				if ( !isset( $y_max ) || max( $y_vals ) > $y_max ) {
					$y_max = max( $y_vals );
				}
				if ( !isset( $z_max ) || max( $z_vals ) > $z_max ) {
					$z_max = max( $z_vals );
				}
				if ( !isset( $x_min ) || min( $x_vals ) < $x_min ) {
					$x_min = min( $x_vals );
				}
				if ( !isset( $y_min ) || min( $y_vals ) < $y_min ) {
					$y_min = min( $y_vals );
				}
				if ( !isset( $z_min ) || min( $z_vals ) < $z_min ) {
					$z_min = min( $z_vals );
				}
				$polygons++;
				$total_volume+=p3d_signed_volume( $p1, $p2, $p3 );
				$surface_area+=p3d_surface_area( $p1, $p2, $p3 );
			}//for ( $i = 0; $i < $numOfFacets[1]; $i++ )

			fclose($fp);
			if ($resize) fclose($fw);
		}//if ( $file_format=='stl_bin' )
		elseif ( $file_format=='stl_ascii' ) {
			$vc=0;
			$vertexX=$vertexY=$vertexZ=array();
			$handle = fopen( $filepath, "r" );
			if ($resize) $fw = fopen( "$filepath_write", "w" );

			if ( $handle ) {
				while ( ( $line = fgets( $handle ) ) !== false ) {
					$raw_line=$line ;
					$line=trim( $line );

					if ( preg_match( "/facet[\s]+normal/", $line ) ) {
						$hold=preg_split( "/[\s]+/", $line );

						if ($resize) fwrite($fw, $raw_line);
						$normalVectorsX = (float)$hold[2];
						$normalVectorsY = (float)$hold[3];
						$normalVectorsZ = (float)$hold[4];
					}
					elseif ( preg_match( "/vertex[\s]+/", $line ) ) {
						$hold=preg_split( "/[\s]+/", $line );

						$vertexX[]=(float)$hold[1]*$scale;
						$vertexY[]=(float)$hold[2]*$scale;
						$vertexZ[]=(float)$hold[3]*$scale;


						if ($resize) fwrite($fw, sprintf("vertex %e %e %e \n", $vertexX[count($vertexX)-1], $vertexY[count($vertexY)-1], $vertexZ[count($vertexZ)-1]));

						if ( !isset( $x_max ) || max( $vertexX ) > $x_max ) {
							$x_max = max( $vertexX );
						}
						if ( !isset( $y_max ) || max( $vertexY ) > $y_max ) {
							$y_max = max( $vertexY );
						}
						if ( !isset( $z_max ) || max( $vertexZ ) > $z_max ) {
							$z_max = max( $vertexZ );
						}
						if ( !isset( $x_min ) || min( $vertexX ) < $x_min ) {
							$x_min = min( $vertexX );
						}
						if ( !isset( $y_min ) || min( $vertexY ) < $y_min ) {
							$y_min = min( $vertexY );
						}
						if ( !isset( $z_min ) || min( $vertexZ ) < $z_min ) {
							$z_min = min( $vertexZ );
						}
					} else {
						if ($resize) fwrite($fw, $raw_line);
					}


					if ( ( count( $vertexX )==3 ) && ( count( $vertexY )==3 ) && ( count( $vertexZ )==3 ) ) {
						$p1=array( $vertexX[0], $vertexY[0], $vertexZ[0] );
						$p2=array( $vertexX[1], $vertexY[1], $vertexZ[1] );
						$p3=array( $vertexX[2], $vertexY[2], $vertexZ[2] );

						$total_volume+=p3d_signed_volume( $p1, $p2, $p3 );
						$surface_area+=p3d_surface_area( $p1, $p2, $p3 );
						$vertexX=$vertexY=$vertexZ=array();
						$polygons++;
					}
				}//while ( ( $line = fgets( $handle ) ) !== false )

				fclose( $handle );
				if ($resize) fclose ($fw);
			}//if ( $handle )
		}//elseif ( $file_format=='stl_ascii' )

		elseif ( $file_format=='obj' ) {
			$vc=0;

			$vertexX=$vertexY=$vertexZ=array();
			$handle = fopen( $filepath, "r" );
			if ($resize) $fw = fopen( "$filepath_write", "w" );
			$v=1;

			if ( $handle ) {
				while ( ( $line = fgets( $handle ) ) !== false ) {
					$raw_line=$line;
					$line=trim( $line );
					if ( substr( $line, 0, 2 ) === "v " ) {

						$hold=preg_split( "/[\s]+/", $line );
						$vertexX[$v]=(float)$hold[1]*$scale;
						$vertexY[$v]=(float)$hold[2]*$scale;
						$vertexZ[$v]=(float)$hold[3]*$scale;

						if ($resize) fwrite($fw, sprintf("v %f %f %f \n", $vertexX[$v], $vertexY[$v], $vertexZ[$v]));

						if ( !isset( $x_max ) || $vertexX[$v] > $x_max ) {
							$x_max = $vertexX[$v];
						}
						if ( !isset( $y_max ) || $vertexY[$v] > $y_max ) {
							$y_max = $vertexY[$v];
						}
						if ( !isset( $z_max ) || $vertexZ[$v] > $z_max ) {
							$z_max = $vertexZ[$v];
						}
						if ( !isset( $x_min ) || $vertexX[$v] < $x_min ) {
							$x_min = $vertexX[$v];
						}
						if ( !isset( $y_min ) || $vertexY[$v] < $y_min ) {
							$y_min = $vertexY[$v];
						}
						if ( !isset( $z_min ) || $vertexZ[$v] < $z_min ) {
							$z_min = $vertexZ[$v];
						}

						$v++;
					}
					else if ( substr( $line, 0, 2 ) === "f " ) {
							$polygons++;
							$hold=preg_split( "/[\s]+/", $line );
							$hold1=explode( '/', $hold[1] );
							$hold2=explode( '/', $hold[2] );
							$hold3=explode( '/', $hold[3] );

							if ($resize) fwrite($fw, $raw_line);

							$vertex1_index=$hold1[0];
							$vertex2_index=$hold2[0];
							$vertex3_index=$hold3[0];
							if ( !isset( $hold[4] ) ) {
								$p1=array( $vertexX[$vertex1_index], $vertexY[$vertex1_index], $vertexZ[$vertex1_index] );
								$p2=array( $vertexX[$vertex2_index], $vertexY[$vertex2_index], $vertexZ[$vertex2_index] );
								$p3=array( $vertexX[$vertex3_index], $vertexY[$vertex3_index], $vertexZ[$vertex3_index] );
								$total_volume+=p3d_signed_volume( $p1, $p2, $p3 );
								$surface_area+=p3d_surface_area( $p1, $p2, $p3 );


							}
							elseif ( isset( $hold[4] ) && !isset( $hold[5] ) ) {
								$hold4=explode( '/', $hold[4] );
								$vertex4_index=$hold4[0];
								$p1=array( $vertexX[$vertex1_index], $vertexY[$vertex1_index], $vertexZ[$vertex1_index] );
								$p2=array( $vertexX[$vertex2_index], $vertexY[$vertex2_index], $vertexZ[$vertex2_index] );
								$p3=array( $vertexX[$vertex3_index], $vertexY[$vertex3_index], $vertexZ[$vertex3_index] );
								$p4=array( $vertexX[$vertex4_index], $vertexY[$vertex4_index], $vertexZ[$vertex4_index] );

								$total_volume+=p3d_signed_volume( $p1, $p2, $p3 );
								$total_volume+=p3d_signed_volume( $p1, $p3, $p4 );
								$surface_area+=p3d_surface_area( $p1, $p2, $p3 );
								$surface_area+=p3d_surface_area( $p1, $p3, $p4 );
							}
							else {
								//todo: triangulate and calculate

								$error_message=__( "<b>Warning:</b> Can't triangulate", '3dprint' );
							}
						}//else if ( $line{0}=='f' )
						else {
							if ($resize) fwrite($fw, $raw_line);
						}
				}//while ( ( $line = fgets( $handle ) ) !== false )

				fclose( $handle );
				if ($resize) fclose($fw);
			}//if ( $handle )
		}//elseif ( $file_format=='obj' )


		$x_dim = ( $x_max - $x_min )/10;
		$y_dim = ( $y_max - $y_min )/10;
		$z_dim = ( $z_max - $z_min )/10;

		$box_volume=$x_dim * $y_dim * $z_dim;




		$total_volume=$total_volume/1000; //mm3 to cm3
		$surface_area=$surface_area/100; //mm2 to cm2
	} //if (!skip_parse)

	
	if ( $unit=='inch' ) {
		$total_volume=$total_volume*16.387064;
		$box_volume=$box_volume*16.387064;
		$surface_area=$surface_area*6.4516;
		$x_dim=$x_dim*2.54;
		$y_dim=$y_dim*2.54;
		$z_dim=$z_dim*2.54;
	}




	//analysed volume
	if ( isset($p3d_cache[md5_file($filepath)][$model_key]) && (float)$p3d_cache[md5_file($filepath)][$model_key]>0) {
		$total_volume = $p3d_cache[md5_file($filepath)][$model_key]/1000;
	}

	$model_stats = array( 'material_volume'=>$total_volume, 'box_volume'=>$box_volume, 'surface_area'=>$surface_area, 'x_dim'=>$x_dim, 'y_dim'=>$y_dim, 'z_dim'=>$z_dim, 'polygons'=>$polygons, 'error'=>$error_message );

	if ($resize)
		$p3d_cache[md5_file($filepath_write)]=$model_stats;
	else		
		$p3d_cache[md5_file($filepath)]=$model_stats;
	update_option('3dp_cache', $p3d_cache);

	$model_stats = apply_filters( '3dprint_get_model_stats', $model_stats, $filepath );
	return $model_stats;
}


function p3d_calculate_printing_cost( $printer_id, $material_id, $coating_id, $product_info, $attributes ) {
	$printing_cost=$material_cost=$coating_cost=0;
	$materials_array = p3d_get_option( '3dp_materials' );
	$material = $materials_array[$material_id];
	$material_coeff = 100;
	$p3d_attr_prices=p3d_get_option('3dp_attr_prices');

	if (count($attributes)) {
		foreach ($attributes as $attr_name => $attr_value) {
			$attr_name=str_replace('attribute_', '', $attr_name);
			if (isset($p3d_attr_prices[$attr_name][$attr_value])) {
				$attr_price=(float)$p3d_attr_prices[$attr_name][$attr_value]['price'];
				$attr_price_type=$p3d_attr_prices[$attr_name][$attr_value]['price_type'];
				$attr_pct_type=$p3d_attr_prices[$attr_name][$attr_value]['pct_type'];
				if ($attr_price_type=='pct') {
					if ($attr_pct_type=='material_amount') {
						$material_coeff+=$attr_price;

					}

				}
			}
		}
	}



	if (is_numeric($coating_id)) {
		$coatings_array = p3d_get_option( '3dp_coatings' );
		$coating = $coatings_array[$coating_id];
	}
	
	$printers_array = p3d_get_option( '3dp_printers' );
	$printer = $printers_array[$printer_id];


	$printing_volume = $product_info['model']['material_volume']*($material_coeff/100);
	$weight = $material['density']*$product_info['model']['material_volume']*($material_coeff/100);

	if ( is_numeric( $material['price'] ) ) {

		if ( $material['price_type']=='cm3' ) {
			$material_cost = ( $printing_volume ) * $material['price'];
		}
		elseif ( $material['price_type']=='gram' ) {
			$material_cost = $weight * $material['price'];
		}
	}

	elseif ( strstr ($material['price'], ':' ) ) {
		$material['price']=trim($material['price']);
		$material_volume_pricing_array = explode(';', $material['price']);
		foreach ($material_volume_pricing_array as $discount_rule) {
			list ($amount, $price) = explode(':', $discount_rule);

			if ( $material['price_type']=='cm3' ) {
				if ($printing_volume >= $amount) {
					$material_cost = ( $printing_volume ) * $price;
				}
			}
			elseif ( $material['price_type']=='gram' ) {
				if ($weight >= $amount) {
					$material_cost = $weight * $price;
				}
			}
		}
	}


	if ( is_numeric( $printer['price'] ) ) {
		if ( $printer['price_type']=="material_volume" ) {
			$printing_cost = ( $printing_volume ) * $printer['price'];
		}
		elseif ( $printer['price_type']=="box_volume" ) {
			$printing_cost = $product_info['model']['box_volume'] * $printer['price'];
		}
		elseif ( $printer['price_type']=="gram" ) {
			$printing_cost = $weight * $printer['price'];
		}
		elseif ( $printer['price_type']=="sla" ) {
			$printing_cost = 100000;
		}
		elseif ( $printer['price_type']=="sls" ) {
			$printing_cost = 500000;
		}
	}
	elseif ( strstr ( $printer['price'], ':' ) ) {
		$printer['price']=trim($printer['price']);
		$printer_volume_pricing_array = explode(';', $printer['price']);
		foreach ($printer_volume_pricing_array as $discount_rule) {
			list ($amount, $price) = explode(':', $discount_rule);
			if ( $printer['price_type']=="material_volume" ) {
				if ($printing_volume >= $amount) {
					$printing_cost = ( $printing_volume ) * $price;
				}
			}
			elseif ( $printer['price_type']=="box_volume" ) {
				if ($product_info['model']['box_volume'] >= $amount) {
					$printing_cost = $product_info['model']['box_volume'] * $price;
				}
			}
			elseif ( $printer['price_type']=="gram" ) {
				if ($weight >= $amount) {				
					$printing_cost = $weight * $price;
				}
			}
		}
	}

	if ( is_numeric ( $coating_id ) ) {
		if ( is_numeric($coating['price']) ) {
			$coating_cost = $product_info['model']['surface_area'] * $coating['price'];
		}

		elseif ( strstr ( $coating['price'], ':' ) ) {
			$coating['price']=trim($coating['price']);
			$surface_area_pricing_array = explode(';', $coating['price']);

			foreach ($surface_area_pricing_array as $discount_rule) {
				list ($amount, $price) = explode(':', $discount_rule);
				if ($product_info['model']['surface_area'] >= $amount) {
					$coating_cost = $product_info['model']['surface_area'] * $price;
				}
			}
	
		}
	}


	if (count($attributes)) {
		foreach ($attributes as $attr_name => $attr_value) {
			$attr_name=str_replace('attribute_', '', $attr_name);
			if (isset($p3d_attr_prices[$attr_name][$attr_value])) {
				$attr_price=(float)$p3d_attr_prices[$attr_name][$attr_value]['price'];
				$attr_price_type=$p3d_attr_prices[$attr_name][$attr_value]['price_type'];
				$attr_pct_type=$p3d_attr_prices[$attr_name][$attr_value]['pct_type'];
				if ($attr_price_type=='pct') {
					if ($attr_pct_type=='printer') {
						$printing_cost+=($printing_cost/100)*$attr_price;
					}
					if ($attr_pct_type=='material') {
						$material_cost+=($material_cost/100)*$attr_price;
					}
					if ($attr_pct_type=='coating') {
						$coating_cost+=($coating_cost/100)*$attr_price;
					}
				}

			}
		}
	}

	$total = $printing_cost+$material_cost+$coating_cost;

	if (count($attributes)) {
		foreach ($attributes as $attr_name => $attr_value) {
			$attr_name=str_replace('attribute_', '', $attr_name);
			if (isset($p3d_attr_prices[$attr_name][$attr_value])) {
				$attr_price=(float)$p3d_attr_prices[$attr_name][$attr_value]['price'];
				$attr_price_type=$p3d_attr_prices[$attr_name][$attr_value]['price_type'];
				$attr_pct_type=$p3d_attr_prices[$attr_name][$attr_value]['pct_type'];
				if ($attr_price_type=='flat') {
					$total+=$attr_price;
				}
				else if ($attr_price_type=='pct') {
					if ($attr_pct_type=='total') { 
						$total+=($total/100)*$attr_price;
					}
				}
			}
		}
	}



	$total = apply_filters( '3dprint_calculate_printing_cost', $total, $printer_id, $material_id, $coating_id, $product_info, $attributes );
	return $total;
}

?>
