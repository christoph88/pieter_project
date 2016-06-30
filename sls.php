<?php

$product_info['model']['box_volume'] = 100;
$printer['price'] = 10;
$product_info['model']['x_dim'] = 10;
$product_info['model']['y_dim'] = 10;
$product_info['model']['z_dim'] = 10;


//printing_cost=product_info['model']['weight']*printer.data('price')*1000000000000;
// added dimensions

// standaard dimensies zijn in cm, converteren naar mm
$x = $product_info['model']['x_dim'] * 100;
$y = $product_info['model']['y_dim'] * 100;
$z = $product_info['model']['z_dim'] * 100;
$xyz = $x * $y * $z;

function calcSLS($multiplier) {
  if ($z * $z< $x * $y) {
      return (3.14 + ( 0.0023 * $x * $z))+ ( (0.042 * (( $x*$y)/($x*10))) * (z-1)) * $multiplier;
  } else
  if ($x < $z) {
      return (3.14 + ( 0.0023 * $x * $z))+ ( (0.042 * (( $z*$y)/($z*10))) * ($x-1)) * $multiplier;
  } else
  {
      return (3.14 + ( 0.0023 * $x * $y))+ ( (0.042 * (( $x*$z)/($x*10))) * ($y-1)) * $multiplier;
  }
};


//if smaller than x use following multiplier
//use . for a comma
//use : to split x and multiplier
//use ; to define end of multiplier and start a new comparison
$multiplierString = "
1001:-0.3;
8001:0.3;
27001:0.5;
64001:0.62;
125001:0.8;
216001:0.9;
343001:1.05;
512001:1.2;
729001:1.32;
1000001:1.47;
1331001:1.75;
2197001:1.9;
2744001:2.05;
3375001:2.15;
4096001:2.3;
4913001:2.45;
5832001:2.6;
6859001:2.75;
8000001:2.95;
15625001:4;
120000001:5;
";

$multiplierArray = explode(";",$multiplierString);
for ($i = 0; $i < count($multiplierArray); $i++) {
  $helper = explode(":",$multiplierArray[$i]);
  if ($xyz < $helper[0]) {
    $printing_cost = calcSLS($helper[1]);
    return $printing_cost;
    break;
  }
}

echo $printing_cost;

?>
