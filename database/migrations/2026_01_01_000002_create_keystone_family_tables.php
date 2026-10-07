<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keystone_families', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code', 100)->unique();
            $table->json('labels')->nullable();
            // Restrict: an attribute that labels a family's products is refused
            // deletion rather than leaving the family without a label.
            $table->foreignUlid('label_attribute_id')->nullable()
                ->constrained('keystone_attributes')->restrictOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('keystone_family_attributes', function (Blueprint $table): void {
            $table->foreignUlid('family_id')->constrained('keystone_families')->cascadeOnDelete();
            $table->foreignUlid('attribute_id')->constrained('keystone_attributes')->cascadeOnDelete();
            // Global for now; becomes per channel once channels land.
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('sort_order')->default(0);

            $table->primary(['family_id', 'attribute_id']);
            $table->index('attribute_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keystone_family_attributes');
        Schema::dropIfExists('keystone_families');
    }
};
