<form class="form" method="post" autocomplete="off">

  <?php if (isset($errors)): ?>
    <div class="message-list">
      <?php echo $wizard->alert($errors); ?>
    </div>
  <?php endif; ?>

  <fieldset class="fieldset field-grid cf">

    <div class="field field-grid-item field-with-headline">
      <h2 class="hgroup hgroup-single-line hgroup-compressed cf">
        <span class="hgroup-title"><?php echo $title; ?> - <?php echo $desc; ?></span>
      </h2>
    </div>

    <div class="field field-grid-item">
      <div class="text">
        Wouldn't it be wonderful to maintain comments directly in Kirby’s
        wonderful Panel? We provide you with a custom field that displays
        comments posted on a specific page directly on the corresponding
        administration page. Simply add the following to the blueprint of your
        content type:

<pre><code>
  comments:
    label: Comments
    type: comments
</code></pre>

      </div>
    </div>

  </fieldset>

  <div class="buttons cf">
    <a class="btn btn-rounded btn-cancel" href="<?php echo $wizard->url($index - 1); ?>">Back</a>
    <a class="btn btn-rounded btn-submit" href="<?php echo $wizard->url($index + 1); ?>">Skip</a>
    <input class="btn btn-rounded btn-submit" value="Continue" type="submit">
  </div>

  <input type="hidden" name="token" value="<?php echo csfr(); ?>">

</form>
