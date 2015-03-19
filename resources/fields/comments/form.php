<?php

$cancelUrl = purl($field->page(), 'show');
$deleteUrl = url('api/pages/' . $field->page()->hash() . '/comments/delete/');

$groups = array(
  array(
    'author' => array(
      'type' => 'text',
      'id' => 'comment-author',
      'label' => 'Author',
      'value' => '{{author}}',
    ),
    'author_email' => array(
      'type' => 'email',
      'id' => 'comment-author-email',
      'label' => 'Author E-Mail',
      'value' => '{{author_email}}',
    ),
    'author_url' => array(
      'type' => 'url',
      'id' => 'comment-author-url',
      'label' => 'Author Url',
      'value' => '{{author_url}}',
    ),
  ),
  array(
    'text' => array(
      'type' => 'textarea',
      'id' => 'comment-text',
      'label' => 'Text',
      'value' => '{{text}}',
    ),
    'redirect_to' => array(
      'type' => 'hidden',
      'value' => url('panel/' . $cancelUrl),
    ),
  ),
);

?>

<script class="comment-form-template" type="text/x-handlebars-template">

    <div class="modal-content modal-content-wide">

      <form class="form comment-form" method="post" action="<?php echo url('api/pages'); ?>/{{hash}}/comments/{{comment.id}}" data-method="put">

        <h2 class="hgroup hgroup-single-line hgroup-compressed cf">
          <span class="hgroup-title">
            <?php _l('comment.singular', 'Comment'); ?> (ID: {{comment.id}})
          </span>
          <span class="hgroup-options shiv shiv-dark shiv-left">
            <span class="hgroup-option-right" href="#">
              <a class="btn btn-with-icon" href="#" title="<?php _l('comment.ban', 'Ban'); ?>">
                <i class="icon fa fa-ban"></i>
              </a>
              <a class="btn btn-with-icon" href="<?php echo $deleteUrl; ?>{{comment.id}}" title="<?php _l('comment.delete', 'Delete'); ?>">
                <i class="icon fa fa-trash-o"></i>
              </a>
            </span>
          </span>
        </h2>

        {{#with comment}}

        <fieldset class="fieldset">

          <?php

            foreach($groups as $group => $fields) {

              if ($group === 0) echo '{{#if author}}';

              foreach($fields as $name => $definition) {

                $type = isset($definition['type']) ? $definition['type'] : 'text';
                $class = ucfirst($type) . 'Field';
                $field = new $class();
                $field->name = $name;

                foreach($definition as $k => $v) {
                  if ($k === 'type') continue;
                  $field->$k = $v;
                }

                echo $field;

              }

              if ($group === 0) echo '{{/if}}';

            }

          ?>

          <div class="buttons cf">
            <a class="btn btn-rounded btn-cancel" href="<?php echo $cancelUrl; ?>"><?php _l('cancel'); ?></a>
            <input class="btn btn-rounded btn-submit btn-primary" type="submit" value="<?php _l('save'); ?>">
          </div>

        </fieldset>

        {{/with}}

      </form>

    </div>

</script>
