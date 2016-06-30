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


//if smaller than x use following multiplier
//use . for a comma
//use : to split x and multiplier
//use ; to define end of multiplier and start a new comparison
var multiplierString = `
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
`

var multiplierArray = multiplierString.split(';');
for (var i = 0; i < multiplierArray.length; i++) {
  var helper = multiplierArray[i].split(':');
  if (xyz < helper[0]) {
    calcSLS(helper[1]);
    break;
  }
}
