<?php
use bigwhoop\Formular\Provider\Bootstrap3Form;
use bigwhoop\Formular\Validator\CallbackValidator;

require __DIR__ . '/../vendor/autoload.php';

$form = new Bootstrap3Form();
$form->addElement('input', ['id,name' => 'first_name', 'label' => 'First Name', 'placeholder' => 'Your first name']);
$form->addElement('input', ['id,name' => 'last_name', 'label' => 'Last Name', 'placeholder' => 'Your last name']);
$form->addElement('input', ['id,name,type' => 'email', 'label' => 'E-Mail', 'placeholder' => 'Your e-mail address']);
$form->addElement('input', ['id,name,type' => 'password', 'label' => 'Password']);
$form->addElement('input', ['id,name' => 'password_confirmation', 'type' => 'password', 'label' => 'Password Confirmation']);
$form->addElement('checkboxes', ['id,name' => 'interests', 'label' => 'Interests', 'value' => [0,1], 'options' => ['Sports', 'Technology', 'Nature', 'Politics']]);
$form->addElement('radios', ['id,name' => 'language', 'label' => 'Prefered Language', 'value' => 'en', 'options' => ['en' => 'English', 'de' => 'Deutsch', 'fr' => 'Français']]);
$form->addElement('checkbox', ['id,name' => 'newsletter', 'label' => 'I want to get spammed by your newsletter', 'value' => 'yes']);
$form->addElement('checkbox', ['id,name' => 'accept_tos', 'label' => 'I accept the terms of service']);
$form->addElement('submit', ['label' => 'Register']);

$form->setValidators([
    '*' => new CallbackValidator(function($value) {
        return empty($value);
    }, "I reject all non-empty values like '%VALUE%'."),
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($form->isValid($_POST)) {
        exit('Successfully validated!');
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    
    <title>Bootstrap 3 Form Example</title>
    <link rel="stylesheet" type="text/css" href="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.2.0/js/bootstrap.min.js"></script>
</head>
<body>
    <div class="container">
        <h1>Bootstrap 3 Form Example</h1>
        <?= $form->render(); ?>
    </div>
</body>
</html>