<?php

try {
    $db = new PDO('dsn', 'user', 'pass');
} catch (PDOException $e) {
    die('Error connect: ' . $e->getMessage());
}
$routes = [ 'login' => 'auth', 'reg' => 'reg', 'foo' => 'bar'];

if (!isset($routes[$_REQUEST['page']])) {
    echo render_error('Page not found');
} else {
    $do = $routes[$_REQUEST['page']];
    echo $do();
}

session_start();

function auth()
{
    if(isset($_SESSION['user'])) {
        return userPage($_SESSION['user']);
    }
    $name = filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING);
    $pass = filter_input(INPUT_POST, 'pass', FILTER_DEFAULT);
    $sql = $db->prepare('SELECT * FROM users WHERE name=? LIMIT 1');
    $sql -> execute($name);
    if (!($user = $sql->fetch())) {
        return render_error('User not found');
    }
    if (!checkPass($pass, $user['pass'])) {
        return render_error('Bad auth data');
    } else {
        $_SESSION['user'] = $user;
        $q = $db->prepare('UPDATE user SET last_visit = ?');
        $q -> execute(time());
        $q = $db->prepare('INSERT INTO user_log user_id=?, ip=?');
        $q->execute($user['id'], $_SERVER['REMOTE_ADDR']);
        }
        return userPage($user);
}

?>
