<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keystone_owner_types', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code', 100)->unique();
            $table->json('labels')->nullable();
            // False: any owner type may be the parent. True: only those in
            // keystone_owner_type_parents, which may be none.
            $table->boolean('restricts_parents')->default(false);
            $table->boolean('can_be_root')->default(true);
            $table->boolean('owns_products')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('keystone_owner_type_parents', function (Blueprint $table): void {
            $table->foreignUlid('owner_type_id')->constrained('keystone_owner_types')->cascadeOnDelete();
            $table->foreignUlid('parent_type_id')->constrained('keystone_owner_types')->cascadeOnDelete();

            $table->primary(['owner_type_id', 'parent_type_id']);
        });

        Schema::create('keystone_owners', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->foreignUlid('owner_type_id')->constrained('keystone_owner_types')->restrictOnDelete();
            $table->foreignUlid('parent_id')->nullable()->constrained('keystone_owners')->restrictOnDelete();
            $table->string('code', 191)->unique();
            $table->json('labels')->nullable();
            // "/root/…/self/": everything beneath an owner is one prefix match.
            $table->text('path');
            $table->unsignedInteger('depth')->default(0);
            $table->timestamps();

            $table->index('parent_id');
        });

        Schema::table('keystone_products', function (Blueprint $table): void {
            $table->foreignUlid('owner_id')->nullable()->after('parent_id')->constrained('keystone_owners')->restrictOnDelete();
        });

        Schema::table('keystone_product_models', function (Blueprint $table): void {
            $table->foreignUlid('owner_id')->nullable()->after('parent_id')->constrained('keystone_owners')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        // Owners reference each other, which a restricted drop would trip over.
        Schema::withoutForeignKeyConstraints(function (): void {
            Schema::table('keystone_product_models', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('owner_id');
            });

            Schema::table('keystone_products', function (Blueprint $table): void {
                $table->dropConstrainedForeignId('owner_id');
            });

            Schema::dropIfExists('keystone_owners');
            Schema::dropIfExists('keystone_owner_type_parents');
            Schema::dropIfExists('keystone_owner_types');
        });
    }
};
