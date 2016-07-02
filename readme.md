## Central do

======

A simple php router designed for super tiny projects.

PATH_INFO Only.

### Examples

```
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

$do->run();
```

Or:

```
use Mis\Cdo;

Cdo::get('/', function () {
    echo 'hello world';
});

Cdo::run();
```