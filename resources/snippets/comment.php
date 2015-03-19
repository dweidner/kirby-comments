<article class="comment comment--<?php echo $comment->id(); ?>">

  <div class="comment-meta">

    <div class="comment-author vcard">
      <?php echo $comment->gravatar(); ?>
      <a href="mailto:<?php echo $comment->authorEmail()->obfuscate(); ?>" class="fn">
        <?php echo $comment->author(); ?>
      </a>
    </div>

    <time datetime="<?php echo $comment->date('c'); ?>">
      <?php echo $comment->date('d.m.Y H:i'); ?>
    </time>

    <?php echo $comment->editLink(); ?>
    <?php echo $comment->deleteLink(); ?>

  </div>

  <div class="comment-content">
    <?php echo $comment->text()->safeMarkdown( c::get('comments.markdown') ); ?>
  </div>

</article>
