<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Enable UUID extension for PostgreSQL
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp";');

        // 1. Users Table
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->string('name', 100);
            $table->string('email', 150)->unique();
            $table->string('password', 255);
            $table->uuid('current_routine')->nullable()->comment('Rutina/Split actualmente en curso');
            $table->integer('active_sequence_index')->default(1)->comment('Puntero al order_index que toca hoy (1, 2, 3...)');
            $table->timestamp('created_at')->useCurrent();
        });

        // 2. Routine Table
        Schema::create('routine', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 100)->comment('Ej: PPL 4 Días con Enfoque Dorsal');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(false)->comment('Solo un split activo a la vez');
            $table->timestamp('created_at')->useCurrent();
        });

        // Foreign Key from users to routine
        Schema::table('users', function (Blueprint $table) {
            $table->foreign('current_routine')->references('id')->on('routine')->nullOnDelete();
        });

        // 3. Workout Table
        Schema::create('workout', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete()->comment('La lista pertenece al usuario, no a una rutina');
            $table->string('name', 100)->comment('Ej: Jale A (Dorsal), Empuje A, Pierna');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 4. Routine Workouts Table
        Schema::create('routine_workouts', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('routine_id')->constrained('routine')->cascadeOnDelete();
            $table->foreignUuid('workout_id')->constrained('workout')->cascadeOnDelete();
            $table->integer('order_index')->comment('Orden rotativo de ejecución cíclica');
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['routine_id', 'order_index'], 'routine_workouts_index_0');
        });

        // 5. Exercises Table
        Schema::create('exercises', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('NULL = Catálogo precargado; Con ID = Ejercicio propio');
            $table->string('external_id', 50)->unique()->nullable()->comment('exerciseId del JSON de ExerciseDB (01qpYSe)');
            $table->string('name', 150);
            $table->string('gif_url', 255)->nullable()->comment('Path local descargado o URL remota');
            $table->jsonb('instructions')->nullable()->comment('Array de pasos ["Step:1 ...", "Step:2 ..."]');
            $table->timestamp('created_at')->useCurrent();
        });

        // 6. Workout Exercises Table
        Schema::create('workout_exercises', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('workout_id')->constrained('workout')->cascadeOnDelete();
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->integer('order_in_routine')->comment('1º Press banca, 2º Fondos...');
            $table->integer('rest_seconds')->default(90)->comment('Segundos para el timer de descanso GLOBAL');
            $table->text('notes')->nullable();
        });

        // 7. Workout Sets Table
        Schema::create('workout_sets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('work_exercise_id')->constrained('workout_exercises')->cascadeOnDelete();
            $table->integer('set_order')->comment('Define el numero de set del ejercicio');
            $table->enum('type', ['NORMAL', 'WARNUP', 'DROPSET', 'FAILURE'])->default('NORMAL');
            $table->integer('range_rep_ini');
            $table->integer('range_rep_end');
            $table->decimal('target_rpe', 3, 1)->nullable()->comment('Esfuerzo estimado (ej: 8.5)');
            $table->integer('rest_second')->nullable()->comment('Segundos de descanso especificado para este set');
        });

        // 8. Body Parts Table
        Schema::create('body_parts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 50)->unique()->comment('chest, back, waist, upper arms...');
        });

        // 9. Muscles Table
        Schema::create('muscles', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique()->comment('latissimus dorsi, biceps, quadriceps...');
        });

        // 10. Equipments Table
        Schema::create('equipments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 80)->unique()->comment('barbell, dumbbell, cable, body weight...');
        });

        // 11. Exercise Body Parts Pivot
        Schema::create('exercise_body_parts', function (Blueprint $table) {
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('body_part_id')->constrained('body_parts')->cascadeOnDelete();
            $table->primary(['exercise_id', 'body_part_id']);
        });

        // 12. Exercise Equipments Pivot
        Schema::create('exercise_equipments', function (Blueprint $table) {
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('equipment_id')->constrained('equipments')->cascadeOnDelete();
            $table->primary(['exercise_id', 'equipment_id']);
        });

        // 13. Exercise Muscles Pivot
        Schema::create('exercise_muscles', function (Blueprint $table) {
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->foreignId('muscle_id')->constrained('muscles')->cascadeOnDelete();
            $table->boolean('is_target')->default(true)->comment('true: targetMuscle, false: secondaryMuscle');
            $table->primary(['exercise_id', 'muscle_id', 'is_target']);
        });

        // 14. Exercise Substitutes Table
        Schema::create('exercise_substitutes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete()->comment('Ejercicio original');
            $table->foreignUuid('substitute_exercise_id')->constrained('exercises')->cascadeOnDelete()->comment('Alternativa si máquina ocupada');
            $table->string('notes', 255)->nullable()->comment('Ej: Si la polea alta está ocupada');
        });

        // 15. Workout Logs Table
        Schema::create('workout_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('workout_id')->constrained('workout')->cascadeOnDelete()->comment('Día de la secuencia ejecutado');
            $table->string('title', 100)->comment('Ej: Sesión de Empuje - 15 Oct');
            $table->timestamp('started_at');
            $table->timestamp('finished_at')->nullable();
            $table->integer('overall_rpe')->nullable()->comment('Fatiga general de la sesión (1-10)');
            $table->text('notes')->nullable();
        });

        // 16. Workout Log Sets Table
        Schema::create('workout_log_sets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('workout_log_id')->constrained('workout_logs')->cascadeOnDelete();
            $table->foreignUuid('exercise_id')->constrained('exercises')->cascadeOnDelete();
            $table->integer('set_order')->comment('Serie 1, 2, 3...');
            $table->enum('set_type', ['NORMAL', 'WARNUP', 'DROPSET', 'FAILURE'])->default('NORMAL');
            $table->decimal('weight_input', 6, 2)->comment('Valor exacto introducido (ej: 45.0)');
            $table->enum('weight_unit', ['KG', 'LBS'])->default('KG');
            $table->decimal('weight_kg', 6, 2)->comment('Valor normalizado a kg para cálculos');
            $table->integer('reps_completed');
            $table->decimal('rpe', 3, 1)->nullable();
            $table->decimal('calculated_1rm', 6, 2)->nullable();
            $table->boolean('is_personal_record')->default(false);
            $table->timestamp('created_at')->useCurrent();
        });

        // 17. Body Measurement Table
        Schema::create('body_measurement', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('log_date');
            $table->decimal('weight_kg', 5, 2)->comment('Peso en ayunas');
            $table->decimal('body_fat_percentage', 4, 2)->nullable();
            $table->decimal('neck_cm', 5, 2)->nullable();
            $table->decimal('chest_cm', 5, 2)->nullable();
            $table->decimal('waist_navel_cm', 5, 2)->nullable();
            $table->decimal('hips_cm', 5, 2)->nullable();
            $table->decimal('bicep_left_cm', 5, 2)->nullable();
            $table->decimal('bicep_right_cm', 5, 2)->nullable();
            $table->decimal('thigh_left_cm', 5, 2)->nullable();
            $table->decimal('thigh_right_cm', 5, 2)->nullable();
            $table->decimal('calf_cm', 5, 2)->nullable();
            $table->text('notes')->nullable();
        });

        // 18. Progress Photos Table
        Schema::create('progress_photos', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('measurement_id')->constrained('body_measurement')->cascadeOnDelete();
            $table->enum('pose_type', ['FRONT', 'BACK', 'LEFT', 'RIGHT']);
            $table->string('photo_path', 255);
            $table->timestamp('taken_at')->useCurrent();
        });

        // 19. Nutrition Targets Table
        Schema::create('nutrition_targets', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 50)->comment('Día de Entreno, Fin de Semana Flexible...');
            $table->decimal('calories_kcal', 6, 1);
            $table->decimal('protein_g', 5, 1);
            $table->decimal('carbs_g', 5, 1);
            $table->decimal('fat_g', 5, 1);
            $table->boolean('is_default')->default(false);
        });

        // 20. Food Items Table
        Schema::create('food_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete()->comment('NULL = Open Food Facts; Con ID = Alimento custom');
            $table->string('barcode', 60)->unique()->nullable();
            $table->string('name', 200);
            $table->string('brand', 100)->nullable();
            $table->decimal('serving_size_g', 6, 2)->default(100);
            $table->decimal('calories_per_100g', 6, 1);
            $table->decimal('protein_per_100g', 5, 1);
            $table->decimal('carbs_per_100g', 5, 1);
            $table->decimal('fat_per_100g', 5, 1);
            $table->decimal('fiber_per_100g', 5, 1)->default(0);
            $table->timestamp('created_at')->useCurrent();
        });

        // 21. Recipes Table
        Schema::create('recipes', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 150)->comment('Ej: Desayuno Habitual, Batido Post-Entreno');
            $table->text('description')->nullable();
        });

        // 22. Recipe Items Table
        Schema::create('recipe_items', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('recipe_id')->constrained('recipes')->cascadeOnDelete();
            $table->foreignUuid('food_item_id')->constrained('food_items')->cascadeOnDelete();
            $table->decimal('amount_in_grams', 6, 2);
        });

        // 23. Daily Food Logs Table
        Schema::create('daily_food_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUuid('food_item_id')->nullable()->constrained('food_items')->cascadeOnDelete();
            $table->foreignUuid('recipe_id')->nullable()->constrained('recipes')->cascadeOnDelete();
            $table->date('log_date');
            $table->string('meal_type', 20)->comment('breakfast, lunch, dinner, snack');
            $table->decimal('amount_in_grams', 6, 2);
            $table->decimal('calculated_calories', 6, 1);
            $table->decimal('calculated_protein', 5, 1);
            $table->decimal('calculated_carbs', 5, 1);
            $table->decimal('calculated_fat', 5, 1);
            $table->timestamp('created_at')->useCurrent();
        });

        // 24. Daily Habit Logs Table
        Schema::create('daily_habit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('log_date');
            $table->decimal('sleep_hours', 4, 2)->nullable();
            $table->integer('sleep_quality')->nullable()->comment('Escala 1 al 5');
            $table->integer('energy_level')->nullable()->comment('Escala 1 al 5');
            $table->integer('water_intake_ml')->default(0);
            $table->integer('water_target_ml')->default(3000);

            $table->unique(['user_id', 'log_date'], 'daily_habit_logs_index_1');
        });

        // 25. Supplements Table
        Schema::create('supplements', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('name', 100)->comment('Ej: Creatina Monohidratada, Omega 3');
            $table->string('target_dosage', 50)->nullable()->comment('Ej: 5g, 2 cápsulas');
        });

        // 26. Daily Supplement Logs Table
        Schema::create('daily_supplement_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('daily_habit_log_id')->constrained('daily_habit_logs')->cascadeOnDelete();
            $table->foreignUuid('supplement_id')->constrained('supplements')->cascadeOnDelete();
            $table->boolean('is_taken')->default(false);
            $table->timestamp('taken_at')->nullable();
        });

        // 27. Joint Discomfort Logs Table
        Schema::create('joint_discomfort_logs', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->foreignUuid('daily_habit_log_id')->constrained('daily_habit_logs')->cascadeOnDelete();
            $table->string('joint_area', 50)->comment('shoulder_left, knee_right, lumbar...');
            $table->integer('pain_intensity')->comment('Escala 1 al 10');
            $table->string('notes', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('joint_discomfort_logs');
        Schema::dropIfExists('daily_supplement_logs');
        Schema::dropIfExists('supplements');
        Schema::dropIfExists('daily_habit_logs');
        Schema::dropIfExists('daily_food_logs');
        Schema::dropIfExists('recipe_items');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('food_items');
        Schema::dropIfExists('nutrition_targets');
        Schema::dropIfExists('progress_photos');
        Schema::dropIfExists('body_measurement');
        Schema::dropIfExists('workout_log_sets');
        Schema::dropIfExists('workout_logs');
        Schema::dropIfExists('exercise_substitutes');
        Schema::dropIfExists('exercise_muscles');
        Schema::dropIfExists('exercise_equipments');
        Schema::dropIfExists('exercise_body_parts');
        Schema::dropIfExists('equipments');
        Schema::dropIfExists('muscles');
        Schema::dropIfExists('body_parts');
        Schema::dropIfExists('workout_sets');
        Schema::dropIfExists('workout_exercises');
        Schema::dropIfExists('exercises');
        Schema::dropIfExists('routine_workouts');
        Schema::dropIfExists('workout');
        
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['current_routine']);
        });
        
        Schema::dropIfExists('routine');
        Schema::dropIfExists('users');
    }
};
