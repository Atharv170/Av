<?php 
session_start(); 
include 'db.php'; 

if(!isset($_SESSION['user_id'])){
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// STEP DATA
$labels = [];
$stepsData = [];

$res = $conn->query("SELECT date, SUM(steps) as total 
FROM steps WHERE user_id='$user_id' GROUP BY date");

if($res){
    while($r=$res->fetch_assoc()){
        $labels[] = $r['date'];
        $stepsData[] = $r['total'];
    }
}
// STEP CALORIES (ADD HERE)
$totalSteps = array_sum($stepsData);
$stepCalories = $totalSteps * 0.04;

// WORKOUT DATA
$workoutLabels = [];
$workoutData = [];
// RUNNING DATA (ADD HERE)
$runLabels = [];
$runDistance = [];

$resRun = $conn->query("
    SELECT date, SUM(value) as dist 
    FROM workouts 
    WHERE user_id='$user_id' AND exercise='Running'
    GROUP BY date
");

if($resRun){
    while($r=$resRun->fetch_assoc()){
        $runLabels[] = $r['date'];
        $runDistance[] = $r['dist'];
    }
}

// EMPTY FIX
if(empty($runLabels)){
    $runLabels=["No Data"];
    $runDistance=[0];
}
$res2 = $conn->query("SELECT date, SUM(calories) as total 
FROM workouts WHERE user_id='$user_id' GROUP BY date");

if($res2){
    while($r=$res2->fetch_assoc()){
        $workoutLabels[] = $r['date'];
        $workoutData[] = $r['total'];
    }
}

// EMPTY DATA FIX
if(empty($labels)){ $labels=["No Data"]; $stepsData=[0]; }
if(empty($workoutLabels)){ $workoutLabels=["No Data"]; $workoutData=[0]; }

// TOTAL WORKOUT CALORIES
$res3 = $conn->query("SELECT SUM(calories) as total FROM workouts WHERE user_id='$user_id'");
$row3 = $res3->fetch_assoc();
$workoutCal = $row3['total'] ? $row3['total'] : 0;
$totalCalories = $workoutCal + $stepCalories;
?>

<!DOCTYPE html>
<html>
<head>
<title>Fitness Arc</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<style>
body { background:#f4f6f9; }

.navbar {
background: linear-gradient(to right,#2b5876,#4e4376);
}

.box {
background:white;
padding:20px;
border-radius:12px;
margin-top:20px;
}

.section { display:none; }
#home { display:block; }

.card-box {
color:white;
padding:20px;
border-radius:10px;
text-align:center;
}

.red { background:#e74c3c; }
.green { background:#27ae60; }
.blue { background:#3498db; }
</style>
</head>

<body>

<nav class="navbar navbar-dark p-3">
<span class="navbar-brand">🏋 Fitness Arc</span>

<div>
<button onclick="showSection('home')" class="btn btn-light btn-sm">Home</button>
<button onclick="showSection('workout')" class="btn btn-light btn-sm">Workouts</button>
<button onclick="showSection('diet')" class="btn btn-light btn-sm">Diet</button>
<button onclick="showSection('history')" class="btn btn-light btn-sm">History</button>
<a href="logout.php" class="btn btn-danger btn-sm">Logout</a>
</div>
</nav>

<div class="container">

<!-- HOME -->
<div id="home" class="section">

<div class="box">
<h4>🏃 Running Tracker</h4>

<button onclick="startRun()" class="btn btn-success w-100">Start Run</button>
<button onclick="stopRun()" class="btn btn-danger w-100 mt-2">Stop Run</button>

<h5>Distance: <span id="runDistance">0</span> km</h5>
<h5>Time: <span id="runTime">0</span> sec</h5>
<h5>Calories: <span id="runCal">0</span></h5>
</div>

<div class="box">
<h4>Step Counter</h4>
<button onclick="startTracking()" class="btn btn-success w-100">Start</button>
<button onclick="stopTracking()" class="btn btn-danger w-100 mt-2">Stop</button>

<h5>Steps: <span id="steps">0</span></h5>
<h6>Distance: <span id="distance">0</span> km</h6>
</div>

<div class="box">
<h4>Add Workout</h4>

<form action="add_workout.php" method="POST">

<select name="exercise" id="exercise" class="form-control mb-2" onchange="changeInputType()" required>
<option value="">Select Workout</option>
<option value="Push Ups">💪 Push Ups</option>
<option value="Squats">🏋 Squats</option>
<option value="Running">🏃 Running</option>
<option value="Cycling">🚴 Cycling</option>
<option value="Yoga">🧘 Yoga</option>
<option value="Jump Rope">🤾 Jump Rope</option>
</select>

<input id="valueField" name="value" type="number" class="form-control mb-2" placeholder="Reps" required>

<input name="sets" type="number" class="form-control mb-2" placeholder="Sets" required>

<button class="btn btn-success w-100">Add</button>

</form>
</div>

<div class="box">
<h4>BMI Calculator</h4>

<input id="weight" type="number" class="form-control mb-2" placeholder="Weight (kg)">
<input id="height" type="number" class="form-control mb-2" placeholder="Height (cm)">

<button onclick="calcBMI()" class="btn btn-primary w-100">Calculate</button>

<p>BMI: <span id="bmi"></span></p>
<p>Status: <span id="status"></span></p>
</div>

</div>

<!-- WORKOUT -->
<div id="workout" class="section">

<div class="row text-center">
<div class="col-md-4"><div class="card-box red">Weight <br><span id="weightBox">0</span></div></div>
<div class="col-md-4"><div class="card-box green">Calories <br><span id="calories"><?php echo round($totalCalories,2);?>0</span></div></div>
<div class="col-md-4"><div class="card-box blue">BMI <br><span id="bmiBox">--</span></div></div>
</div>

<div class="box"><canvas id="stepChart"></canvas></div>
<div class="box"><canvas id="workoutChart"></canvas></div>
<div class="box"><h4>🏃 Running Distance</h4><canvas id="runChart"></canvas></div>

</div>

<!-- DIET -->
<div id="diet" class="section">
<div class="box">
<h4>Diet Suggestion</h4>
<p id="dietText">Calories-based diet will appear here</p>
</div>
</div>

<!-- HISTORY -->
<div id="history" class="section">
<div class="box">
<h4>Workout History</h4>

<table class="table table-bordered">

<tr>
<th>Date</th>
<th>Steps</th>
<th>Exercise</th>
<th>Value</th>
<th>Sets</th>
<th>Action</th>
</tr>

<?php

$res = $conn->query("SELECT 
    w.id,
    w.date,
    w.exercise,
    w.value,
    w.sets,

    IFNULL(s.total_steps,0) as steps,

    IFNULL(r.total_run,0) as running

FROM workouts w

LEFT JOIN
(
    SELECT date, SUM(steps) as total_steps
    FROM steps
    WHERE user_id='$user_id'
    GROUP BY date
) s ON w.date = s.date

LEFT JOIN
(
    SELECT date, SUM(value) as total_run
    FROM workouts
    WHERE user_id='$user_id' AND exercise='Running'
    GROUP BY date
) r ON w.date = r.date

WHERE w.user_id='$user_id'

ORDER BY w.date DESC
");

if($res){
    while($row = $res->fetch_assoc()){
        echo "<tr>
          <td>{$row['date']}</td>
          <td>{$row['steps']}</td>
          <td>{$row['exercise']}</td>
          <td>{$row['value']}</td>
          <td>{$row['sets']}</td>

<td>
<a href='edit_workout.php?id={$row['id']}' class='btn btn-warning btn-sm'>Edit</a>

<a href='delete_workout.php?id={$row['id']}' 
class='btn btn-danger btn-sm'
onclick='return confirm(\"Delete this record?\")'>
Delete
</a>
</td>

</tr>";
    }
}

?>

</table>

</div>
</div>

</div>

<script>

let workoutCalories = <?php echo $totalCalories; ?>;

// SECTION SWITCH
function showSection(sec){
document.querySelectorAll(".section").forEach(s=>s.style.display="none");
document.getElementById(sec).style.display="block";
}

// BMI
function calcBMI(){
let w = parseFloat(weight.value);
let h = parseFloat(height.value)/100;

if(!w || !h){
alert("Enter valid values");
return;
}

let bmi = (w/(h*h)).toFixed(2);

document.getElementById("bmi").innerText = bmi;
document.getElementById("bmiBox").innerText = bmi;
document.getElementById("weightBox").innerText = w;

let s = bmi<18.5?"Underweight":bmi<24.9?"Normal":bmi<29.9?"Overweight":"Obese";
document.getElementById("status").innerText = s;
}
//RUN COUNTER
function saveRun(){
    let dist = document.getElementById("runDistance").innerText;
    let time = document.getElementById("runTime").innerText;
    let cal = document.getElementById("runCal").innerText;

    fetch("save_run.php", {
        method:"POST",
        headers:{"Content-Type":"application/x-www-form-urlencoded"},
        body:`distance=${dist}&time=${time}&calories=${cal}`
    })
    .then(res=>res.text())
    .then(data=>console.log(data));
}

// STEP COUNTER (SIMPLE)
let watchId,last=null,dist=0,steps=0;

function startTracking(){
watchId = navigator.geolocation.watchPosition(function(pos){

let lat=pos.coords.latitude;
let lon=pos.coords.longitude;

if(last){
let d=getDistance(last.lat,last.lon,lat,lon);
dist+=d;

steps=Math.floor(dist/0.75);

document.getElementById("steps").innerText=steps;
document.getElementById("distance").innerText=(dist/1000).toFixed(2);

let cal = (steps * 0.04).toFixed(2);

let currentCal = parseFloat(document.getElementById("calories").innerText);
currentCal += parseFloat(cal);

document.getElementById("calories").innerText = currentCal.toFixed(2);

// UPDATE GLOBAL VARIABLE
workoutCalories = currentCal;

// UPDATE DIET
updateDiet();
}

last={lat:lat,lon:lon};

});
}

function stopTracking(){
navigator.geolocation.clearWatch(watchId);
last=null;
}

// DISTANCE
function getDistance(a,b,c,d){
let R=6371000;
let dLat=(c-a)*Math.PI/180;
let dLon=(d-b)*Math.PI/180;

let x=Math.sin(dLat/2)**2+
Math.cos(a*Math.PI/180)*Math.cos(c*Math.PI/180)*
Math.sin(dLon/2)**2;

return R*(2*Math.atan2(Math.sqrt(x),Math.sqrt(1-x)));
}

// DIET
function updateDiet(){

let d = "";

if(workoutCalories < 200){
    d = `
    🥗 <b>Light Indian Diet</b><br>
    Morning: Warm water + lemon 🍋<br>
    Breakfast: Poha / Upma<br>
    Lunch: 2 Roti + Sabji + Dal<br>
    Evening: Coconut water 🥥<br>
    Dinner: Khichdi / Soup
    `;
}
else if(workoutCalories < 500){
    d = `🍛 <b>Balanced Indian Diet</b><br>
    Morning: Soaked almonds + warm water<br>
    Breakfast: Paratha + curd<br>
    Lunch: 2-3 Roti + Dal + Rice + Sabji<br>
    Evening: Tea + roasted chana<br>
    Dinner: Roti + Paneer/Vegetables`;
}
else if(workoutCalories < 800){
    d = ` 💪 <b>High Protein Indian Diet</b><br>
    Morning: Banana + milk 🍌🥛<br>
    Breakfast: Oats / Paneer sandwich<br>
    Lunch: Rice + Chicken / Paneer + Dal<br>
    Evening: Boiled eggs / sprouts<br>
    Dinner: Roti + Paneer / Chicken`;
}
else{
    d = `🔥 <b>Muscle Gain Indian Diet</b><br>
    Morning: Banana shake 🥤<br>
    Breakfast: 4 Eggs / Paneer bhurji<br>
    Lunch: Rice + Chicken + Dal + Ghee<br>
    Evening: Peanut butter sandwich<br>
    Dinner: Roti + Chicken / Paneer + Milk`;
}

document.getElementById("dietText").innerHTML = d;
console.log("Calories:", workoutCalories);
}

// CHANGE INPUT TYPE
function changeInputType(){
let ex=document.getElementById("exercise").value;
let field=document.getElementById("valueField");

if(ex==="Running"||ex==="Cycling"){
field.placeholder="Distance (km)";
field.step="0.1";
}else{
field.placeholder="Reps";
field.step="1";
}
}

// CHARTS
// RUNNING CHART (ADD HERE)
new Chart(document.getElementById("runChart"),{
    type:"line",
    data:{
        labels: <?php echo json_encode($runLabels); ?>,
        datasets:[{
            label:"Running Distance (km)",
            data: <?php echo json_encode($runDistance); ?>,
            borderColor:"#e1802b",
            backgroundColor:"rgba(230,126,34,0.2)",
            tension:0.4
        }]
    }
});
new Chart(document.getElementById("stepChart"),{
type:"line",
data:{labels: <?php echo json_encode($labels); ?>,
datasets:[{label:"Steps",data: <?php echo json_encode($stepsData); ?>}]}
});

new Chart(document.getElementById("workoutChart"),{
type:"bar",
data:{labels: <?php echo json_encode($workoutLabels); ?>,
datasets:[{label:"Calories Burned",data: <?php echo json_encode($workoutData); ?>}]}
});
updateDiet();
</script>

</body>
</html>