<?php

$hash = $field->page->hash();
$id = $comment->id();

?>

<div id="comment-<?php echo $comment->id(); ?>" class="item item-condensed item-with-image">
  <div class="item-content">
    <figure class="item-image">
      <a class="item-image-container" href="#">
        <?php echo $comment->gravatar(); ?>
      </a>
    </figure>
    <div class="item-info">
      <strong class="item-title"><?php echo $comment->author(); ?></strong>
      <small class="item-meta marginalia"><?php echo $comment->text()->excerpt(40); ?></small>
    </div>
  </div>
  <nav class="item-options">
    <ul class="nav nav-bar">
      <li>
        <a class="btn btn-with-icon" href="<?php echo $comment->panelUrl('edit'); ?>">
          <i class="icon icon-left fa fa-pencil"></i>
          <span><?php _l('comment.edit', 'Edit'); ?></span>
        </a>
      </li>
      <li>
        <a class="btn btn-with-icon" href="<?php echo $comment->panelUrl('ban'); ?>">
          <i class="icon icon-left fa fa-ban"></i>
          <span><?php _l('comment.ban', 'Ban'); ?></span>
        </a>
      </li>
      <li>
        <a class="btn btn-with-icon" href="<?php echo $comment->actionUrl('delete'); ?>">
          <i class="icon icon-left fa fa-trash-o"></i>
          <span><?php _l('comment.delete', 'Delete'); ?></span>
        </a>
      </li>
    </ul>
  </nav>
</div>
