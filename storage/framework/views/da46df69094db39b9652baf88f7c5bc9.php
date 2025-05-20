

<?php $__env->startSection('title', 'Order Details'); ?>

<?php $__env->startSection('content'); ?>
<div class="content-header">
    <div class="container-fluid">
        <h1 class="m-0">Order #<?php echo e($order->id); ?></h1>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="card">
            <div class="card-body">
                <h5>Order Information</h5>
                <p><strong>User:</strong> <?php echo e($order->user ? $order->user->name : 'Guest'); ?></p>
                <p><strong>Total Price:</strong> $<?php echo e(number_format($order->total_price, 2)); ?></p>
                <p><strong>Status:</strong> 
                    <span class="badge badge-<?php echo e($order->status == 'completed' ? 'success' : ($order->status == 'pending' ? 'warning' : ($order->status == 'shipped' ? 'info' : 'danger'))); ?>">
                        <?php echo e($order->status); ?>

                    </span>
                </p>
                <p><strong>Created At:</strong> <?php echo e($order->created_at->format('Y-m-d H:i:s')); ?></p>
                <p><strong>Updated At:</strong> <?php echo e($order->updated_at->format('Y-m-d H:i:s')); ?></p>

                <h5 class="mt-4">Order Items</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Quantity</th>
                            <th>Price</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $order->orderItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($item->product ? $item->product->name : 'Unknown'); ?></td>
                                <td><?php echo e($item->quantity); ?></td>
                                <td>$<?php echo e(number_format($item->price, 2)); ?></td>
                                <td>$<?php echo e(number_format($item->price * $item->quantity, 2)); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>

                <h5 class="mt-4">Payments</h5>
                <?php if($order->payments->isEmpty()): ?>
                    <p>No payments recorded.</p>
                <?php else: ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Method</th>
                                <th>Transaction ID</th>
                                <th>Created At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $order->payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $payment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($payment->id); ?></td>
                                    <td>$<?php echo e(number_format($payment->amount, 2)); ?></td>
                                    <td><?php echo e($payment->payment_status); ?></td>
                                    <td><?php echo e($payment->payment_method); ?></td>
                                    <td><?php echo e($payment->transaction_id ?? 'N/A'); ?></td>
                                    <td><?php echo e($payment->created_at->format('Y-m-d H:i:s')); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php endif; ?>

                <h5 class="mt-4">Downloads</h5>
                <?php if($order->downloads->isEmpty()): ?>
                    <p>No downloads available.</p>
                <?php else: ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Download URL</th>
                                <th>Expires At</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $order->downloads; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $download): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td><?php echo e($download->product ? $download->product->name : 'Unknown'); ?></td>
                                    <td><a href="<?php echo e($download->download_url); ?>" target="_blank">Download</a></td>
                                    <td><?php echo e($download->expires_at ? $download->expires_at->format('Y-m-d H:i:s') : 'No Expiry'); ?></td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH D:\Year 4\S2\E-COMMERCE\backend1\resources\views/admin/orders/show.blade.php ENDPATH**/ ?>