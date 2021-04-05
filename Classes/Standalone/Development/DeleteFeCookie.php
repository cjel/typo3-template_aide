<?php
if (isset($_SERVER['HTTP_COOKIE'])) {
    $cookies = explode(';', $_SERVER['HTTP_COOKIE']);
    foreach($cookies as $cookie) {
        $parts = explode('=', $cookie);
        $name = trim($parts[0]);
        if (strpos($name, 'fe_typo_user') === 0) {
            setcookie($name, '', time()  - 3600);
            setcookie($name, '', time()  - 3600, '/');
            setcookie($name, '', time()  - 3600, '/', '.'.$_SERVER['HTTP_HOST']);
        }
    }
}
