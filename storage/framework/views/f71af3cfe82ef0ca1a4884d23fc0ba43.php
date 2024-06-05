<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

        <title>Golfbiljart</title>
<!-- Fonts en Styles -->
<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Nunito">
    <link href="<?php echo e(asset('css/stijl.css')); ?>" rel="stylesheet">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <!-- Scripts (Veronderstel dat Vite of vergelijkbare build tool gebruikt wordt voor assets) -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>
<body class="font-sans antialiased">
    <!-- Navigation -->
    <?php echo $__env->make('layouts.navigation', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

        <!-- Page Content -->
    <main>
        <?php echo $__env->yieldContent('content'); ?>
    </main>

    <footer class="footer">
        <div class="container">
            <img src="<?php echo e(asset('images/Pixapop_black.webp')); ?>" alt="Pixapop Logo" class="footer-logo">
            <p class="footer-text">Designed & created by <a href="https://pixapop.be" target="_blank">Pixapop webdesign</a> © <?php echo e(date('Y')); ?></p>
        </div>
    </footer>
    <!-- Optioneel JavaScript -->
<!-- jQuery eerst, dan Popper.js, dan Bootstrap JS -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const nightModeToggle = document.getElementById('nightModeToggle');
    const isNightMode = localStorage.getItem('nightMode') === 'true';

    // Stel de toggle in op basis van opgeslagen voorkeur
    nightModeToggle.checked = isNightMode;
    document.body.classList.toggle('night-mode', isNightMode);

    // Luister naar veranderingen in de toggle
    nightModeToggle.addEventListener('change', function() {
        document.body.classList.toggle('night-mode', this.checked);
        // Bewaar de voorkeur in localStorage
        localStorage.setItem('nightMode', this.checked);
    });
});
</script>
    </body>
</html>
<?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/layouts/guest.blade.php ENDPATH**/ ?>