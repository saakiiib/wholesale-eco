<?php $__env->startSection('title','Secure checkout'); ?>
<?php $__env->startSection('content'); ?><div class="pageintro"><h1>Business checkout</h1><p>Your order is securely processed by Stripe. No card numbers are stored here.</p></div>
<div class="twocol"><form class="panel formgrid" method="post" action="<?php echo e(route('checkout.submit')); ?>"><?php echo csrf_field(); ?>
<h2>Business & contact details</h2><label>Business name<input name="business_name" required value="<?php echo e(old('business_name',auth()->user()?->business?->name)); ?>"></label>
<label>Contact name<input name="contact_name" required value="<?php echo e(old('contact_name',auth()->user()?->name)); ?>"></label><label>Email<input type="email" name="email" required value="<?php echo e(old('email',auth()->user()?->email)); ?>"></label>
<label>Phone<input name="phone" value="<?php echo e(old('phone')); ?>"></label><h2>Delivery address</h2>
<label>Address line 1<input name="line1" required value="<?php echo e(old('line1')); ?>"></label><label>Address line 2<input name="line2" value="<?php echo e(old('line2')); ?>"></label>
<label>Town / City<input name="city" required value="<?php echo e(old('city')); ?>"></label><label>Postcode<input name="postcode" required value="<?php echo e(old('postcode')); ?>"></label>
<label class="check"><input type="checkbox" name="terms" value="1" required> I agree to the wholesale terms and confirm this is a business purchase.</label>
<button class="btn">Continue to secure payment →</button></form>
<aside class="panel summary"><h2>Order summary</h2><?php $__currentLoopData = $quote['lines']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><p><?php echo e($line['variant']->product->name); ?> (<?php echo e($line['packs']); ?> cases) <b>£<?php echo e(number_format($line['subtotal_pence']/100,2)); ?></b></p><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
<hr><p>Products <b>£<?php echo e(number_format($quote['subtotal_pence']/100,2)); ?></b></p><p>Delivery <b>£<?php echo e(number_format($quote['delivery_pence']/100,2)); ?></b></p><p>VAT on products <b>£<?php echo e(number_format($quote['vat_pence']/100,2)); ?></b></p><h2>£<?php echo e(number_format($quote['grand_total_pence']/100,2)); ?></h2><small>Payment is final only after the verified Stripe webhook confirms it.</small></aside></div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Users\sakib\Desktop\wholesaleecoproducts\wholesale-eco-laravel\resources\views/shop/checkout.blade.php ENDPATH**/ ?>