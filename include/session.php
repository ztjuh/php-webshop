<?php

//Check if Session directory exists and if not create it
//if (!file_exists(realpath(dirname($_SERVER['DOCUMENT_ROOT'])) . $session_dir)) {
//    if (!mkdir(realpath(dirname($_SERVER['DOCUMENT_ROOT'])) . $session_dir, 0700)) {
//        echo "Could not create " . realpath(dirname($_SERVER['DOCUMENT_ROOT'])) . $session_dir . ", please create the directory yourself with read permissions for the webserver!";
//        exit();
//    }
//}

//Set the Session directory
//ini_set('session.save_path',realpath(dirname($_SERVER['DOCUMENT_ROOT']) . $session_dir));

//Start the Session
session_start();

//Check if timestamp exist if it doesn't set it with a value of 24 hours
if (!isset($_SESSION['timestamp']) || $_SESSION['timestamp'] + (60 * 60 * 24) > time()) {
    $_SESSION['timestamp'] = time();
} 
else {
    session_destroy();
    if (isset($_SERVER['HTTP_REFERER'])) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit();
    }
    else {
        header("Location: index.php");
        exit();
    }
}
