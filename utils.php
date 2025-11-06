<?php
function simulate_email($to, $subject, $message)
{
    $log = "To: $to\nSubject: $subject\nMessage: $message\n\n";
    file_put_contents('email_log.txt', $log, FILE_APPEND | LOCK_EX);
}
?>