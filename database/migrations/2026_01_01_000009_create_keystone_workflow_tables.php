<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Null: a required attribute is required on every channel.
        Schema::table('keystone_family_attributes', function (Blueprint $table): void {
            $table->json('required_channels')->nullable();
        });

        Schema::table('keystone_products', function (Blueprint $table): void {
            $table->string('status', 32)->default('draft')->index();
            // The version storefronts read; editing never touches it.
            $table->unsignedInteger('published_version')->nullable();
            $table->timestamp('published_at')->nullable();
        });

        // Derived from values and family requirements, stored so search can
        // filter on it.
        Schema::create('keystone_product_completeness', function (Blueprint $table): void {
            $table->foreignUlid('product_id')->constrained('keystone_products')->cascadeOnDelete();
            $table->foreignUlid('channel_id')->constrained('keystone_channels')->cascadeOnDelete();
            $table->foreignUlid('locale_id')->constrained('keystone_locales')->cascadeOnDelete();
            $table->unsignedInteger('required');
            $table->unsignedInteger('missing');
            $table->unsignedTinyInteger('ratio');
            $table->json('missing_attributes')->nullable();
            $table->timestamp('updated_at')->nullable();

            $table->primary(['product_id', 'channel_id', 'locale_id']);
            $table->index(['channel_id', 'locale_id', 'ratio']);
        });

        Schema::create('keystone_versions', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('versionable_type', 64);
            $table->ulid('versionable_id');
            $table->unsignedInteger('version');
            $table->string('action', 32);
            $table->json('snapshot');
            $table->json('changes')->nullable();
            $table->text('comment')->nullable();
            $table->string('author_type')->nullable();
            $table->string('author_id', 64)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->unique(['versionable_type', 'versionable_id', 'version']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keystone_versions');
        Schema::dropIfExists('keystone_product_completeness');

        Schema::table('keystone_products', function (Blueprint $table): void {
            $table->dropIndex(['status']);
            $table->dropColumn(['status', 'published_version', 'published_at']);
        });

        Schema::table('keystone_family_attributes', function (Blueprint $table): void {
            $table->dropColumn('required_channels');
        });
    }
};
