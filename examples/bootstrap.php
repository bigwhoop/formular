<?php
use bigwhoop\Formular\Provider\Bootstrap3Form;
use bigwhoop\Formular\Validator\RespectValidationValidator;
use bigwhoop\Formular\Validator\CallbackValidator;
use Respect\Validation\Validator as v;

require __DIR__ . '/../vendor/autoload.php';

$form = new Bootstrap3Form();
$form->addElement('input', ['id,name' => 'first_name', 'label' => 'First Name', 'placeholder' => 'Your first name']);
$form->addElement('input', ['id,name' => 'last_name', 'label' => 'Last Name', 'placeholder' => 'Your last name']);
$form->addElement('input', ['id,name,type' => 'email', 'label' => 'E-Mail', 'placeholder' => 'Your e-mail address']);
$form->addElement('input', ['id,name,type' => 'password', 'label' => 'Password']);
$form->addElement('input', ['id,name' => 'password_confirmation', 'type' => 'password', 'label' => 'Password Confirmation']);
$form->addElement('checkboxes', ['id,name' => 'interests', 'label' => 'Interests', 'values' => [0,1], 'options' => ['Sports', 'Technology', 'Nature', 'Politics']]);
$form->addElement('radios', ['id,name' => 'language', 'label' => 'Prefered Language', 'value' => 'en', 'options' => ['en' => 'English', 'de' => 'Deutsch', 'fr' => 'Français']]);
$form->addElement('checkbox', ['id,name' => 'newsletter', 'label' => 'I want to get spammed by your newsletter', 'checked' => true]);
$form->addElement('checkbox', ['id,name' => 'accept_tos', 'label' => 'I accept the terms of service']);
$form->addElement('submit', ['label' => 'Register']);

$form->setValidators([
    'first_name' => new RespectValidationValidator('First Name', v::create()->notEmpty()->string()->length(3, 20)),
    'last_name' => new RespectValidationValidator('Last Name', v::create()->notEmpty()->string()->length(3, 20)),
    'email' => new RespectValidationValidator('E-Mail', v::create()->notEmpty()->email()),
    'password' => new RespectValidationValidator('Password', v::create()->notEmpty()->string()->length(6)),
    'password_confirmation' => new RespectValidationValidator('Password Confirmation', v::create()->notEmpty()->string()->length(6)),
    'accept_tos' => new CallbackValidator(function($value) {
        return !!$value;
    }, 'You must accept the terms of service (%VALUE%)'),
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