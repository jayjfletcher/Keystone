<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keystone_locales', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code', 20)->unique();
            $table->json('labels')->nullable();
            $table->timestamps();
        });

        Schema::create('keystone_channels', function (Blueprint $table): void {
            $table->ulid('id')->primary();
            $table->string('code', 100)->unique();
            $table->json('labels')->nullable();
            // ISO 4217 codes the channel sells in.
            $table->json('currencies')->nullable();
            // The root category of the branch this channel sells.
            $table->foreignUlid('category_tree_id')->nullable()->constrained('keystone_categories')->restrictOnDelete();
            $table->timestamps();
        });

        Schema::create('keystone_channel_locale', function (Blueprint $table): void {
            $table->foreignUlid('channel_id')->constrained('keystone_channels')->cascadeOnDelete();
            // Restrict: a locale a channel publishes in cannot vanish under it.
            $table->foreignUlid('locale_id')->constrained('keystone_locales')->restrictOnDelete();

            $table->primary(['channel_id', 'locale_id']);
            $table->index('locale_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keystone_channel_locale');
        Schema::dropIfExists('keystone_channels');
        Schema::dropIfExists('keystone_locales');
    }
};
