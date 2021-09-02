<!--================Connecting to the database=================-->
<?php
 session_start();
 include("db.php");
?>

<?php
if (!isset($_SESSION['name'])) {
    die('User Not Logged In');
}

// my ngrok code http://6048bbc8d12e.ngrok.io/coursera


if ( isset($_POST['Delete']) && isset($_POST['profile_id']))
    {
    $sql = "DELETE FROM Profile WHERE profile_id = :zip";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':zip' => $_POST['profile_id']));
    $_SESSION['success'] = 'Record deleted';
    header( 'Location: index.php' ) ;
    return;
}


if ( ! isset($_GET['profile_id']) ) {
    $_SESSION['error'] = "Missing user_id";
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare("SELECT first_name, last_name FROM Profile where profile_id = :xyz");

$stmt->execute(array(":xyz" => $_GET['profile_id']));

$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
    $_SESSION['error'] = 'Value for profile_id not valid';
    header('Location: index.php');
    return;
}
?>


<?php
 include("head.php");
?>


    <p>First Name: <?php echo($row['first_name']); ?></p>
    <p>Last Name: <?php echo($row['last_name']); ?></p>
    
    <form method="post"><input type="hidden" name="profile_id" value="<?php echo $_GET['profile_id'] ?>">
        <input type="submit" name="Delete" value="Delete">
        <input type="submit" name="cancel" value="cancel">
    </form>
</div>
</body>