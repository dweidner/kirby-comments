<form class="form" method="post" enctype="multipart/form-data" autocomplete="off">

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
        The <strong>Kirby Comments</strong> plugin tries to support you in
        migrating from your existing system. For now we have implemented a
        simple CSV importer but we plan to other file formats from WordPress or
        Disqus in the future.
      </div>
    </div>

    <div class="field field-grid-item">
      <label class="label" for="file">CSV File<abbr title="Required">*</abbr></label>
      <div class="input input-with-fileupload"><input type="file" id="file" name="file" required></div>
    </div>

    <div class="field field-grid-item">
      <label class="label" for="form-field-username">Head<abbr title="Required">*</abbr></label>
      <input type="text" name="head" id="head" class="input" required value="<?php echo implode(', ', array('id', 'page_uri', 'created_at', 'updated_at', 'author', 'author_email', 'x', 'text', 'status', 'parent_id', 'username', 'x', 'x', 'x', 'x', 'x', 'x', 'x', 'x', 'x', 'x', 'x', 'x', 'x', 'x')); ?>">
      <p class="field-help marginalia">
        Map each column in the csv to a column in the comments table. Available
        columns are: <?php echo implode(', ', $columns); ?>. Use an x to ignore
        the value given in the csv.
      </p>
    </div>

    <div class="field field-grid-item">
      <label class="label" for="form-field-username">Delimiter<abbr title="Required">*</abbr></label>
      <input type="text" name="delimiter" id="delimiter" class="input" required value=",">
      <p class="field-help marginalia">The field delimiter separating each column.</p>
    </div>

    <div class="field field-grid-item">
      <label class="label" for="form-field-username">Enclosure<abbr title="Required">*</abbr></label>
      <input type="text" name="enclosure" id="enclosure" class="input" required value='"'>
      <p class="field-help marginalia">The character used to enclose field values.</p>
    </div>

  </fieldset>

  <div class="buttons cf">
    <a class="btn btn-rounded btn-cancel" href="<?php echo $wizard->url($index - 1); ?>">Back</a>
    <input class="btn btn-rounded btn-submit" value="Continue" type="submit">
  </div>

  <input type="hidden" name="token" value="<?php echo csfr(); ?>">

</form>
