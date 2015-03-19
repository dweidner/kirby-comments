<h2 class="hgroup hgroup-single-line hgroup-compressed">
  <span class="hgroup-title">
    <a href="#">Installation Steps</a>
  </span>
</h2>

<ul class="nav nav-list sidebar-list datalist-items">
  <?php foreach($items as $item): ?>
  <li>

    <?php

      $icon = 'fa-square-o';
      if ($item->index() == $step) {
        $icon = 'fa-caret-square-o-right';
      } else if ($item->index() <  $step) {
        $icon = 'fa-check-square-o';
      }

    ?>

    <span>
      <i class="icon icon-left fa <?php echo $icon; ?>"></i>
      <span><?php echo $item->title(); ?></span>
      <small class="marginalia"><?php echo $item->desc(); ?></small>
    </span>
  </li>
  <?php endforeach; ?>
</ul>
