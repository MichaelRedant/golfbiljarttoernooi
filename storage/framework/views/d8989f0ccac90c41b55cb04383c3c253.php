<?php $__env->startSection('content'); ?>
    <h1>Bewerk Speler</h1>

    <form action="<?php echo e(route('players.update', $player)); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="form-group">
            <label for="first_name">Voornaam:</label>
            <input type="text" name="first_name" class="form-control" id="first_name" value="<?php echo e(old('first_name', $player->first_name)); ?>" required>
        </div>
        <div class="form-group">
            <label for="last_name">Achternaam:</label>
            <input type="text" name="last_name" class="form-control" id="last_name" value="<?php echo e(old('last_name', $player->last_name)); ?>" required>
        </div>
        <div class="form-group">
            <label for="team_id">Team:</label>
            <select name="team_id" class="form-control" id="team_id">
                <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($team->id); ?>" <?php echo e((old('team_id', $player->team_id) == $team->id) ? 'selected' : ''); ?>><?php echo e($team->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group">
            <label for="division_id">Divisie:</label>
            <select name="division_id" class="form-control" id="division_id">
                <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($division->id); ?>" <?php echo e((old('division_id', $player->division_id) == $division->id) ? 'selected' : ''); ?>><?php echo e($division->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group">
            <label for="photo">Foto:</label>
            <input type="file" name="photo" class="form-control" id="photo">
            <?php if($player->photo): ?>
                <img src="<?php echo e(asset('storage/photos/' . $player->photo)); ?>" width="100" alt="Speler foto">
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-primary">Opslaan</button>
    </form>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/players/edit.blade.php ENDPATH**/ ?>