<?php

include("include/session.php");

class Process {
    /* Class constructor */

    function Process() {
        global $session;
        /* User submitted login form */
        if (isset($_POST['sublogin'])) {
            $this->procLogin();
        }
        /* User submitted registration form */ else if (isset($_POST['subjoin'])) {
            $this->procRegister();
        }
        
        /* User submitted forgot password form */ else if (isset($_POST['subforgot'])) {
            $this->procForgotPass();
        }
        /* User submitted edit account form */ else if (isset($_POST['subedit'])) {
            $this->procEditAccount();
        }
        else if (isset($_POST['csv'])){
            $this->parseCSV();
        }
        else if (isset($_POST['subupdislaikyta'])){
            $this ->changeValue();
        }
        else if(isset($_POST['subuppakeista'])){
            $this ->changeSkola();
        }
        else if (isset($_POST['SendMail'])){
            $this ->sendMail();
        }
        /**
         * The only other reason user should be directed here
         * is if he wants to logout, which means user is
         * logged in currently.
         */ else if ($session->logged_in) {
            $this->procLogout();
        }
        /**
         * Should not get here, which means user is viewing this page
         * by mistake and therefore is redirected.
         */ else {
            header("Location: index.php");
        }
    }
    function sendMail(){
        global $database,$mailer,$session ;
        $subuser = $_REQUEST['SendMail'];
        $q = "SELECT vartotojo_vardas, Destytojo_id "
            . "FROM " . TBL_SKOLOS . " where id = \" " . $subuser . '"';
        $result = $database->query($q);
        echo $q;
       while( $r = mysqli_fetch_assoc($result)) {
           $vardas = $r['Destytojo_id'];
           $vartotojas = $r['vartotojo_vardas'];
       }

        $q = "SELECT email "
            . "FROM " . TBL_USERS . " where username = \"" . $vartotojas . '"';
        $result = $database->query($q);
         while( $r = mysqli_fetch_assoc($result)) {
             $r = mysqli_fetch_assoc($result);
             $emailas = $r['email'];
         }

        $mailer->sendinvoice($subuser,$emailas,$vardas);
        header("Location: " . $session->referrer);
    }
    function changeSkola(){
        global $database,$session;
        $subuser = $_REQUEST['upduser'];
        /* Update user level */
        $fieldai= array('Ampoketa','Nuo','Iki','verte','kiekis');
        $arra= array($_REQUEST['updApmoketqa'],$_REQUEST['Nuoo'],$_REQUEST['Ikis'],$_REQUEST['Vertee'],$_REQUEST['Kiekis']);
       echo $arra[4];
        $database->updateUserExamValues($subuser, $fieldai, $arra); // pasiimam id
        header("Location: " . $session->referrer);
    }

    function changeValue(){
        global $session, $database;
        /* Username error checking */
        $subuser = $_REQUEST['upduser'];
        /* Update user level */
            $database->updateUserExam($subuser, "Islaikyta", (int) $_POST['updlevel']);
            header("Location: " . $session->referrer);

    }
    function parseCSV(){
        global $database;
        global $session;
$csv = array();

// check there are no errors
if($_FILES['cssv']['error'] == 0){
    $name = $_FILES['cssv']['name'];
//$ext = strtolower(end(explode('.', $_FILES['csv']['name'])));
    $type = $_FILES['cssv']['type'];
    $tmpName = $_FILES['cssv']['tmp_name'];

// check the file is a csv
    if(0===0){
        if(($handle = fopen($tmpName, 'r')) !== FALSE) {
// necessary if a large csv file
            set_time_limit(0);

            $row = 0;

            while(($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
// number of fields in the csv
                $col_count = count($data);

// get the values from the csv
                $csv[$row][0] = $data[0];

// inc the row
        $database ->AddSkola($data[0],$session ->username);
                $row++;
            }
            fclose($handle);
        }
    }
    header("Location: " . $session->referrer);
}

    }

    /**
     * procLogin - Processes the user submitted login form, if errors
     * are found, the user is redirected to correct the information,
     * if not, the user is effectively logged in to the system.
     */
    function procLogin() {
        global $session, $form;
        /* Login attempt */
        $retval = $session->login($_POST['user'], $_POST['pass'], isset($_POST['remember']));

        /* Login successful */
        if ($retval) {
            $session->logged_in = 1;
            header("Location: " . $session->referrer);
        }
        /* Login failed */ else {
            $session->logged_in = null;
            $_SESSION['value_array'] = $_POST;
            $_SESSION['error_array'] = $form->getErrorArray();
            header("Location: " . $session->referrer);
        }
    }

    /**
     * procLogout - Simply attempts to log the user out of the system
     * given that there is no logout form to process.
     */
    function procLogout() {
        global $session;
        $retval = $session->logout();
        header("Location: index.php");
    }
 
    /**
     * procRegister - Processes the user submitted registration form,
     * if errors are found, the user is redirected to correct the
     * information, if not, the user is effectively registered with
     * the system and an email is (optionally) sent to the newly
     * created user.
     */
    function procRegister() {
        global $session, $form;
        /* Convert username to all lowercase (by option) */
        if (ALL_LOWERCASE) {
            $_POST['user'] = strtolower($_POST['user']);
        }
        /* Registration attempt */
        $retval = $session->register($_POST['user'], $_POST['pass'], $_POST['email']);

        /* Registration Successful */
        if ($retval == 0) {
            $_SESSION['reguname'] = $_POST['user'];
            $_SESSION['regsuccess'] = true;
            header("Location: " . $session->referrer);
        }
        /* Error found with form */ else if ($retval == 1) {
            $_SESSION['value_array'] = $_POST;
            $_SESSION['error_array'] = $form->getErrorArray();
            header("Location: " . $session->referrer);
        }
        /* Registration attempt failed */ else if ($retval == 2) {
            $_SESSION['reguname'] = $_POST['user'];
            $_SESSION['regsuccess'] = false;
            header("Location: " . $session->referrer);
        }
    }

    /**
     * procForgotPass - Validates the given username then if
     * everything is fine, a new password is generated and
     * emailed to the address the user gave on sign up.
     */
    function procForgotPass() {
        global $database, $session, $mailer, $form;
        /* Username error checking */
        $subuser = $_POST['user'];
        $field = "user";  //Use field name for username
        if (!$subuser || strlen($subuser = trim($subuser)) == 0) {
            $form->setError($field, "* Neįvestas vartotojo vardas<br>");
        } else {
            /* Make sure username is in database */
            $subuser = stripslashes($subuser);
            if (strlen($subuser) < 5 || strlen($subuser) > 30 ||
                    !preg_match("/^([0-9a-z])+$/", $subuser) ||
                    (!$database->usernameTaken($subuser))) {
                $form->setError($field, "* Vartotojas neegzistuoja<br>");
            }
        }

        /* Errors exist, have user correct them */
        if ($form->num_errors > 0) {
            $_SESSION['value_array'] = $_POST;
            $_SESSION['error_array'] = $form->getErrorArray();
        }
        /* Generate new password and email it to user */ else {
            /* Generate new password */
            $newpass = $session->generateRandStr(8);

            /* Get email of user */
            $usrinf = $database->getUserInfo($subuser);
            $email = $usrinf['email'];

            /* Attempt to send the email with new password */
            if ($mailer->sendNewPass($subuser, $email, $newpass)) {
                /* Email sent, update database */
                $database->updateUserField($subuser, "password", md5($newpass));
                $_SESSION['forgotpass'] = true;
            }
            /* Email failure, do not change password */ else {
                $_SESSION['forgotpass'] = false;
            }
        }

        header("Location: " . $session->referrer);
    }

    /**
     * procEditAccount - Attempts to edit the user's account
     * information, including the password, which must be verified
     * before a change is made.
     */
    function procEditAccount() {
        global $session, $form;
        /* Account edit attempt */
        $retval = $session->editAccount($_POST['curpass'], $_POST['newpass'], $_POST['email']);

        /* Account edit successful */
        if ($retval) {
            $_SESSION['useredit'] = true;
            header("Location: " . $session->referrer);
        }
        /* Error found with form */ else {
            $_SESSION['value_array'] = $_POST;
            $_SESSION['error_array'] = $form->getErrorArray();
            header("Location: " . $session->referrer);
        }
    }

}

/* Initialize process */
$process = new Process;
?>
