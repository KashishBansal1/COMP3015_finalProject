<?php
require 'includes/functions.php';

if (count($_POST) > 0) {
    if ($_GET['from'] == 'login') {
        $found = false; // assume not found

        $email = trim($_POST['email']);
        $pass = trim($_POST['password']);

        if (preg_match("/^([a-z0-9_\.-]+)@([\da-z\.-]+)\.([a-z\.]{2,6})$/", $email)) {
            $found = findUser($email, $pass);

            if ($found) {
                session_start();
                $_SESSION['loggedin'] = true;
                $_SESSION['email'] = $email;
                // $_SESSION['admin'] = true;
                // saveUserInFile($_POST);
                header('Location: index.php?from=login');
                exit();
            }
        }

        setcookie('error_message', 'Login not found! Try again.');
        header('Location: index.php');
        exit();
    } elseif ($_GET['from'] == 'signup') {
        if (checkSignUp($_POST) && saveUser($_POST)) {
            session_start();
            $_SESSION['signedin'] = true;
            $_SESSION['email'] = trim($_POST['email']);
            header('Location: index.php?from=signup');
            exit();
        }

        setcookie('error_message', 'Unable to sign up at this time.');
        header('Location: index.php');
        exit();
    } elseif ($_GET['from'] == 'newItem') {
        session_start();
        if (count($_FILES) > 0) {
            $check = checkPost($_FILES);
            if ($check !== true) {
                setcookie('error_message', $check);
                header('Location: index.php');
                exit();
            } else {
                if (checkProduct($_POST) && saveProduct($_POST, $_SESSION['email'])) {
                    header('Location: index.php?from=newItem');
                    exit();
                } else {
                    setcookie('error_message', 'Not able to upload new item!');
                    header('Location: index.php');
                    exit();
                }
            }
        }
    }
}
header('Location: index.php');
exit();
