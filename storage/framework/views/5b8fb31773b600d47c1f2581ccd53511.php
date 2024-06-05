<?php $__env->startSection('content'); ?>
<div class="card p-3">
    <h1>Team Bewerken</h1>
 
    <form action="<?php echo e(route('teams.update', $team)); ?>" method="POST">
        <?php echo csrf_field(); ?>
        <?php echo method_field('PUT'); ?>
        <div class="form-group">
            <label for="name">Team Naam:</label>
            <input type="text" name="name" class="form-control" id="name" value="<?php echo e($team->name); ?>" required>
        </div>
        <div class="form-group">
            <label for="division_id">Divisie:</label>
            <select name="division_id" class="form-control" id="division_id" required>
                <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($division->id); ?>" <?php echo e($team->division_id == $division->id ? 'selected' : ''); ?>>
                        <?php echo e($division->name); ?>

                    </option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Opslaan</button>
        <h2 class="mt-4 p-4">Spelers in dit Team</h2>
<table class="table">
    <thead>
        <tr>
            <th>Spelernaam</th>
            <th>Acties</th>
            <th>Verplaats naar ander Team</th> <!-- Extra kolom voor het verplaatsingsformulier -->
        </tr>
    </thead>
    <tbody>
        <?php $__currentLoopData = $players; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $player): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <tr>
            <td>
                 <a href="<?php echo e(route('players.edit', $player)); ?>"><?php echo e($player->first_name); ?> <?php echo e($player->last_name); ?></a>  
            </td>
          
            <td>
                <a href="<?php echo e(route('players.edit', $player)); ?>" class="btn btn-info">Bewerk</a>
                <a href="<?php echo e(route('players.remove', ['team' => $team->id, 'player' => $player->id])); ?>" class="btn btn-warning">Verwijder uit team</a>
            </td>
            <td>
                <form action="<?php echo e(route('players.moveToTeam', $player)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <select name="new_team_id" class="form-control">
                        <?php $__currentLoopData = $allTeams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($team->id); ?>"><?php echo e($team->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button type="submit" class="btn btn-success btn-sm mt-1">Verplaats</button>
                </form>
            </td>
        </tr>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </tbody>
</table>

        
    </form>
</div>
    
<?php $__env->stopSection(); ?>
 
<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/teams/edit.blade.php ENDPATH**/ ?>