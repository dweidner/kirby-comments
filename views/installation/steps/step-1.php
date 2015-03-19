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

    <div class="field field-with-info field-grid-item">
      <div class="text">
        This installation guide will help you setting up the
         <strong>Kirby Comments</strong> plugin. To continue with the
        installation of the plugin please login as a user with
        <strong>administration</strong> privileges.
      </div>
    </div>

    <div class="field field-grid-item field-with-icon">
      <label class="label" for="form-field-username">Username<abbr title="Required">*</abbr></label>
      <div class="field-content">
        <input type="text" name="username" id="username" class="input" required autocomplete="on" autofocus>
        <div class="field-icon"><i class="icon fa fa-user"></i></div>
      </div>
    </div>

    <div class="field field-grid-item field-with-icon">
      <label class="label" for="form-field-password">Password<abbr title="Required">*</abbr></label>
      <div class="field-content">
        <input type="password" name="password" id="password" class="input" required autocomplete="on" >
        <div class="field-icon"><i class="icon fa fa-key"></i></div>
      </div>
    </div>

  </fieldset>

  <div class="buttons cf">
    <input class="btn btn-rounded btn-submit" value="Continue" type="submit">
  </div>

  <input type="hidden" name="token" value="<?php echo csfr(); ?>">

</form>
