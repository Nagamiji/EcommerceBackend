

<?php $__env->startSection('title', 'Orders'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">Orders</h1>
            </div>
            <div class="col-sm-6">
                <a href="<?php echo e(route('orders.create')); ?>" class="btn btn-primary float-right">Add New Order</a>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <?php if(session('success')): ?>
            <div class="alert alert-success">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>
        <?php if($errors->any()): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
        <div class="card">
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Total Price</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($order->id); ?></td>
                                <td><?php echo e($order->user ? $order->user->name : 'Guest'); ?></td>
                                <td>$<?php echo e(number_format($order->total_price, 2)); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo e($order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : ($order->status == 'shipped' ? 'info' : 'danger'))); ?>">
                                        <?php echo e($order->status); ?>

                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo e(route('orders.show', $order->id)); ?>" class="btn btn-info btn-sm">View</a>
                                    <a href="<?php echo e(route('orders.edit', $order->id)); ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <form action="<?php echo e(route('orders.destroy', $order->id)); ?>" method="POST" style="display:inline;">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this order?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center">No orders available.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Year 4\S2\E-COMMERCE\backend1\resources\views/admin/orders/index.blade.php ENDPATH**/ ?>