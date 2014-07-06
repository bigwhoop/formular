<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

use bigwhoop\Formular\Form;

require __DIR__ . '/../vendor/autoload.php';

$form = new Form();
$form->addTemplatesPath(__DIR__ . '/../templates/bootstrap3');

$form->addElement('form');
$form->addElement('errors', [
    'errors' => $form->bind(function() {
        return [];
    }),
]);
$form->addElement('input', ['id,name' => 'first_name', 'label' => 'First Name', 'placeholder' => 'Your first name']);
$form->addElement('input', ['id,name' => 'last_name', 'label' => 'Last Name', 'placeholder' => 'Your last name']);
$form->addElement('input', ['id,name,type' => 'email', 'label' => 'E-Mail', 'placeholder' => 'Your e-mail address']);
$form->addElement('input', ['id,name,type' => 'password', 'label' => 'Password']);
$form->addElement('input', ['id,name' => 'password_confirmation', 'type' => 'password', 'label' => 'Password Confirmation']);
$form->addElement('checkbox', ['id,name' => 'accept_tos', 'label' => 'I accept the terms of service']);
$form->addElement('submit', ['label' => 'Register']);
?>
<!DOCTYPE HTML>
<html>
<head>
    <title>Horizontal Bootstrap Form Example</title>
    <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Horizontal Bootstrap Form Example</h1>
        <?= $form->render(); ?>
    </div>
</body>
</html>