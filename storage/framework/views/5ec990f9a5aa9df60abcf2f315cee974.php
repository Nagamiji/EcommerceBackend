<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-Commerce</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
</head>
<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <nav class="main-header navbar navbar-expand navbar-white navbar-light">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link" data-widget="pushmenu" href="#" role="button"><i class="fas fa-bars"></i></a>
                </li>
            </ul>

            <ul class="navbar-nav ml-auto">
                <?php if(Auth::check()): ?>
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo e(Auth::user()->name); ?></span>
                    </li>
                    <li class="nav-item">
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" style="display: inline;">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="nav-link btn btn-link">Logout</button>
                        </form>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo e(route('login')); ?>">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <!-- Main Sidebar Container -->
        <aside class="main-sidebar sidebar-dark-primary elevation-4">
            <a href="#" class="brand-link">
                <span class="brand-text font-weight-light">E-Commerce</span>
            </a>
            <div class="sidebar">
                <nav class="mt-2">
                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
                        <?php if(Auth::check()): ?>
                            <?php if(Auth::user()->is_admin): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('admin.dashboard')); ?>" class="nav-link">
                                        <i class="nav-icon fas fa-tachometer-alt"></i>
                                        <p>Admin Dashboard</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo e(url('admin/products')); ?>" class="nav-link">
                                        <i class="nav-icon fas fa-box"></i>
                                        <p>Products</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo e(url('admin/categories')); ?>" class="nav-link">
                                        <i class="nav-icon fas fa-tags"></i>
                                        <p>Categories</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo e(url('admin/orders')); ?>" class="nav-link">
                                        <i class="nav-icon fas fa-shopping-cart"></i>
                                        <p>Orders</p>
                                    </a>
                                </li>
                            <?php elseif(Auth::user()->role === 'seller'): ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('seller.dashboard')); ?>" class="nav-link">
                                        <i class="nav-icon fas fa-tachometer-alt"></i>
                                        <p>Seller Dashboard</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('seller.products.create')); ?>" class="nav-link">
                                        <i class="nav-icon fas fa-plus"></i>
                                        <p>Add Product</p>
                                    </a>
                                </li>
                            <?php else: ?>
                                <li class="nav-item">
                                    <a href="<?php echo e(route('home')); ?>" class="nav-link">
                                        <i class="nav-icon fas fa-home"></i>
                                        <p>Home</p>
                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
        </aside>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <div class="content">
                <div class="container-fluid mt-3">
                    <?php echo $__env->yieldContent('content'); ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/js/adminlte.min.js"></script>
</body>
</html><?php /**PATH D:\Year 4\S2\E-COMMERCE\backend1\resources\views/layouts/app.blade.php ENDPATH**/ ?>