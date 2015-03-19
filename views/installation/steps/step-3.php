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
        In a next step the <strong>Kirby Comments</strong> installation script will
        create the required database tables for you. Please ensure these do not
        already exist in your database otherwise they will be overwritten.
      </div>
    </div>

    <?php $prefix = c::get('db.prefix', ''); ?>
    <?php $prefix = empty($prefix) ? $prefix : '[' . $prefix . '] '; ?>

    <?php foreach ($tables as $name): ?>
    <div class="field field-is-readonly field-grid-item">
      <div class="input input-is-readonly"><?php echo html($prefix . $name); ?></div>
    </div>
    <?php endforeach; ?>

    <div class="field field-grid-item">
      <div class="note text marginalia">
        <strong>TIP</strong>: You can define a custom prefix in your configuration
        file using <code>c::set('db.prefix', '')</code> to avoid naming collisions
        with your existing tables.
      </div>
    </div>

  </fieldset>

  <div class="buttons cf">
    <a class="btn btn-rounded btn-cancel" href="<?php echo $wizard->url($index - 1); ?>">Back</a>
    <input class="btn btn-rounded btn-submit" value="Continue" type="submit">
  </div>

  <input type="hidden" name="token" value="<?php echo csfr(); ?>">

</form>
