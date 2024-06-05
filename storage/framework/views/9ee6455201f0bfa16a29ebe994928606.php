<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Golfbiljart')); ?></title>
    <!-- Fonts en Styles -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito">
    <link href="<?php echo e(asset('css/stijl.css')); ?>" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>
<body class="font-sans antialiased">
    <!-- Navigation -->
    <?php echo $__env->make('layouts.navigation', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Page Heading -->
    <?php if(isset($header)): ?>
        <header class="bg-white dark:bg-gray-800 shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                <?php echo e($header); ?>

            </div>
        </header>
    <?php endif; ?>

    <!-- Flash Messages -->
    <?php echo $__env->make('partials.flash-messages', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Page Content -->
    <main class="content-wrapper">
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer class="footer">
        <div class="container">
            <img src="<?php echo e(asset('images/Pixapop_black.webp')); ?>" alt="Pixapop Logo" class="footer-logo">
            <p class="footer-text">Designed & created by <a href="https://pixapop.be" target="_blank">Pixapop webdesign</a> © <?php echo e(date('Y')); ?></p>
        </div>
    </footer>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const nightModeToggle = document.getElementById('nightModeToggle');
            const isNightMode = localStorage.getItem('nightMode') === 'true';

            // Set the toggle based on saved preference
            nightModeToggle.checked = isNightMode;
            document.body.classList.toggle('night-mode', isNightMode);

            // Listen for changes in the toggle
            nightModeToggle.addEventListener('change', function() {
                document.body.classList.toggle('night-mode', this.checked);
                // Save the preference in localStorage
                localStorage.setItem('nightMode', this.checked);
            });
        });
    </script>
</body>
</html>
<?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\resources\views/layouts/app.blade.php ENDPATH**/ ?>