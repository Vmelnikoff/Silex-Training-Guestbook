<?php

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

$app->get('/', function () use ($app) {

    $notes = $app['db']->fetchAll('SELECT * FROM reviews');

    $cnt = count($notes);
    for ($i = 0; $i < $cnt; $i++) {
        $notes[$i]['note'] = substr($notes[$i]['note'], 0, 256) . '...';
    }

    return $app['twig']->render('index.twig', [
        'notes' => $notes,
    ]);

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

        $sql = "INSERT INTO reviews (name, note, date, ip) VALUES (?, ?, CURDATE(), INET_ATON('$ip'))";
        $stmt = $app['db']->prepare($sql);
        $stmt->bindValue(1, $data['name']);
        $stmt->bindValue(2, $data['note']);
        $stmt->execute();

        return $app['twig']->render('form-success.twig');

    } else {

        $title = 'Данные были заполнены не верно!';

        return $app['twig']->render('form.twig', [
            'data' => $data, 'title' => $title
        ]);

    }

});

$app->get('/note/{id}', function ($id) use ($app) {

    $sql = 'SELECT * FROM reviews WHERE id = ?';
    $note = $app['db']->fetchAssoc($sql, [(int)$id]);
    $prev = $id - 1;
    $next = $id + 1;

    return $app['twig']->render('note.twig', [
        'note' => $note, 'prev' => $prev, 'next' => $next
    ]);

});
