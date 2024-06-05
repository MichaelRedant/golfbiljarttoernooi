<?php $__env->startSection('title', 'Home'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mt-4">
    <div class="row">
        <!-- Navigation cards on the left side -->
        <div class="col-md-3">
            <div class="nav-flex-column">
                <a href="<?php echo e(route('divisions.index')); ?>" class="nav-card-link card my-2">Divisies</a>
                <a href="<?php echo e(route('teams.index')); ?>" class="nav-card-link card my-2">Teams</a>
                <a href="<?php echo e(route('players.index')); ?>" class="nav-card-link card my-2">Spelers</a>
                <a href="<?php echo e(route('rankings.index')); ?>" class="nav-card-link card my-2">Rankings</a>
                <a href="<?php echo e(route('clubs.index')); ?>" class="nav-card-link card my-2">Clubs</a>
                <a href="<?php echo e(route('live-scores')); ?>" class="nav-card-link card my-2">Live Wedstrijden</a>
            </div>
        </div>
        <!-- Content section about Golfbiljart -->
        <div class="col-md-9">
            <h1 class="text-center my-4">Welkom bij onze Golfbiljart Applicatie</h1>
            <p class="text-justify">
                Golfbiljart is een fascinerende sport die precisie, tactiek en vaardigheid combineert. Het wordt gespeeld op een speciale biljarttafel, waarbij het doel is om de ballen in een specifieke volgorde te raken en punten te scoren. Onze applicatie helpt liefhebbers van de sport om wedstrijden te organiseren, scores bij te houden en meer te leren over verschillende teams en spelers.
            </p>
            <p class="text-justify">
                Verken onze wedstrijdkalender om de aankomende evenementen te zien, duik in de details van verschillende divisies, of bekijk de prestaties van teams en spelers door onze uitgebreide rankings. Of je nu een speler, coach of gewoon een fan bent, onze app biedt iets voor iedereen.
            </p>
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title">Wat is Golfbiljart?</h2>
                </div>
                <div class="card-body">
                    <p class="text-justify">
                        Golfbiljart is een biljartspel dat zich onderscheidt door zijn unieke regels en speelmethode. Het spel vereist strategisch inzicht en een vaste hand om succesvol te zijn. Elk spel bestaat uit verschillende rondes waarin spelers moeten proberen hun ballen in de juiste volgorde te potten.
                    </p>
                    <p class="text-justify">
                        Golfbiljart wordt vaak gespeeld in competitieverband, met spelers die strijden om de hoogste eer binnen hun divisie. Deze applicatie biedt alle tools die nodig zijn om competities te beheren en de prestaties van spelers en teams te volgen.
                    </p>
                </div>
            </div>
            <div class="card mb-4">
                <div class="card-header">
                    <h2 class="card-title">Hoe te beginnen?</h2>
                </div>
                <div class="card-body">
                    <ul class="list-group">
                        <li class="list-group-item">Bekijk de <a href="<?php echo e(route('divisions.index')); ?>">divisies</a> en kies je favoriete teams.</li>
                        <li class="list-group-item">Volg je favoriete <a href="<?php echo e(route('teams.index')); ?>">teams</a> en <a href="<?php echo e(route('players.index')); ?>">spelers</a>.</li>
                        <li class="list-group-item">Blijf op de hoogte van de laatste <a href="<?php echo e(route('rankings.index')); ?>">rankings</a> en prestaties.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <!-- Video section -->
        <div class="col-md-12">
            <div class="video-container my-4">
                <video autoplay loop muted class="home-video">
                    <source src="<?php echo e(asset('images/billiards.mp4')); ?>" type="video/mp4">
                    Uw browser ondersteunt geen video tag.
                </video>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/home.blade.php ENDPATH**/ ?>