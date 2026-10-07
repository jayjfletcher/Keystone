<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // A root category is a tree; each tree is independent of the others.
        Schema::create('keystone_categories', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('parent_id')->nullable()->constrained('keystone_categories')->restrictOnDelete();
            $table->string('code', 100)->unique();
            $table->json('labels')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            // "/root/…/self/": a whole branch is one prefix match.
            $table->text('path');
            $table->unsignedInteger('depth')->default(0);
            $table->timestamps();

            $table->index(['parent_id', 'sort_order']);
        });

        Schema::create('keystone_category_product', function (Blueprint $table): void {
            $table->foreignUlid('category_id')->constrained('keystone_categories')->cascadeOnDelete();
            $table->foreignUlid('product_id')->constrained('keystone_products')->cascadeOnDelete();

            $table->primary(['category_id', 'product_id']);
            $table->index('product_id');
        });

        Schema::create('keystone_category_product_model', function (Blueprint $table): void {
            $table->foreignUlid('category_id')->constrained('keystone_categories')->cascadeOnDelete();
            $table->foreignUlid('product_model_id')->constrained('keystone_product_models')->cascadeOnDelete();

            $table->primary(['category_id', 'product_model_id']);
            $table->index('product_model_id');
        });
    }

    public function down(): void
    {
        // Categories reference each other, which a restricted drop would trip over.
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::dropIfExists('keystone_category_product_model');
            Schema::dropIfExists('keystone_category_product');
            Schema::dropIfExists('keystone_categories');
        });
    }
};
