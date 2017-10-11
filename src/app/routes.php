<?php

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use App\Models\Reviews;

$app->get('/', function () use ($app) {

    $reviews = new Reviews($app);
    $notes = $reviews->mainPage();

    return $app['twig']->render('index.twig', [
        'notes' => $notes, 'sortvote' => 'DESC', 'sortdate' => 'DESC',
    ]);

});

$app->post('/', function () use ($app) {

    $orderby = '';
    if (!empty($_POST['sortvote'])) {
        $orderby = ' ORDER BY likes ' . $_POST['sortvote'];
        $sortvote = ($_POST['sortvote'] == 'ASC') ? 'DESC' : 'ASC';
        $sortdate = 'ASC';
    } elseif (!empty($_POST['sortdate'])) {
        $orderby = ' ORDER BY date ' . $_POST['sortdate'];
        $sortdate = ($_POST['sortdate'] == 'ASC') ? 'DESC' : 'ASC';
        $sortvote = 'ASC';
    }

    $reviews = new Reviews($app);
    $notes = $reviews->postMainPage($orderby);

    return $app['twig']->render('index.twig', [
        'notes' => $notes, 'sortdate' => $sortdate, 'sortvote' => $sortvote,
    ]);

});

$app->get('/vote/{id}', function ($id) use ($app) {

    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }

    if ($app['session']->get('user')[$id] != $ip) {

        $reviews = new Reviews($app);
        $reviews->votePage($id);

        $app['session']->set('user', [$id => $ip]);
    }

    return $app->redirect('/');
});

$app->get('/add', function () use ($app) {
    if (empty($data)) {
        $data = [
            'name' => '',
            'note' => '',
        ];
        $title = '';
    }
    return $app['twig']->render('form.twig', [
        'data' => $data, 'title' => $title,
    ]);
});

$app->post('/add', function () use ($app) {

    $app->register(new Silex\Provider\ValidatorServiceProvider());

    $constraint = new Collection([
        'name' => [new Length(['min' => 5]),
            new NotBlank()
        ],
        'note' => [new Length(['min' => 5]),
            new NotBlank()
        ]
    ]);

    $data = [
        'name' => $_POST['name'],
        'note' => strip_tags($_POST['text']),
    ];

    $errors = $app['validator']->validate($data, $constraint);

    if (count($errors) === 0) {

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        $reviews = new Reviews($app);
        $reviews->postAddPage($ip, $data['name'], $data['note']);

        return $app['twig']->render('form-success.twig');

    } else {

        $title = 'Данные были заполнены не верно!';

        return $app['twig']->render('form.twig', [
            'data' => $data, 'title' => $title
        ]);

    }

});

$app->get('/note/{id}', function ($id) use ($app) {


    if ($id == 1) {
        $prev = 1;
    } else {
        $prev = $id - 1;
    }
    if ($id < count($note)) {
        $next = $id + 1;
    } else {
        $next = $id;
    }

    $reviews = new Reviews($app);
    $note = $reviews->notePage($id);

    return $app['twig']->render('note.twig', [
        'note' => $note, 'prev' => $prev, 'next' => $next
    ]);

});
