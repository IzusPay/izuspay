<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webhook_auto_configs', function (Blueprint $table) {
            $table->id();
            $table->string('scope')->default('global'); // global | association
            $table->foreignId('association_id')->nullable()->constrained('associations')->onDelete('cascade');
            $table->unsignedInteger('skip_every_n_sales')->nullable();
            $table->unsignedInteger('revenue_threshold_cents')->nullable();
            $table->unsignedInteger('reserve_amount_cents')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->index(['scope', 'association_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webhook_auto_configs');
    }
};
