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
        The <strong>Kirby Comments</strong> plugin requires a database connection to
        work. You can use both MySQL and sqlite connections. By default the plugin
        will use sqlite and save the comment database in your content directory.
      </div>
    </div>

    <div class="field field-grid-item">
      <div class="note text marginalia">
        To change these database settings you have to put the required
        configuration values in your sites configuration file. Please see the
        <a href="http://github.com/dweidner/kirby-comments/">documentation</a> for
        a full reference of available options.
      </div>
    </div>

    <?php foreach ($connection as $option => $value): ?>

      <?php if (in_array($option, array('database'))):  ?>
        <div class="field field-is-readonly field-with-icon field-grid-item">
          <label class="label"><?php echo ucfirst($option); ?></label>
          <div class="field-content">
            <div class="input input-is-readonly"><?php echo !empty($value) ? html($value) : '[empty]'; ?></div>
            <div class="field-icon">
              <i class="icon fa fa-<?php echo $option; ?>"></i>
            </div>
          </div>
        </div>
      <?php elseif (in_array($option, array('password'))): ?>
        <div class="field field-is-readonly field-grid-item">
          <span class="label"><?php echo ucfirst($option); ?></span>
          <div class="input input-is-readonly"><?php echo str_repeat('*', strlen($value)); ?></div>
        </div>
      <?php else: ?>
        <div class="field field-is-readonly field-grid-item">
          <span class="label"><?php echo ucfirst($option); ?></span>
          <div class="input input-is-readonly"><?php echo !empty($value) ? html($value) : '[empty]'; ?></div>
        </div>
      <?php endif; ?>

    <?php endforeach; ?>

  </fieldset>

  <div class="buttons cf">
    <a class="btn btn-rounded btn-cancel" href="<?php echo $wizard->url($index - 1); ?>">Back</a>
    <input class="btn btn-rounded btn-submit" value="Continue" type="submit">
  </div>

  <input type="hidden" name="token" value="<?php echo csfr(); ?>">

</form>
