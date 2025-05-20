

<?php $__env->startSection('title', 'View Category'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">View Category</h1>
                </div>
                <div class="col-sm-6">
                    <a href="<?php echo e(route('categories.index')); ?>" class="btn btn-secondary float-right">Back</a>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <?php if(isset($category)): ?>
                <div class="card">
                    <div class="card-body">
                        <p><strong>ID:</strong> <?php echo e($category->id); ?></p>
                        <p><strong>Name:</strong> <?php echo e($category->name); ?></p>
                        <p><strong>Description:</strong> <?php echo e($category->description ?? 'None'); ?></p>
                        <p><strong>Products Count:</strong> <?php echo e($category->products_count ?? 0); ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-danger">
                    Category not found or an error occurred.
                </div>
            <?php endif; ?>
        </div>
    </section>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Year 4\S2\E-COMMERCE\backend1\resources\views/admin/categories/show.blade.php ENDPATH**/ ?>