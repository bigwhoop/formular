Here are the conventions:

* All elements are added to a queue.
* This means that ...
 * the elements are rendered in the same order they were added to the form.
 * each element is only rendered once.

You can use `<?= $this->next(); ?>` to change where the next element should be rendered. By default the next
element is rendered after the current template.