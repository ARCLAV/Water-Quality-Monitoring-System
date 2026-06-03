<?php
session_start();


// ===== LOGIN CHECK =====
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

// ===== DATABASE CONNECTION =====
$conn = mysqli_connect("localhost", "root", "", "water_quality");
if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ===== SEND PDF FUNCTION =====
if (isset($_POST['send_pdf'])) {

    require_once(__DIR__ . '/tcpdf/tcpdf.php');
    require_once(__DIR__ . '/PHPMailer/src/PHPMailer.php');
    require_once(__DIR__ . '/PHPMailer/src/SMTP.php');
    require_once(__DIR__ . '/PHPMailer/src/Exception.php');

    // ===== CREATE PDF =====
    $pdf = new TCPDF();
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 10);

  $html = "
  <h2 style='text-align:center;'>Water Quality History Report</h2>
  <br>
  <table border='1' cellpadding='6' cellspacing='0' width='100%'>
        <thead>
            <tr style='background-color:#1e3a8a; color:white; font-weight:bold;'>
                <th width='10%'>ID</th>
                <th width='20%'>Turbidity</th>
                <th width='20%'>Temperature</th>
                <th width='20%'>TDS</th>
                <th width='30%'>Timestamp</th>
            </tr>
        </thead>
        <tbody>
";

$sql = "SELECT * FROM sensor_data ORDER BY id DESC LIMIT 10";
$result = mysqli_query($conn, $sql);

while ($row = mysqli_fetch_assoc($result)) {

    $html .= "
        <tr>
            <td>{$row['id']}</td>
            <td>{$row['turbidity']}</td>
            <td>{$row['temperature']}</td>
            <td>{$row['tds']}</td>
            <td>{$row['timestamp']}</td>
        </tr>
    ";
}

$html .= "
    </tbody>
</table>
<br><br>
<p style='text-align:right; font-size:10px;'>Generated on: " . date("Y-m-d H:i:s") . "</p>
";
    $html .= "</table>";

    $pdf->writeHTML($html);

    $filePath = __DIR__ . "/report.pdf";
    $pdf->Output($filePath, 'F');

    // ===== SEND EMAIL =====
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = ''; // CHANGE
        $mail->Password   = ''; // USE APP PASSWORD
        $mail->SMTPSecure = '';
        $mail->Port       = ;

        $mail->setFrom('Add Email', 'Water Monitoring System');
        $mail->addAddress('Add Email');
        $mail->addAddress('Add Email');
        $mail->addAddress('Add Email');

        $mail->Subject = 'Water Quality PDF Report';
        $mail->Body    = 'Attached is the latest water quality history report.';
        $mail->addAttachment($filePath);

        $mail->send();

        // Delete the temporary PDF file
        unlink($filePath);

        

    } catch (Exception $e) {
        echo "Mailer Error: {$mail->ErrorInfo}";
    }

    unlink($filePath);
}
?>

<!DOCTYPE html>
<html>
<head>
<title>Water Quality Monitoring</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body { font-family: Arial; background:#f5f7fa; margin:0; }
.header { background:#1e3a8a; color:white; padding:30px; text-align:center; }
.main-wrapper { display:flex; padding:40px; gap:30px; }
.left-panel { width:30%; display:flex; flex-direction:column; gap:20px; }
.right-panel { width:70%; }

/* NEW HEADER TOP BAR */
.top-bar {
    position:absolute;
    top:20px;
    right:30px;
    display:flex;
    align-items:center;
    gap:15px;
}

.username {
    font-size:14px;
    opacity:0.9;
}

.logout-btn {
    background:white;
    color:#1e3a8a;
    padding:8px 15px;
    border-radius:8px;
    text-decoration:none;
    font-weight:bold;
    transition:0.3s;
}

.logout-btn:hover {
    background:#e0e7ff;
}

.card {
    background:white;
    padding:25px;
    border-radius:15px;
    box-shadow:0 8px 20px rgba(0,0,0,0.08);
    text-align:center;
}

.progress-bar {
    width:85%;
    height:10px;
    background:#e5e7eb;
    border-radius:6px;
    overflow:hidden;
    margin:8px auto 0 auto;
}

.progress-fill {
    height:100%;
    width:0%;
    transition:0.5s ease-in-out;
    border-radius:6px;
}

.green { background:green; }
.blue { background:blue; }
.red { background:red; }

table {
    width:100%;
    border-collapse:collapse;
    margin-top:20px;
}

.pdf-btn {
    background: linear-gradient(135deg, #2563eb, #1e40af);
    color: white;
    padding: 12px 30px;
    border: none;
    border-radius: 10px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: 0.3s ease;
}

.pdf-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 18px rgba(0,0,0,0.15);
}
th, td {
    border:1px solid #ddd;
    padding:8px;
    text-align:center;
}

th { background:#1e3a8a; color:white; }

canvas { height:400px !important; }

footer { background:#0077b6; color:white; text-align:center; padding:15px; }
</style>
</head>

<body>

<div class="header">
<h1>Water Quality Monitoring System</h1>

<!-- NEW USER + LOGOUT SECTION -->
<div class="top-bar">
    <div class="username">
        Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?>
    </div>
    <a href="logout.php" class="logout-btn">Logout</a>
</div>

</div>

<div class="main-wrapper">

<div class="left-panel">

<div class="card">
<h3>Water Status</h3>
<h2 id="status">--</h2>
</div>

<div class="card">
<h3>TDS (ppm)</h3>
<h2 id="tds">--</h2>
<div class="progress-bar"><div id="tdsBar" class="progress-fill"></div></div>
</div>

<div class="card">
<h3>Turbidity (NTU)</h3>
<h2 id="turbidity">--</h2>
<div class="progress-bar"><div id="turbBar" class="progress-fill"></div></div>
</div>

<div class="card">
<h3>Temperature (°C)</h3>
<h2 id="temperature">--</h2>
<div class="progress-bar"><div id="tempBar" class="progress-fill"></div></div>
</div>

<div class="card">
<h3>WQI</h3>
<h2 id="wqi">--</h2>
</div>

</div>

<div class="right-panel">
<div class="card">
<h3>Live Water Quality Index Chart</h3>
<canvas id="chart"></canvas>

<h3>Recent Readings (Last 6)</h3>
<table id="historyTable">
<thead>
<tr>
<th>Time</th>
<th>TDS</th>
<th>Turbidity</th>
<th>Temp</th>
<th>WQI</th>
<th>Status</th>
</tr>
</thead>
<tbody></tbody>
</table>
<div style="text-align:center; margin-top:25px;">
    <button onclick="generatePDF()" class="pdf-btn">
        📄 Generate PDF
    </button>
</div>
</div>
</div>
</div>

</div>

<footer>© 2026 Water Quality Monitoring</footer>

<script>
let alertSent = false;

let chartData = {
labels: [],
datasets: [{
label: "WQI",
data: [],
borderColor: "blue",
borderWidth: 2
}]
};

let myChart = new Chart(document.getElementById("chart"), {
type: "line",
data: chartData,
options: { responsive:true }
});

function fetchData(){

fetch("get.php")
.then(res=>res.json())
.then(data=>{

let turbidity=parseFloat(data.turbidity);
let temperature=parseFloat(data.temperature);
let tds=parseFloat(data.tds);

/* ALWAYS SHOW TURBIDITY */
document.getElementById("turbidity").innerText=turbidity.toFixed(2);

let turbBar=document.getElementById("turbBar");

/* ===== TURBIDITY LOGIC ===== */

if(turbidity <= 10){

    turbBar.className="progress-fill green";
    turbBar.style.width="20%";

}
else if(turbidity > 10 && turbidity <= 120){

    document.getElementById("status").innerText="No Water Detected";

    document.getElementById("tds").innerText="--";
    document.getElementById("temperature").innerText="--";
    document.getElementById("wqi").innerText="--";

    document.getElementById("tdsBar").style.width="0%";
    document.getElementById("tempBar").style.width="0%";

    turbBar.className="progress-fill blue";
    turbBar.style.width="100%";

    alertSent = false; // RESET ALERT
    return;
}
else{

    turbBar.className="progress-fill red";
    turbBar.style.width="100%";
}

/* ===== TDS LOGIC ===== */

document.getElementById("tds").innerText=tds.toFixed(0);

let tdsStatus="";
let tdsBar=document.getElementById("tdsBar");

if(turbidity > 120){

    tdsStatus="Not Drinkable";
    tdsBar.className="progress-fill red";

    if(!alertSent){
        fetch("check_alert.php");
        alertSent = true;
    }

}
else if(tds <= 450){

    tdsStatus="Drinkable";
    tdsBar.className="progress-fill green";
    alertSent = false; // RESET WHEN SAFE

}
else{

    tdsStatus="Not Drinkable";
    tdsBar.className="progress-fill red";

    if(!alertSent){
        fetch("check_alert.php");
        alertSent = true;
    }
}

tdsBar.style.width=Math.min((tds/1000)*100,100)+"%";

/* ===== TEMPERATURE ===== */
let tempBar=document.getElementById("tempBar");
let tempStatus="";

if(temperature < 25){
    tempStatus="Cold";
    tempBar.className="progress-fill blue";
}
else if(temperature <= 31){
    tempStatus="Normal";
    tempBar.className="progress-fill green";
}
else{
    tempStatus="Hot";
    tempBar.className="progress-fill red";
}

tempBar.style.width=Math.min((temperature/60)*100,100)+"%";

document.getElementById("temperature").innerText=
temperature.toFixed(2)+" °C ("+tempStatus+")";



/* ===== STATUS ===== */
document.getElementById("status").innerText=tdsStatus;

/* ===== WQI ===== */
let Qturb=(turbidity/200)*100;
let Qtemp=(temperature/35)*100;
let Qtds=(tds/1000)*100;
let WQI=(Qturb+Qtemp+Qtds)/3;
document.getElementById("wqi").innerText=WQI.toFixed(2);

/* ===== CHART ===== */
let time=new Date().toLocaleTimeString();
chartData.labels.push(time);
chartData.datasets[0].data.push(WQI);

if(chartData.labels.length>6){
chartData.labels.shift();
chartData.datasets[0].data.shift();
}
myChart.update();

/* ===== TABLE ===== */
let table=document.getElementById("historyTable").getElementsByTagName('tbody')[0];
let row=table.insertRow(0);

row.insertCell(0).innerText=time;
row.insertCell(1).innerText=tds.toFixed(0);
row.insertCell(2).innerText=turbidity.toFixed(2);
row.insertCell(3).innerText=temperature.toFixed(2);
row.insertCell(4).innerText=WQI.toFixed(2);
row.insertCell(5).innerText=tdsStatus;

if(table.rows.length>6){
table.deleteRow(6);
}

});
}

setInterval(fetchData,2000);

function generatePDF(){
let btn=document.querySelector(".pdf-btn");
btn.disabled=true;
btn.innerText="Generating...";

fetch("index.php",{
method:"POST",
headers:{"Content-Type":"application/x-www-form-urlencoded"},
body:"send_pdf=1"
})
.then(res=>res.text())
.then(()=>{
alert("PDF Generated & Sent Successfully!");
btn.disabled=false;
btn.innerText="📄 Generate PDF";
});
}
</script>

</body>
</html>
