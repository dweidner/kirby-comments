<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Installation | Kirby Comments</title>
  <link rel="stylesheet" href="<?php echo url('plugins/comments/assets/css/styles.css'); ?>">
</head>
<body class="app">

  <div class="main">

    <?php echo $header; ?>

    <div class="bars bars-with-sidebar-left cf">

      <aside class="sidebar sidebar-left">
        <a class="sidebar-toggle" href="#sidebar" data-hide="Hide options">
          <span>Show options</span>
        </a>
        <div class="sidebar-content section">
          <?php echo $nav; ?>
        </div>
      </aside>

      <div class="mainbar">
        <div class="section">
          <?php echo $content; ?>
        </div>
      </div>

    </div>

  </div>

  <script type="text/javascript" src="<?php echo url('plugins/comments/assets/js/scripts.css'); ?>"></script>

</body>
</html>
