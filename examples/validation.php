<?php
use bigwhoop\Formular\Form;
use bigwhoop\Formular\TemplateFactory\FileBasedFactory;
use bigwhoop\Formular\Validation;
use Respect\Validation\Validator as v;
use Zend\Validator as ZF2;

require __DIR__ . '/../vendor/autoload.php';

$templateFactory = new FileBasedFactory();
$templateFactory->addTemplatesPath(__DIR__ . '/templates');

$form = new Form();
$form->setTemplateFactory($templateFactory);

$form->addElement('form', [
    'elements' => $form->bindContinue(),
    'errors'   => $form->bindErrorMessages(),
]);

$form->addElement('input', ['id,name' => 'name', 'label' => 'Your Name']);
$form->addElement('input', ['id,name,type' => 'email', 'label' => 'Your E-Mail Address']);
$form->addElement('input', ['id,name' => 'captcha', 'label' => 'Captcha']);
$form->addElement('submit', ['label' => 'Signup for Newsletter']);

$captchaValue = mt_rand(10000, 99999);

$form->setValidators([
    // Each validator has a key that is matched against the ID of a form element. Form elements without IDs can't
    // get validated. The validation starts with the first element and validates it against all found validators
    // in the order they were added. Then it moves on to the next element.
    
    // RespectValidationValidator enables you to easily integrate https://github.com/Respect/Validation 
    'name' => new Validation\Adapter\RespectValidationAdapter('Name', v::create()->notEmpty()->string()->length(10, 20)),
    
    // You can also Zend Framework 2 validators. Just pass in multiple validators to chain them up.
    'email' => new Validation\Adapter\ZendFrameworkAdapter(
        'E-Mail',
        new ZF2\NotEmpty(),
        new ZF2\EmailAddress()
        // new \ZF2...
        // ...
    ),
    
    // CallbackValidator enables you to easily integrate custom validators. The 2nd argument is a error message
    // which can contain a %VALUE% term that gets replaced with the user input upon failed validation.
    'captcha' => new Validation\CallbackValidator(function($value) use ($captchaValue) {
        return $value === $captchaValue;
    }, "The correct captcha value would have been '$captchaValue', not '%VALUE%'."),
    
    // You can assign validators to multiple elements.
    'name,email' => new Validation\CallbackValidator(function() { return true; }, "I'm invisible!"), 
    
    // Or even match all elements.
    '*' => new Validation\CallbackValidator(function() { return false; }, "I don't like '%VALUE%' ..."),
]);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 'isValid()' will check the elements against the validators using the given values. This will also assign
    // the given value to the 'value' attribute of the element. So if you use \<?= $this->value; ?\> inside a
    // template you'll get the value that was used during validation.
    if ($form->isValid($_POST)) {
        exit('Successfully validated!');
    }
}
?>
<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title>Form Validation Example</title>
</head>
<body>
    <h1>Form Validation Example</h1>
    <?= $form->render(); ?>
</body>
</html>