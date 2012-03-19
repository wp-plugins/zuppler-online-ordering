/**
 * Checks if the restaurnt with given working_hours_info array is open at the current time. 
 * Receives restaurant.working_hours_info array
 */

function isRestaurantOpen(s) {
  var d = new Date();
  var m = d.getHours()*60+d.getMinutes();
  var t = s[d.getDay() % 7];
  for (var i=0; i<t.length; i++) {
    if (t[i][0] <= m && m <= t[i][1]) return true;
  }
  return false;
};