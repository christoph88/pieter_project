<?php

$product_info['model']['box_volume'] = 100;
$printer['price'] = 10;

// voeg volumefactor toe
$printer_volume_pricing_string = "
  0:4.641;
  64000:3.24;
  125000:2.42;
  216000:1.9;
  343000:1.6;
  512000:1.5;
  729000:1.4;
  1000000:1.2;
  1728000:1.1
";

$printer_volume_pricing_array = explode(";",$printer_volume_pricing_string);

for ($i = 0; $i < count($printer_volume_pricing_array); $i++) {
  $discount_rule = explode(":",$printer_volume_pricing_array[$i]);
  if (count($discount_rule) == 2) {
    $amount = $discount_rule[0];
    $price = $discount_rule[1];	
    // put box_volume in cubic cm, convert to mm > * 1000
    if ($product_info['model']['box_volume']*1000 >= $amount)
      $printing_vol = $product_info['model']['box_volume'] * $price;
  }
} 
$printing_cost = 50.50 + $printing_vol * $printer['price'];

echo $printing_cost;
?>
