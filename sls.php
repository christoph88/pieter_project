<?php

$product_info['model']['box_volume'] = 100;
$printer['price'] = 10;
$product_info['model']['x_dim'] = 30;
$product_info['model']['y_dim'] = 30;
$product_info['model']['z_dim'] = 2;

//printing_cost=product_info['model']['weight']*printer.data('price')*1000000000000;
// added dimensions

// standaard dimensies zijn in cm, converteren naar mm
$x = $product_info['model']['x_dim'] * 100;
$y = $product_info['model']['y_dim'] * 100;
$z = $product_info['model']['z_dim'] * 100;
$xyz = ($x * $y * $z);

function calcSLS($multiplier, $x, $y, $z, $xyz) {
      $sola = (3.14 + ( 0.0023 * $x * $z))+ ( (0.042 * (( $x*$y)/($x*10))) * ($z-1)) * $multiplier;
      $solb = (3.14 + ( 0.0023 * $x * $z))+ ( (0.042 * (( $z*$y)/($z*10))) * ($x-1)) * $multiplier;
      $solc = (3.14 + ( 0.0023 * $x * $y))+ ( (0.042 * (( $x*$z)/($x*10))) * ($y-1)) * $multiplier;
  if ($sola < $solb) {
    return $sola
  } else
  if ($solb > $solc) {
    return $solc
  } else
  {
    return $solb
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
9261001:4;
10648001:5.01;
12167001:5.21;
13824001:5.41;
15625001:5.61;
17576001:5.81;
19683001:6.05;
21952001:6.25;
24389001:6.45;
27000001:6.7;
29791001:6.9;
32768001:7.1;
35937001:7.35;
39304001:7.55;
42875001:7.75;
46656001:7.95;
50653001:8.15;
54872001:8.35;
59319001:8.55;
64000001:8.8;
125000001:10.9;
216000001:13.1;
343000001:15.3;
512000001:17.5;
729000001:19.7;
1000000001:22.2;
";

$multiplierArray = explode(";",$multiplierString);
for ($i = 0; $i < count($multiplierArray); $i++) {
  $helper = explode(":",$multiplierArray[$i]);
  if ($xyz < $helper[0]) {
    $printing_cost = calcSLS($helper[1], $x, $y, $z, $xyz);
    return $printing_cost;
    break;
  }
}
$printing_cost = $printing_cost;

?>
