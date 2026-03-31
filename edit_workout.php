<?php
include 'db.php';

$id = $_GET['id'];
$res = $conn->query("SELECT * FROM workouts WHERE id='$id'");
$row = $res->fetch_assoc();
?>

<form method="POST">
<input type="text" name="exercise" value="<?php echo $row['exercise']; ?>" class="form-control mb-2">
<input type="number" name="value" value="<?php echo $row['value']; ?>" class="form-control mb-2">
<input type="number" name="sets" value="<?php echo $row['sets']; ?>" class="form-control mb-2">

<button name="update" class="btn btn-success">Update</button>
</form>

<?php
if(isset($_POST['update'])){
$exercise = $_POST['exercise'];
$value = $_POST['value'];
$sets = $_POST['sets'];

$conn->query("UPDATE workouts SET 
exercise='$exercise',
value='$value',
sets='$sets'
WHERE id='$id'");

header("Location: dashboard.php");
}
?>