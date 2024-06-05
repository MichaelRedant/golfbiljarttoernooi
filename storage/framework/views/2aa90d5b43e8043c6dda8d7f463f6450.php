<?php $__env->startSection('content'); ?>
    <h1>Nieuwe Speler Toevoegen</h1>

    <form action="<?php echo e(route('players.store')); ?>" method="POST" enctype="multipart/form-data">
        <?php echo csrf_field(); ?>
        <div class="form-group">
            <label for="first_name">Voornaam:</label>
            <input type="text" name="first_name" class="form-control" id="first_name" required>
        </div>
        <div class="form-group">
            <label for="last_name">Achternaam:</label>
            <input type="text" name="last_name" class="form-control" id="last_name" required>
        </div>
        <div class="form-group">
            <label for="division_id">Divisie:</label>
            <select name="division_id" class="form-control" id="division_id" required>
                <option value="">Selecteer een divisie</option>
                <?php $__currentLoopData = $divisions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $division): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($division->id); ?>"><?php echo e($division->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div class="form-group">
            <label for="team">Team:</label>
            <select name="team_id" required>
                <option value="">Selecteer een team</option>
                <?php $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($team->id); ?>"><?php echo e($team->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        
        <button type="submit" class="btn btn-primary">Opslaan</button>
    </form>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#division_id').on('change', function () {
                var divisionId = $(this).val();
                if (divisionId) {
                    $.ajax({
                        type: 'GET',
                        url: '<?php echo e(route('get-teams')); ?>',
                        data: {division_id: divisionId},
                        success: function (teams) {
                            $('#team').empty();
                            $.each(teams, function (key, value) {
                                $('#team').append('<option value="' + value.id + '">' + value.name + '</option>');
                            });
                        }
                    });
                } else {
                    $('#team').empty();
                }
            });
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\michael.redant\Downloads\xnd\tab\golfbiljarttoernooi\golfbiljarttoernooi\resources\views/players/create.blade.php ENDPATH**/ ?>