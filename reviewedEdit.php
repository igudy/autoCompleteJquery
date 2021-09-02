<?php
session_start();
require_once "pdo.php";
require_once "util.php";

//check logged in
if ( ! isset($_SESSION['user_id']) ) {
  die('ACCESS DENIED');
}

// If the user cancels
if ( isset($_POST['cancel']) ) {
    header('Location: index.php');
    return;
}

// Guardian: Make sure that REQUEST parameter (profile_id) is present
if ( ! isset($_REQUEST['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

//Load up the profile in question
$stmt = $pdo->prepare("SELECT * FROM Profile 
	WHERE profile_id = :profile_id AND user_id = :uid");
$stmt->execute(array(":profile_id" => $_REQUEST['profile_id'], 
	':uid' => $_SESSION['user_id']));
$profile = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $profile === false ) {
    $_SESSION['error'] = 'Could not load profile';
    header( 'Location: index.php' ) ;
    return;
}

$fn = $profile['first_name'];
$ln = $profile['last_name'];
$em = $profile['email'];
$he = $profile['headline'];
$su = $profile['summary'];
$uid = $profile['user_id'];
$profile_id = $profile['profile_id'];

if ( isset($_POST['save']) ) {
	if ( isset($_POST['first_name']) || isset($_POST['last_name']) || isset($_POST['email']) 
		|| isset($_POST['headline']) || isset($_POST['summary']))
	{	

		$msg = validateProfile();
		if (is_string($msg)){
			$_SESSION['error'] = $msg;
			header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
			return;
		}
		
		//Validate position entries if present
		$msg = validatePos();
		if (is_string($msg)){
			$_SESSION['error'] = $msg;
			header("Location: edit.php?profile_id=".$_REQUEST['profile_id']);
			return;
		}
		
		if (isset($_POST['save']))
		{
			$stmt = $pdo->prepare("UPDATE Profile SET 
						first_name = :fn,
						last_name = :ln,
						email = :em,
						headline = :he,
						summary = :su,
						user_id = :uid
					WHERE profile_id = :profile_id AND user_id=:uid"
			);
			$stmt->execute(array(	
				':profile_id' => $_REQUEST['profile_id'],		
				':uid' => $_SESSION['user_id'],	
				':fn' => $_POST['first_name'],
				':ln' => $_POST['last_name'],
				':em' => $_POST['email'],
				':he' => $_POST['headline'],
				':su' => $_POST['summary']
			));
			
			
			//Clear out the old position entries
			$stmt = $pdo->prepare('DELETE FROM Position
				WHERE profile_id=:profile_id');
			$stmt->execute(array( ':profile_id' => $_REQUEST['profile_id']));
			
			//Insert the position entries
			$rank = 1;
			for ($i=1; $i<=9; $i++){
				if ( ! isset($_POST['year'.$i])) continue;
				if ( ! isset($_POST['desc'.$i])) continue;
				$year = $_POST['year'.$i];
				$desc = $_POST['desc'.$i];
				
				$stmt = $pdo->prepare('INSERT INTO Position
					(profile_id, rank, year, description)
					VALUES (:profile_id, :rank, :year, :desc)');
				$stmt->execute(array(
				  ':profile_id' => $_REQUEST['profile_id'],
				  ':rank' => $rank,
				  ':year' => $year,
				  ':desc' => $desc)
				);
				$rank++;
			}			
			
			$_SESSION['success'] = "Profile edited";
			header("Location: index.php");
			return;
		}
	}
}

//Load up the position rows
$positions = loadPos($pdo, $_REQUEST['profile_id']);
?>
<html>
<head>
<title>Erwin Swinnen</title>
<?php require_once "bootstrap.php"; ?>
</head>
<body>
<div class="container">
<h1>Edit Profile in Database</h1>
<body>
<h2>Enter new Profile data here:</h2>
<?php
    if ( isset($_SESSION["error"]) ) {
        echo('<p style="color:red">'.htmlentities($_SESSION["error"])."</p>\n");
        unset($_SESSION["error"]);
    }
?>
<form method="POST" action="edit.php">
<p>First Name:
<input type="text" name="first_name" size="60" value="<?= htmlentities($fn) ?>"></p>
<p>Last Name:
<input type="text" name="last_name" size="60" value="<?= htmlentities($ln) ?>"></p>
<p>Email:
<input type="text" name="email" size="30" value="<?= htmlentities($em) ?>"></p>
<p>Headline:<br/>
<input type="text" name="headline" size="80" value="<?= htmlentities($he) ?>"></p>
<p>Summary:
<textarea name="summary" rows="8" cols="80" wrap="hard" value="<?= htmlentities($su)?>"><?php echo $su;?></textarea></p>
<input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?>">
<input type="hidden" name="profile_id" value="<?= $_GET['profile_id']; ?>">

<?php

$pos = 0;
echo('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
echo('<div id="position_fields">'."\n");
foreach($positions as $position){
	$pos++;
	echo('<div id="position'.$pos.'">'."\n");
	echo('<p>Year: <input type="text" name="year'.$pos.'"');
	echo(' value="'.$position['year'].'" />'."\n");
	echo('<input type="button" value="-" ');
	echo('onclick="$(\'#position'.$pos.'\').remove(); return false;">'."\n");
	echo("</p>\n");
	echo('<textarea name="desc'.$pos.'" rows="8" cols="80">'."\n");
	echo(htmlentities($position['description'])."\n");
	echo("\n</textarea>\n</div>\n");
}
echo("</div></p>\n");
?>

<input type="submit" name="save" value="Save"/>
<input type="submit" name="cancel" value="Cancel"/>
</form>

<script>
countPos = <?= $pos ?>;

$(document).ready(function(){
	window.console && console.log('Document ready called');
	$('#addPos').click(function(event){
		event.preventDefault();
		if ( countPos >= 9) {
			alert("Maximum of nine position entries exceeded");
			return;
		}
		countPos++;
		window.console && console.log("Adding position "+countPos);
		$('#position_fields').append(
			'<div id="position'+countPos+'"> \
			<p>Year: <input type="text" name="year'+countPos+'" value="" /> \
			<input type="button" value="-" \
				onclick="$(\'#position'+countPos+'\').remove(); return false;"></p> \
			<textarea name="desc'+countPos+'" rows="8" cols="80"></textarea> \
			</div>');
	});
});
</script>
</div>
</body>