var x = process.argv[2];
var y = process.argv[3];
var z = process.argv[4];
var xyz = x * y * z;

var calcSLS = function(multiplier) {
  console.log("het volume is: " + xyz);
  console.log("SLS uitkomst: ");
  if (z * z< x * y) {
    console.log("A:");
    console.log(
      (3.14 + ( 0.0023 * x * z))+ ( (0.042 * (( x*y)/(x*10))) * (z-1)) * multiplier
    );
  } else
  if (x < z) {
    console.log("B:");
    console.log(
      (3.14 + ( 0.0023 * x * z))+ ( (0.042 * (( z*y)/(z*10))) * (x-1)) * multiplier
    );
  } else
  {
    console.log("C:");
    console.log(
      (3.14 + ( 0.0023 * x * y))+ ( (0.042 * (( x*z)/(x*10))) * (y-1)) * multiplier
    );
  }
};


if (xyz < 1001) {
  var multiplier = -0.3;
  calcSLS(multiplier);
} else
if (xyz < 8001) {
  var multiplier = 0.3;
  calcSLS(multiplier);
} else
if (xyz < 27001) {
  var multiplier = 0.5;
  calcSLS(multiplier);
} else
if (xyz < 64001) {
	var multiplier = 0.62;
  calcSLS(multiplier);
} else
if (xyz < 125001) {
	var multiplier = 0.8;
  calcSLS(multiplier);
} else
if (xyz < 216001) {
	var multiplier = 0.9;
  calcSLS(multiplier);
} else
if (xyz < 343001) {
	var multiplier = 1.05;
  calcSLS(multiplier);
} else
if (xyz < 512001) {
	var multiplier = 1.2;
  calcSLS(multiplier);
} else
if (xyz < 729001) {
	var multiplier = 1.32;
  calcSLS(multiplier);
} else
if (xyz < 1000001) {
	var multiplier = 1.47;
  calcSLS(multiplier);
} else
if (xyz < 1331001) {
	var multiplier = 1.75;
  calcSLS(multiplier);
} else
if (xyz < 2197001) {
	var multiplier = 1.9;
  calcSLS(multiplier);
} else
if (xyz < 2744001) {
	var multiplier = 2.05;
  calcSLS(multiplier);
} else
if (xyz < 3375001) {
	var multiplier = 2.15;
  calcSLS(multiplier);
} else
if (xyz < 4096001) {
	var multiplier = 2.3;
  calcSLS(multiplier);
} else
if (xyz < 4913001) {
	var multiplier = 2.45;
  calcSLS(multiplier);
} else
if (xyz < 5832001) {
	var multiplier = 2.6;
  calcSLS(multiplier);
} else
if (xyz < 6859001) {
	var multiplier = 2.75;
  calcSLS(multiplier);
} else
if (xyz < 8000001) {
	var multiplier = 2.95;
  calcSLS(multiplier);
} else
if (xyz < 15625001) {
	var multiplier = 4;
  calcSLS(multiplier);
} else
if (xyz < 120000001) {
	var multiplier = 5;
  calcSLS(multiplier);
} 
