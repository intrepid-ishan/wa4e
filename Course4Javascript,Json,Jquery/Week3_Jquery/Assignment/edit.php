<!-- note:profile id recieved in $_GET['profile_id'] -->
<!-- --------backend stuff-------- -->
<?php
session_start();


require_once "pdo.php";
require_once 'util.php';
//***testcases**** 
if (!isset($_SESSION['name'])) {
    die('Not logged in');
}
if (isset($_POST['cancel'])) {
    header('Location: index.php');
    return;
}

//when press submit all fields will be set if no value entered then also set
//instead of this 6 lines code you can use if($_SERVER["REQUEST_METHOD"]=="POST")




//--------updation in table where profile id matches--------
if (
    isset($_POST['first_name'])
    && isset($_POST['first_name'])
    && isset($_POST['last_name'])
    && isset($_POST['email'])
    && isset($_POST['headline'])
    && isset($_POST['summary'])
) {


    //use utiility to validate profile
    $msg = validateProfile();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location:edit.php?profile_id=" . $_GET['profile_id']);
        return;
    }

    //use utility to validate position
    $msg = validatePos();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location:edit.php?profile_id=" . $_GET['profile_id']);
        return;
    }

    //Update Profile
    $sql = "UPDATE Profile SET 
            first_name = :first_name, 
            last_name = :last_name,
            email=:email,
            headline=:headline,
            summary=:summary
            WHERE profile_id = :profile_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(
        array(
            ':first_name' => $_POST['first_name'],
            ':last_name' => $_POST['last_name'],
            ':email' => $_POST['email'],
            ':headline' => $_POST['headline'],
            ':summary' => $_POST['summary'],
            ':profile_id' => $_GET['profile_id'] //--------update in row with similar profile id
        )
    );


    // ---------------Here we are deleting and inserting instead of updating-----------------------

    $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
    $stmt->execute(array(':pid' => $_GET['profile_id']));

    insertInPosition($pdo,$_GET['profile_id']);


    //success redirection
    $_SESSION['success'] = 'Record updated';
    header('Location: index.php');
    return;
}




//***testcase***Make sure that user_id is present
if (!isset($_GET['profile_id'])) {
    $_SESSION['error'] = "Missing profile_id";
    header('Location: index.php');
    return;
}




//---------select a row with where profile id matches for displaying PROFILE--------
$stmt = $pdo->prepare("SELECT * FROM Profile where profile_id = :xyz");
$stmt->execute(array(":xyz" => $_GET['profile_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);

//-----------------load POSITION for displaying-------------------
$positions = loadPos($pdo, $_GET['profile_id']);



//***testcase*** if database changed by mistake 
if ($row === false) {
    $_SESSION['error'] = 'Bad value for user_id';
    header('Location: index.php');
    return;
}
?>

<!-- --------Fall into View-------- -->

<!DOCTYPE html>
<html>

<head>
    <?php require_once "bootstrap.php"; ?>
    <title>Edit Profile cf6c7f1e</title>
</head>

<body>
    <div class="container">
        <h1>Editing Profile for ITNU,Nirma</h1>
        <?php
        flashMessages();
        ?>

        <!-- --------simple form-------- with value field set -->
        <div class="form-group">
            <form method="post">
                <input type="text" class="form-control col-sm-4" placeholder="First Name" name="first_name" value="<?php echo $row['first_name'] ?>"><br>
                <input type="text" class="form-control col-sm-4" placeholder="Last Name" name="last_name" value="<?php echo $row['last_name'] ?>"><br>
                <input type="text" class="form-control col-sm-4" placeholder="Email" name="email" value="<?php echo $row['email'] ?>"><br>
                <input type="text" class="form-control col-sm-4" placeholder="Headline" name="headline" value="<?php echo $row['headline'] ?>"><br>
                <textarea class="form-control col-sm-8" placeholder="Summary" id="exampleTextarea" rows="5" name="summary"><?php echo $row['summary'] ?></textarea><br>

                <!-- look over to complete line and its easy -->
                <?php
                $pos = 0;
                echo ('<p>Position: <input type="submit" id="addPos" value="+">' . "\n");
                echo ('<div id="position_fields">' . "\n");
                foreach ($positions as $position) {
                    $pos++;
                    // echo "hi";
                    echo ('<div id="position' . $pos . '">' . "\n");
                    echo ('<p>Year:<input type="text" name="year' . $pos . '"');
                    echo ('value ="' . $position['year'] . '"/>' . "\n");
                    echo ('<input type="button" value="-" ');
                    echo ('onclick="$(\'#position' . $pos . '\').remove(); return false;">' . "\n");
                    echo ("</p>\n");
                    echo ('<textarea name="desc' . $pos . '" rows="8" cols="80">' . "\n");
                    echo (htmlentities($position['description']) . "\n");
                    echo ("\n</textarea>\n</div>\n");
                }
                echo ("</div></p>\n");
                ?>

                <button type="submit" class="btn btn-primary">Save</button>
                <button type="submit" class="btn btn-danger" name="cancel">Cancel</button>
            </form>
        </div>

        <!-- add jquery for dynamacity -->
        <script>
            //after displaying where pos stops from there starts
            countPos = <?= $pos ?>;

            // http://stackoverflow.com/questions/17650776/add-remove-html-inside-div-using-javascript
            // adding event listener on id element ---->$().click
            $(document).ready(function() {
                window.console && console.log('Document ready called');

                // adding event listener on id element ---->$().click
                $('#addPos').click(function(event) {

                    //----very imp---- this line will prevent the default action of submit button which we have used
                    //i.e it will not be submitted as post because form is POST
                    //and it will work as we guide below
                    event.preventDefault();

                    if (countPos >= 9) {
                        alert("Maximum of nine position entries exceeded");
                        return;
                    }
                    countPos++;
                    window.console && console.log("Adding position" + countPos);

                    //------> $().append()
                    $('#position_fields').append(
                        '<div id="position' + countPos + '"> \
                <p>Year: <input type="text" name="year' + countPos + '" value="" /> \
                <input type="button" value="-"  \
                    onclick="$(\'#position' + countPos + '\').remove(); return false;"></p>  \
                <textarea name="desc' + countPos + '" rows="8" placeholder="Description of above year" cols="80"></textarea>\
                </div>');
                });

            });
        </script>
    </div>
</body>

</html>