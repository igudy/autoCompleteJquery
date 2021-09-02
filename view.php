<!--================Connecting to the database=================-->
<?php
 session_start();
 include("db.php");
?>

<?php

if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing autos_id";
    header('Location: index.php');
    return;
}

$stmt = $pdo->prepare("SELECT * FROM Profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM Position where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$rowsofpos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("SELECT * FROM Education join Institution on Education.institution_id = Institution.institution_id where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$rowsofedu = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
 include("head.php");
?>

    <h1>Profile information</h1>
    <p>First Name: <?php echo($row['first_name']); ?></p>
    <p>Last Name: <?php echo($row['last_name']); ?></p>
    <p>Email: <?php echo($row['email']); ?></p>
    <p>Headline:<br/> <?php echo($row['headline']); ?></p>
    <p>Summary: <br/><?php echo($row['summary']); ?></p>
    <p>Education: <br/><ul>
        
        <?php
        foreach ($rowsofedu as $row) {
            echo('<li>'.$row['year'].':'.$row['name'].'</li>');
        } ?>
        
    </ul></p>
    <p>Position: <br/><ul>
        <?php
        foreach ($rowsofpos as $row) {
            echo('<li>'.$row['year'].':'.$row['description'].'</li>');
        } ?>
        </ul></p>
    <a href="index.php">Done</a>
</div>
</body>
</html>
