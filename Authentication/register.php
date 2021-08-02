
<?php

    include '../vendor/autoload.php';

    function isArrEmpty($arr){
        foreach((array)$arr as $key => $value){
            if (!empty($arr[$key])) {
                
                return false;

            }
        }
        return true;
    }

$errors = array(
    'firstName' => "", 
    'lastName' => "",
    'email' => "",
    'contact' => "",
    'password-1' => "",
    'password-2' => "",
);



$fields = array(
    'firstName'=>"",
    'lastName' => "", 
    'email' => "", 
    'contact' => "", 
    'password-1' => "", 
    'password-2' => ""
);

if (isset($_POST['submit'])) {
    //getting data form input fields
    {
        $firstName = htmlspecialchars($_POST['first-name']);
        $lastName = htmlspecialchars($_POST['last-name']);
        $email = htmlspecialchars($_POST['email']);
        $contact = htmlspecialchars($_POST['contact']);
        $password_1 = htmlspecialchars($_POST['password-1']);
        $password_2 = htmlspecialchars($_POST['password-2']);
    }

    
    //storing data in fields array
    {
        $fields['firstName'] = $firstName;
        $fields['lastName'] = $lastName; 
        $fields['email'] = $email;
        $fields['contact'] = $contact;
        $fields['password-1'] = $password_1; 
        $fields['password-2'] = $password_2;
    }


    //checking for empty fields
    foreach ($fields as $field => $value) {
        if (empty($value)) {
            if ($field === "password-1" or $field === "password-2") {
                $errors[$field] = "password can't be empty";

            }else {
                $errors[$field] = "$field can't be empty";
            }
        }
    }
    
    


    //further validation
    foreach ($errors as $field => $error) {


        //email validation
        if ($field === "email" and empty($error)) {

            //invalid email address
            if (!filter_var($fields[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = "invalid email address"; 

            } else {
                
                //email already registered (sign in instead) ?
                $conn = mysqli_connect('localhost', 'root', '', 'hospital_management_system');
                $snapshot = $conn->query("SELECT email FROM patients WHERE email = '$email'");
                
                if ($snapshot != false) {
                    if ($snapshot->num_rows > 0) {
                        $errors[$field] = "email already exist, <a href='./login.php' id='sign-in-link'>Sign In</a> instead";
                        // echo "email already exist";
                    }
                }else {
                    echo "error querying email";
                }
            }
        }
        

        // password validation 
        if (($field === 'password-1' or $field === 'password-2') and $fields['password-1'] !== $fields['password-2']) {
            $errors[$field] = "password mismatch";
        }

        //other field verification
    }

    //on successful registry
    if(isArrEmpty($errors)){

        //generate patient id

                // 1 => incrementing id
            // {
            //     $conn = mysqli_connect('localhost', 'root', '', 'hospital_management_system');
            //     $snapshot = $conn->query('SELECT COUNT(*) FROM patients');
            //     $result = $snapshot->fetch_all(MYSQLI_ASSOC);

            //     $patient_id_num = ($result[0]['COUNT(*)'] + 1);

            //     switch (strlen((string) $patient_id_num)) {
            //         case 1:
            //             $patient_id = "PAT-000".$patient_id_num."A";
            //             break;
            //         case 2:
            //             $patient_id = "PAT-00".$patient_id_num."A";
            //             break;
            //         case 3:
            //             $patient_id = "PAT-0".$patient_id_num."A";
            //             break;
            //         case 4:
            //             $patient_id = "PAT-".$patient_id_num."A";
            //             break;
            //         default:
            //             $patient_id = null;
            //     }
            // }
            

             //2 => random id
             {
                $conn = mysqli_connect('localhost', 'root', '', 'hospital_management_system');

                do {
                    $patient_id_num = mt_rand(1,9999);

                    switch (strlen((string) $patient_id_num)) {
                        case 1:
                            $patient_id = "PAT-000".$patient_id_num."A";
                            break;
                        case 2:
                            $patient_id = "PAT-00".$patient_id_num."A";
                            break;
                        case 3:
                            $patient_id = "PAT-0".$patient_id_num."A";
                            break;
                        case 4:
                            $patient_id = "PAT-".$patient_id_num."A";
                            break;
                        default:
                            $patient_id = null;
                    }
                
                    $snapshot = $conn->query("SELECT * FROM patients WHERE patient_id = '$patient_id'");
                } while ($snapshot->num_rows > 0);

            }

            //connection to db
            {
                $conn = mysqli_connect("localhost", "root", "", "hospital_management_system");
                if ($conn) {
                    $statement = $conn ->prepare("INSERT INTO patients (first_name, last_name, email, contact, pswd, patient_id) VALUES (?, ?, ?, ?, ?, ?)");
                   if($statement != false){
                    $statement->bind_param('ssssss', $firstName, $lastName, $email, $contact, $password_2, $patient_id);
                    if (!$statement->execute()) {
                        echo "error updating info";
                    };
                   }
                   else {
                    echo "statement error";
                   }
                }
            }


        //send verification mail
        {
            $transport = (new Swift_SmtpTransport('smtp.gmail.com',587,'tls'))
            ->setUsername("ta373098@gmail.com")
            ->setPassword("test@gmail")
            ;



            $body = 
            "
            Hi $lastName, <br>
            <!-- Welcome to -->
            Your Patient ID is <strong>$patient_id</strong> <br>
            <a href='localhost/G10_MEDICALS/authentication/login.php'>Go To Sign In Page</a>
            ";

            $message = (new Swift_Message("Account Verification", $body, "text/html"))
                ->setFrom("ta373098@gmail.com")
                ->setTo($email)
            ;

            $mailer = (new Swift_Mailer($transport))->send($message);

            if ($mailer > 0) {
                echo "succesfully sent";
            }else {
                echo "mail not sent";
            }

        }
    }
    

}

?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>

    <style>
        #form {
            display: flex;
            flex-direction: column;
        }

        #form label {
            margin-bottom: 20px;
        }

        a {
            color: blue;
            text-decoration: none;
        }

        .error {
            color: red;
        }
    </style>
</head>

<body>
    <form action="register.php" method="post" id="form">
        <label>
            First Name:
            <input type="text" name="first-name" id="first-name" value="<?php echo $fields['firstName'] ?>"> <br>
           <div class="error"><?php echo $errors['firstName'] ?></div>
        </label>
        <label>
            Last Name:
            <input type="text" name="last-name" id="last-name" value="<?php echo $fields['lastName'] ?>"> <br>
            <div class="error"><?php echo $errors['lastName'] ?></div>
        </label>
        <label>
            Email:
            <input type="text" name="email" id="email" value="<?php echo $fields['email'] ?>"><br>
            <div class="error"><?php echo $errors['email'] ?></div>
        </label>
        <label>
            Contact:
            <input type="tel" name="contact" id="contact" value="<?php echo $fields['contact'] ?>"><br>
            <div class="error"><?php echo $errors['contact'] ?></div>
        </label>
        <label>
            Password:
            <input type="password" name="password-1" id="password-1" ><br>
            <div class="error"><?php echo $errors['password-1'] ?></div>
        </label>
        <label>
            Confirm Password:
            <input type="password" name="password-2" id="password-2" ><br>
            <div class="error"><?php echo $errors['password-2'] ?></div>
        </label>

        <input type="submit" name="submit" value="Register">
    </form>

    <p>already have an account ? <a href="./login.php" id="sign-in-link">Sign In</a> </p>
</body>

</html>