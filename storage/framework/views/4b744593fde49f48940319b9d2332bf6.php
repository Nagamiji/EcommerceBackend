

<?php $__env->startSection('title', 'Categories'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Categories</h1>
                </div>
                <div class="col-sm-6">
                    <a href="<?php echo e(url('/admin/categories/create')); ?>" class="btn btn-primary float-right">Add New Category</a>
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
            <?php if(session('error')): ?>
                <div class="alert alert-danger">
                    <?php echo e(session('error')); ?>

                </div>
            <?php endif; ?>
            <div class="card">
                <div class="card-body">
                    <p>Debug: Categories count = <?php echo e($categories->count()); ?></p>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Products</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr>
                                    <td><?php echo e($category->id); ?></td>
                                    <td><?php echo e($category->name); ?></td>
                                    <td><?php echo e($category->description ?? 'None'); ?></td>
                                    <td><?php echo e($category->products_count ?? 0); ?></td>
                                    <td>
                                        <a href="<?php echo e(url('/admin/categories/' . $category->id)); ?>" class="btn btn-info btn-sm">View</a>
                                        <a href="<?php echo e(url('/admin/categories/' . $category->id . '/edit')); ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <form action="<?php echo e(url('/admin/categories/' . $category->id)); ?>" method="POST" style="display:inline;" class="delete-form">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="text-center">No categories available.</td>
                                </tr>
                            <?php endif; ?>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.delete-form').forEach(form => {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to delete this category?')) {
                    this.submit();
                }
            });
        });
    });
</script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Year 4\S2\E-COMMERCE\backend1\resources\views/admin/categories/index.blade.php ENDPATH**/ ?>