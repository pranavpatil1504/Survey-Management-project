<?php
function validatePassword($password)
{
    return preg_match('/^(?=.*[A-Z])(?=.*\d)(?=.*[^\da-zA-Z]).{8,}$/', $password);
}

?>