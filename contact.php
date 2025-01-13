<?php
if ($_SERVER["REQUEST_METHOD"]=="POST"){
    $name = $_POST["yourName"];
    $name = $_POST["yourEmail"];
    $name = $_POST["yourPhone"];
    $name = $_POST["yourMessage"];
    $to = "slimmworldtechnologies21@gmail.com";
    $headers = "Form $email";
    if(mail($to, $subject, $message, $headers)){
        echo "Email sent";

    }
    else{
        echo "Email not sent";
    }
}
?>