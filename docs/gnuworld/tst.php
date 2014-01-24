<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=windows-1252">
<title>ServerDate Example</title>
<!-- The following two lines are necessary to use ServerDate. -->
<script type="text/javascript">var ServerDate = new Date</script>
<script type="text/javascript" src="serverdate.php"></script>

<script type="text/javascript">
function updateClocks()
{
  var client = new Date;
  var precision = ServerDate.getPrecision();

  document.getElementById("clocks").innerHTML
    = "<table border='1'>"
    + "<tr><td>server</td><td>" + ServerDate.now()/1000 + "</td></tr>"
    + "<tr><td>client</td><td>" + client + "</td></tr>"
    + "<tr><td>difference</td><td style='text-align: right'>" + (ServerDate
      - client) + " &plusmn; " + precision + " ms</td></tr>"
    + "</table>";
}
</script>
</head>

<!-- Display the clocks and update them every second. -->
<body onload="updateClocks(); setInterval(updateClocks, 1000);">
<h1>ServerDate Example asd</h1>

<p>The precision may improve after a few seconds as a result of clock
synchronization and amortization.</p>

<div id="clocks"></div>
</body>
</html>
