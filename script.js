
function validateForm() {
    let email = document.forms["form"]["email"].value;
    let password = document.forms["form"]["password"].value;

    if (email == "" || password == "") {
        alert("All fields are required!");
        return false;
    }
}
function changeInputType(){

let ex = document.getElementById("exercise").value;
let field = document.getElementById("valueField");

if(ex === "Running" || ex === "Cycling"){
    field.placeholder = "Distance (km)";
    field.step = "0.1";   // allows decimal like 2.5 km
}else{
    field.placeholder = "Reps";
    field.step = "1";
}
}
// RUN TRACKER
let runWatch = null;
let runLast = null;
let runDist = 0;
let runTime = 0;
let runTimer = null;

function startRun(){
    runDist = 0;
    runTime = 0;

    runTimer = setInterval(()=>{
        runTime++;
        document.getElementById("runTime").innerText = runTime;
    },1000);

    runWatch = navigator.geolocation.watchPosition(pos=>{
        let lat = pos.coords.latitude;
        let lon = pos.coords.longitude;

        if(runLast){
            let d = getDistance(runLast.lat, runLast.lon, lat, lon);
            runDist += d;

            let km = (runDist/1000).toFixed(2);
            document.getElementById("runDistance").innerText = km;

            let cal = (km * 60).toFixed(2);
            document.getElementById("runCal").innerText = cal;
        }

        runLast = {lat, lon};

    }, error=>{
        alert("Enable location!");
    });
}

function stopRun(){
    navigator.geolocation.clearWatch(runWatch);
    clearInterval(runTimer);
    runLast = null;

    saveRun();
}