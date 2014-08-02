# Formular

A light-weight, template-oriented form builder.


## Features

* Build custom forms using re-usable templates (Bootstrap, ...).
* Write PHP templates. No need to learn a new syntax.
* Non-intrusive filtering and validation support. Take your 3rd party library and plug it in using the available adapters.
* Fully tested. 


## Terminology

* **Template**: A view script written in PHP and HTML.
* **Template Value**: A value inside a template, most likely coming from an element definition.
* **Element**: A part of the form that is rendered using a specific template.


## A simple example

    // ./templates/input.php
    <div class="row">
        <input <?= $this->type->attr('text'); ?> <?= $this->attr(['id', 'name', 'value', 'placeholder']); ?> class="input input-large">
    </div>
    
    // ./templates/submit.php
    <div class="row">
        <button type="submit"><?= $this->label; ?></button>
    </div>
    
    // ./src/form.php
    use bigwhoop\Formular\Form;
    use bigwhoop\Formular\TemplateFactory\FileBasedFactory;
    
    $templateFactory = new FileBasedFactory();
    $templateFactory->addTemplatesPath(__DIR__ . '/templates');
    
    $form = new Form();
    $form->setTemplatesFactory($templateFactory);
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

First we create some `.php` files in a directory. This templates directory we then register with the form using a new
`FileBasedFactory`. The name of the files minus the extension are used as the template's name. Next we define some
elements using the available templates. When rendering the specified element, its attributes will get passed on to the
templates.

### Conventions 

Here are the conventions you need to know.

* All elements are added to a queue.
* This means that ...
 * the elements are rendered in the same order they were added to the form.
 * each element is only rendered once.


## Templates

Templates are provided by template factories. The easiest factory is a the file-based `FileBasedFactory`; as seen
in the first example. There templates are `.php` files laying around in directories. You can add as many such
directories to your form as you wish.

### Template namespaces

Let's have a look at the following example:

    // ./templates1/input.php
    // ./templates2/input.php
    ...
    $templateFactory->addTemplatesPath('./templates1');
    $templateFactory->addTemplatesPath('./templates2');
    ...
    $form->addElement('input');
    $form->render();

This will make Formular go BOOM becaue it doesn't know which `input` template to use. To solve this problem you can use
namespaces.

    $templateFactory->addTemplatesPath('./templates1');
    $templateFactory->addTemplatesPath('./templates2', 'foo');
    ...
    $form->addElement('input');     // uses ./templates1/input
    $form->addElement('input@foo'); // uses ./templates2/input

You can also define a default namespace.

    $templateFactory->addTemplatesPath('./templates1', 'fu');
    $templateFactory->addTemplatesPath('./templates2', 'foo');
    $templateFactory->setDefaultNamespace('foo');
    ...
    $form->addElement('input@fu');  // => uses ./templates1/input
    $form->addElement('input');     // => uses ./templates2/input
    $form->addElement('input@foo'); // => uses ./templates2/input

### Template values

Inside templates you can access the element data/attributes using `$this->[KEY]`. This will provide access to a
`Value` object with a set of little helpers to make things easier. Even if the attribute does not exist you'll get
such an object. It's default value will be `null`.

    // Returns a Value object representing attribute 'foo'.
    <?php $foo = $this->foo; ?>                 # instance of \bigwhoop\Formular\Template\Value
    
    // Prints an escaped string representing attribute 'foo'.
    <?= $this->foo; ?>                          # '[SAFE_VALUE]' or '' if value is empty
    <?= $this->foo->str(); ?>                   # '[SAFE_VALUE]' or '' if value is empty
    <?= $this->foo->str('bar'); ?>              # '[SAFE_VALUE]' or 'bar' if value is empty
    
    // Returns the value the attribute encapsulates.
    <?php $val = $this->foo->val(); ?>          # [VALUE] or null if no value was set
    <?php $val = $this->foo->val('bar'); ?>     # [VALUE] or 'bar' if value is empty
    
    // Prints a string in the format of '[KEY]="[VALUE]"'. Prints an empty string if the value is empty.
    <?= $this->foo->attr(); ?>                  # 'foo="[SAFE_VALUE]"' or '' if value is empty
    <?= $this->foo->attr('bar'); ?>             # 'foo="[SAFE_VALUE]"' or 'foo="bar"' if value is empty
    
    // Prints a string in the format of '[KEY]'. Prints an empty string if the value is empty.
    <?= $this->foo->prop(); ?>                  # 'foo'/[KEY] or '' if value is empty
    <?= $this->foo->prop(true); ?>              # 'foo'/[KEY]
    <?= $this->foo->prop(false); ?>             # 'foo'/[KEY] or '' if value is empty


## Template helpers

Inside templates you can also use some helper methods using `$this->[HELPER]()`.

    // Pass the current element data to a different template
    <?= $this->partial('template@ns'); ?>
    
    // Render multiple attributes at once
    <?= $this->attr(['id', 'name']); ?>
    ... same as ...
    <?= $this->id->attr(); ?> <?= $this->name->attr(); ?>
    
    // Render multiple properties at once
    <?= $this->prop(['id', 'name']); ?>
    ... same as ...
    <?= $this->id->prop(); ?> <?= $this->name->prop(); ?>

## Providers

The following forms/templates have been created so far:

* [Bootstrap 3](https://github.com/bigwhoop/formular-form-bootstrap3)

Other packages:

* [Laravel Integration](https://github.com/bigwhoop/formular-provider-laravel)


## Bindings

### Continue binding

Use the continue binding to define where the next template should be rendered. By default the next template is rendered
and appended to the current template.

For ...

    <div class="depth-<?= $this->depth; ?>"></div>
    ...
    $form->addElement('template', ['depth' => 1]);
    $form->addElement('template', ['depth' => 2]);

... the output would be ...

    <div class="depth-1"></div>
    <div class="depth-2"></div>
    
If you use the continue binding, ... 

    <div class="depth-<?= $this->depth; ?>">
        <?= $this->next->val(); ?>
    </div>
    ...
    $form->addElement('template', ['depth' => 1, 'next' => $form->bindContinue(); ]);
    $form->addElement('template', ['depth' => 2, 'next' => $form->bindContinue(); ]);
    $form->addElement('template', ['depth' => '3a']);
    $form->addElement('template', ['depth' => '3b']);
    
... you get the following, nested output:

    <div class="depth-1>
        <div class="depth-2">
            <div class="depth-3a"></div>
            <div class="depth-3b"></div>
        </div>
    </div>

**Make sure to call the `val()` and not `str()` on the bound value as otherwise you'd get escaped text.

### Error messages binding

The error messages binding allows for easy access to the error messages that occurred during validation. See the
validation chapter for more information on how to set up validators.

    <ul>
        <?php foreach ((array)$this->errors->val() as $error): ?>
            <li><?= $error; ?></li>
        <?php endforeach; ?>
    </ul>
    ...
    $form->addElement('template', ['errors' => $form->bindErrorMessages()]);

### Value/Variable binding

Using the value binding you can pass a wrapped value into the template that gets unwrapped not until you access it.

    <h1><?= $this->name; ?></h1>
    ...
    $obj->setName('Tim');
    $form->addElement('template', [
        'name' => $form->bindValue([$obj, 'getName']),
    ]);
    $obj->setName('John');
    $form->render();

The output will be `<h1>John</h1>` and not `<h1>Tim</h1>`.

You can bind to all possible `callable`s and even public object properties `[$obj, 'propertyName']`.

Using `$form->bindVariable()` it's also possible to bind to a variable.

    $var = 'Tim';
    $form->addElement('text', [
        'name' => $form->bindVariable($var),
    ]);
    $var = 'James';
    $form->render(); // => <h1>James</h1>


## Validation

The following adapters are available:

* [Zend Validator (ZF2)](https://github.com/zendframework/Component_ZendValidator): `\bigwhoop\Formular\Validation\Adapter\ZendFrameworkAdapter`
* [Respect Validation](https://github.com/Respect/Validation): `\bigwhoop\Formular\Validation\Adapter\RespectValidationAdapter`

See the validators example for how to use them.

## Filters

*WIP*

## License

See LICENSE file.