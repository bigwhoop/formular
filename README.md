# Formular (WIP)

A light-weight, template-oriented form builder.

## Features

* Build custom forms using re-usable templates
* Write PHP templates. No need to learn a new syntax.
* Non-intrusive validation support. Take your 3rd party library and plug it in.
* Fully tested. 

## Terminology

* **Template**: A view script written in PHP and HTML.
* **Template Value**: A value inside a template, most likely coming from an element definition.
* **Element**: A part of the form that is rendered using a specific template.

## A simple example

    // ./templates/input.php
    <div class="row">
        <input <?= $this->type->attr('text'); ?> <?= $this->attributes(['id', 'name', 'value', 'placeholder']); ?> class="input input-large">
    </div>
    
    // ./templates/submit-button.php
    <div class="row">
        <button type="submit"><?= $this->label; ?></button>
    </div>
    
    // ./src/form.php
    use bigwhoop\Formular\Form;
    
    $form = new Form();
    $form->addTemplatesPath(__DIR__ . '/../templates');
    $form->addElement('input', ['id,name' => 'name', 'placeholder' => 'Your name']);
    $form->addElement('input', ['id,name,type' => 'email', 'placeholder' => 'Your email address']);
    $form->addElement('submit', ['label' => 'Register']);
    echo $form->render();
    
Which outputs the following:
    
    <div class="row">
        <input type="text" id="name" name="name" placeholder="Your name" class="input input-large">
    </div>
    <div class="row">
        <input type="email" id="email" name="email" placeholder="Your email address" class="input input-large">
    </div>
    <div class="row">
        <button type="submit">Register</button>
    </div>

So what is happening here?

First we create some `.php` files in a directory. This templates directory we then register with the form using
`addTemplatesPath()`. The name of the files minus the extension are used as the template's name. Next we define some
elements using the available templates. When rendering the specified element, it's attributes will get passed on to the
templates.

### Conventions 

Here are the conventions you need to know.

* All elements are added to a queue.
* This means that ...
 * the elements are rendered in the same order they were added to the form.
 * each element is only rendered once.


## Templates

As learned in the first example, templates are `.php` files laying around in directories. You can add as many such
directories to your form as you wish.

### Template namespaces

Let's have a look at the following example:

    // ./templates1/input.php
    // ./templates2/input.php
    ...
    $form->addTemplatesPath('./templates1');
    $form->addTemplatesPath('./templates2');
    $form->addElement('input');
    $form->render();

This will make Formular go BOOM becaue it doesn't know which `input` template to use. To solve this problem you can use
namespaces.

    $form->addTemplatesPath('./templates1');
    $form->addTemplatesPath('./templates2', 'foo');
    $form->addElement('input');     // => uses templates1/input
    $form->addElement('input@foo'); // => uses templates2/input

You can also define a default namespace.

    $form->addTemplatesPath('./templates1', 'fu');
    $form->addTemplatesPath('./templates2', 'foo');
    $form->setDefaultNamespace('foo');
    $form->addElement('input@fu');  // => uses templates1/input
    $form->addElement('input');     // => uses templates2/input
    $form->addElement('input@foo'); // => uses templates2/input

### Template values

Inside templates you can access the element data using `$this->[KEY]`. This will provide access to a
`Value` object with a set of little helpers to make things easier. Even if the attribute does not exist you'll get
such an object. It's default value will be `null`.

    // Returns a Value object representing attribute 'foo'.
    <?php $foo = $this->foo; ?>      # instance of \bigwhoop\Formular\Value
    
    // Prints a string representing attribute 'foo'. Prints an empty string if the value is empty.
    <?= $this->foo; ?>               # '[VALUE]' or '' if value is empty
    
    // Same as above but uses 'bar' as a default value in case the current value is empty.
    <?= $this->foo->val('bar'); ?>   # '[VALUE]' or 'bar' if value is empty
    
    // Prints a string in the format of '[KEY]="[VALUE]"'. Prints an empty string if the value is empty.
    <?= $this->foo->attr(); ?>       # 'foo="[VALUE]"' or '' if value is empty
    <?= $this->foo->attr('bar'); ?>  # 'foo="[VALUE]"' or 'foo="bar"' if value is empty
    
    // Prints a string in the format of '[KEY]'. Prints an empty string if the value is empty.
    <?= $this->foo->prop(); ?>       # 'foo' or '' if value is empty
    <?= $this->foo->prop(true); ?>   # 'foo'
    <?= $this->foo->prop(false); ?>  # 'foo' or '' if value is empty


## Template providers

Formular comes with support for **Bootstrap 3** forms. Just create a `new bigwhoop\Formular\Provider\Bootstrap3Form()`
instead of a regular form and you're ready to go. Have a look at `templates/bootstrap3` for all the available templates
and how to use them.


## Bindings

### Continue binding

Use the continue binding to define where the next template should be rendered. By default the next template is rendered
and appended to the current template.

For ...

    <div class="depth-<?= $this->depth; ?>"></div>
    ...
    $form->addElement('text', ['depth' => 0]);
    $form->addElement('text', ['depth' => 1]);

... the output would be ...

    <div class="depth-1></div><div class="depth-2"></div>
    
If you use the continue binding, ... 

    <div class="depth-<?= $this->depth; ?>"><?= $this->next; ?></div>
    ...
    $form->addElement('text', ['depth' => 0, 'next' => $form->bindContinue(); ]);
    $form->addElement('text', ['depth' => 1]);
    
... you get the following, nested output:

    <div class="depth-1><div class="depth-2"></div></div>

### Error messages binding

The error messages binding allows for easy access to the error messages that occurred during validation.

    <ul><?php foreach ((array)$this->messages->val() as $error): ?><li><?= $error; ?></li><?php endforeach; ?></ul>
    ...
    $form->addElement('errors', ['messages' => $form->bindErrorMessages()]);

### Value/Variable binding

Using the value binding you can pass a wrapped value into the template, that gets unwrapped not until you access it.

    <h1><?= $this->foo; ?></h1>
    ...
    $obj->setName('Tim');
    $form->addElement('text', [
        'foo' => $form->bindValue([$obj, 'getName']),
    ]);
    $obj->setName('John');
    $form->render();

The output will be `<h1>John</h1>` and not `<h1>Tim</h1>`.

You can bind to all possible `callable`s and even public object properties `[$obj, 'propertyName']`.

Using `$form->bindVariable()` it's also possible to bind to a variable.

    $var = 'Tim';
    $form->addElement('text', [
        'foo' => $form->bindVariable($var),
    ]);
    $var = 'James';
    $form->render(); // => <h1>James</h1>

## License

See LICENSE file.