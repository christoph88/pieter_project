var x = process.argv[2];
var y = process.argv[3];
var z = process.argv[4];

var xyz = x * y * z;

if (xyz < 1001) {console.log('-0,3')} else
if (xyz < 8001) {console.log('0,3')} else
if (xyz < 27001) {console.log('0,5')} else
if (xyz < 64001) {console.log('0,62')} else
if (xyz < 125001) {console.log('0,8')} else
if (xyz < 216001) {console.log('0,9')} else
if (xyz < 343001) {console.log('1,05')} else
if (xyz < 512001) {console.log('1,2')} else
if (xyz < 729001) {console.log('1,32')} else
if (xyz < 1000001) {console.log('1,47')} else
if (xyz < 1331001) {console.log('1,75')} else
if (xyz < 2197001) {console.log('1,9')} else
if (xyz < 2744001) {console.log('2,05')} else
if (xyz < 3375001) {console.log('2,15')} else
if (xyz < 4096001) {console.log('2,3')} else
if (xyz < 4913001) {console.log('2,45')} else
if (xyz < 5832001) {console.log('2,6')} else
if (xyz < 6859001) {console.log('2,75')} else
if (xyz < 8000001) {console.log('2,95')} else
if (xyz < 15625001) {console.log('4')} else
if (xyz < 120000001) {console.log('5')} 
