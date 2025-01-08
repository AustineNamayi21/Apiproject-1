<?php
	//prevent user from accessing this page without logging in
	session_start();
	if (!isset($_SESSION['email'],$_SESSION['id'])) {
		header("location:login.php");
	}

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Home</title>
</head>
<body>

	<a href="logout.php">Logout</a>

	<h1>Task App</h1>
	<?php
		
		$email = $_SESSION['email'];
		echo "Welcome $email";

	?>
<h2>Add Task</h2>
<form method="post" action="addtask.php">
	<label>Task Name: </label>
	<input type="text" name="task_name">
	<label>Due Date: </label>
	<input type="date" name="due_date">
	<input type="submit" name="add" value="Add Task">
</form>
<h2>My Tasks</h2>
<table>
	<tr>
		<th>Task Name</th>
		<th>Due Date</th>
		<th>Completed</th>
	</tr>
<?php
include 'connect.php';
$user_id = $_SESSION['id'];
$sql = "SELECT * FROM tasks WHERE user_id=$user_id";
$result = mysqli_query($connect, $sql);
if ($result) {
	while($task = mysqli_fetch_assoc($result)){
	$task_name = $task['task_name'];
	$due_date = $task['due_date'];
	echo "<tr>
		<td>$task_name</td>
		<td>$due_date</td>
		<td><input type='checkbox'></td>
	</tr>";
	}
	}

?>
</table>
</body>
</html>