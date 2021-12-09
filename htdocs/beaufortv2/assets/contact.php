<?php
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Get the form fields and remove whitespace.
        $name = strip_tags(trim($_POST["name"]));
				$name = str_replace(array("\r","\n"),array(" "," "),$name);
        $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
        $subject = trim($_POST["subject"]);
        $number = trim($_POST["number"]);
        $message = trim($_POST["message"]);

        if ( empty($name) OR empty($subject) OR empty($number) OR empty($message) OR !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo "Please complete the form and try again.";
            exit;
        }

        $recipient = "testreply85@gmail.com";
        $email_content = "Naam: $name\n";
        $email_content .= "Email: $email\n\n";
        $email_content .= "Onderwerp: $subject\n\n";
        $email_content .= "Telefoonnummer: $number\n\n";
        $email_content .= "Bericht:\n$message\n";
        $email_headers = "From: $name <$email>";

        if (mail($recipient, $subject, $email_content, $email_headers)) {
            http_response_code(200);
            echo "De email is verstuurd!";
        } else {
            http_response_code(500);
            echo "Oeps, er ging iets mis.";
        }
    } else {
        http_response_code(403);
			echo "Oeps, er ging iets mis.";
    }
?>

