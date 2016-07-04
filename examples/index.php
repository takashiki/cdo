<?php

require __DIR__.'/../src/Cdo.php';

$do = new \Mis\Cdo();

$do->get('/', function () {
    echo 'hello world';
});

$do->post('/', function () {
    $name = isset($_POST['name']) ? $_POST['name'] : 'world';
    echo "hello {$name}";
});

$do->any('/(\d+)', function ($id) {
    echo $id;
});

/**
 * When using named subpattern, order of parameters is not matter.
 * eg. /book/2
 */
$do->any('/(?P<type>\w+)/(?P<page>\d+)', function ($page, $type) {
    echo $type.'<br>'.$page;
});

$do->get('/site/index', array((new SiteController()), 'actionIndex'));

$do->get('/user/detail', array(User::class, 'detail'));

$do->setNotFound(function () {
    echo 'Page not found.';
});

$do->run();

class SiteController
{
    public function actionIndex()
    {
        echo 'homepage';
    }
}

class User
{
    public static function detail()
    {
        echo 'user detail';
    }
}

/*
// Using static call.
use Mis\Cdo;

Cdo::get('/', function () {
    echo 'hello world';
});

Cdo::run();
*/
