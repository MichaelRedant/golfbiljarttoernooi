<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container">
        <a class="navbar-brand" href="<?php echo e(url('/')); ?>">Golfbiljart Bond Aalst</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="<?php echo e(route('home')); ?>">Home</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownDivisions" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Divisies
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownDivisions">
                        <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a class="dropdown-item" href="<?php echo e(route('divisions.show', $division->id)); ?>">
                            <?php echo e($division->name); ?>

                        </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('rankings.index')); ?>">Rankings</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo e($hasLiveMatches ? 'text-primary' : ''); ?>" href="<?php echo e(route('live-scores')); ?>">Live</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownInfo" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Informatie
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdownInfo">
                        <a class="dropdown-item" href="<?php echo e(route('clubs.index')); ?>">Clubs</a>
                        <a class="dropdown-item" href="<?php echo e(route('teams.index')); ?>">Teams</a>
                        <a class="dropdown-item" href="<?php echo e(route('players.index')); ?>">Spelers</a>
                        <a class="dropdown-item" href="<?php echo e(route('teams.addresses')); ?>">Adressen</a>
                    </div>
                </li>
                <?php if(auth()->guard()->check()): ?>
                    <?php if(Auth::user()->role == 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo e(route('dashboard')); ?>">Admin Dashboard</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>
            <ul class="navbar-nav ms-auto">
                <?php if(auth()->guard()->check()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownMenuLink" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo e(Auth::user()->name); ?>

                    </a>
                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                        <a class="dropdown-item" href="<?php echo e(route('dashboard')); ?>">Dashboard</a>
                        <?php if(Auth::user()->role == 'admin'): ?>
                            <a class="dropdown-item" href="<?php echo e(route('players.create')); ?>">Nieuwe Speler Toevoegen</a>
                            <a class="dropdown-item" href="<?php echo e(route('teams.create')); ?>">Nieuw Team Toevoegen</a>
                            <a class="dropdown-item" href="<?php echo e(route('divisions.create')); ?>">Nieuwe Divisie Toevoegen</a>
                            <a class="dropdown-item" href="<?php echo e(route('clubs.create')); ?>">Nieuwe Club Toevoegen</a>
                            <a class="dropdown-item" href="<?php echo e(route('seasons.create')); ?>">Nieuw Seizoen Toevoegen</a>
                            <a class="dropdown-item" href="<?php echo e(route('games.create', ['division_id' => $divisions->first()->id ?? null, 'season_id' => $currentSeason->id ?? null])); ?>">Nieuwe Wedstrijd</a> <!-- Corrected this line -->
                        <?php endif; ?>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="<?php echo e(route('logout')); ?>" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                            Uitloggen
                        </a>
                        <form id="logout-form" action="<?php echo e(route('logout')); ?>" method="POST" class="d-none">
                            <?php echo csrf_field(); ?>
                        </form>
                    </div>
                </li>
                <div class="vr mx-3"></div>
                <div class="navbar-text d-flex align-items-center">
                    <i class="fas fa-sun" style="color:#e5e500;"></i>
                    <label class="switch mx-2">
                        <input type="checkbox" id="nightModeToggle">
                        <span class="slider round"></span>
                    </label>
                    <i class="fas fa-moon" style="color: black;"></i>
                </div>
                <?php endif; ?>
                <?php if(auth()->guard()->guest()): ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo e(route('login')); ?>">Login</a>
                </li>
                <div class="vr mx-3"></div>
                <div class="navbar-text d-flex align-items-center">
                    <i class="fas fa-sun" style="color:#e5e500;"></i>
                    <label class="switch mx-2">
                        <input type="checkbox" id="nightModeToggle">
                        <span class="slider round"></span>
                    </label>
                    <i class="fas fa-moon" style="color: black;"></i>
                </div>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<script>
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
<?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/layouts/navigation.blade.php ENDPATH**/ ?>