
<header id="topbar" class="topbar">

  <a id="menu-toggle" class="nav-icon nav-icon-left" data-dropdown="true" href="#menu">
    <i class="icon fa fa-bars fa-lg"></i>
  </a>

  <nav id="menu" class="dropdown dropdown-left" style="display: none;">
    <ul class="nav nav-list dropdown-list">
      <li>
        <a href="<?php echo url('panel'); ?>">
          <i class="icon icon-left fa fa-file-o"></i>Dashboard</a>
      </li>
      <li>
        <a href="<?php echo url('panel/logout'); ?>">
          <i class="icon icon-left fa fa-power-off"></i>Logout</a>
      </li>
    </ul>
  </nav>

  <nav class="breadcrumb">

    <a class="nav-icon nav-icon-left" data-dropdown="" href="#breadcrumb-menu">
      <i class="icon fa fa-sitemap fa-lg"></i>
    </a>

    <ul class="nav nav-bar breadcrumb-list cf">
      <li>
        <a class="breadcrumb-link" href="#">
          <span class="breadcrumb-label">Plugin Installation</span>
        </a>
      </li>
      <li>
        <a class="breadcrumb-link" href="<?php echo $parent->url(); ?>">
          <span class="breadcrumb-label">Kirby Comments</span>
        </a>
      </li>
    </ul>

    <nav style="display: none;" id="breadcrumb-menu" class="dropdown dropdown-left breadcrumb-dropdown">

      <ul class="nav cf dropdown-list">
        <li>
          <a href="<?php echo url('panel'); ?>"><span>Dashboard</span></a>
        </li>
        <li>
          <a href="<?php echo $parent->url(); ?>"><span>Kirby Comments</span></a>
        </li>
      </ul>

    </nav>

  </nav>

  <?php if ($user = user::current()): ?>
    <a class="nav-icon nav-icon-right" href="<?php echo url('panel/logout'); ?>" title="Logout: <?php echo $user->username(); ?>">
      <i class="icon fa fa-user fa-lg"></i>
    </a>
  <?php endif; ?>

</header>
