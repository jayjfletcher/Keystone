<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('showroom_family_variants', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('family_id')->constrained('showroom_families')->cascadeOnDelete();
            $table->string('code', 100)->unique();
            $table->json('labels')->nullable();
            // 1 or 2: how many levels of axes sit between the root model and a variant.
            $table->unsignedTinyInteger('levels');
            $table->timestamps();
        });

        // Which attributes are set at which variant level, and which of them
        // are axes. Attributes of the family on no level are common: set on
        // the root product model.
        Schema::create('showroom_family_variant_attributes', function (Blueprint $table): void {
            $table->foreignUlid('family_variant_id')->constrained('showroom_family_variants')->cascadeOnDelete();
            $table->foreignUlid('attribute_id')->constrained('showroom_attributes')->cascadeOnDelete();
            $table->unsignedTinyInteger('level');
            $table->boolean('is_axis')->default(false);

            $table->primary(['family_variant_id', 'attribute_id']);
            $table->index('attribute_id');
        });

        Schema::create('showroom_product_models', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code', 191)->unique();
            // Restrict: a family variant in use keeps its models' structure.
            $table->foreignUlid('family_variant_id')->constrained('showroom_family_variants')->restrictOnDelete();
            $table->foreignUlid('parent_id')->nullable()->constrained('showroom_product_models')->cascadeOnDelete();
            // {attribute: {channel|<all_channels>: {locale|<all_locales>: data}}}
            $table->json('values')->nullable();
            $table->timestamps();

            $table->index('parent_id');
        });

        Schema::create('showroom_products', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('identifier', 191)->unique();
            $table->foreignUlid('family_id')->nullable()->constrained('showroom_families')->restrictOnDelete();
            $table->foreignUlid('parent_id')->nullable()->constrained('showroom_product_models')->cascadeOnDelete();
            $table->boolean('enabled')->default(true);
            $table->json('values')->nullable();
            $table->timestamps();

            $table->index('family_id');
            $table->index('parent_id');
            $table->index(['enabled', 'updated_at']);
        });

        // JSON cannot carry a unique index, so each value of a unique
        // attribute is mirrored here as a hash the database can enforce.
        Schema::create('showroom_product_unique_values', function (Blueprint $table): void {
            $table->foreignUlid('attribute_id')->constrained('showroom_attributes')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained('showroom_products')->cascadeOnDelete();
            $table->char('value_hash', 64);

            $table->primary(['attribute_id', 'product_id']);
            $table->unique(['attribute_id', 'value_hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('showroom_product_unique_values');
        Schema::dropIfExists('showroom_products');
        Schema::dropIfExists('showroom_product_models');
        Schema::dropIfExists('showroom_family_variant_attributes');
        Schema::dropIfExists('showroom_family_variants');
    }
};
