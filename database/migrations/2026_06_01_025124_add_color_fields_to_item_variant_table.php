<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('item_variant', function (Blueprint $table) {
            if (Schema::hasColumn('item_variant', 'color')) {
                $table->renameColumn('color', 'value');
            }

            if (!Schema::hasColumn('item_variant', 'variant_id')) {
                $table->foreignId('variant_id')
                      ->nullable()
                      ->after('price_override')
                      ->constrained('variants')
                      ->onDelete('set null');
            }

            if (!Schema::hasColumn('item_variant', 'type')) {
                $table->enum('type', ['size', 'color'])
                      ->default('size')
                      ->after('variant_id');
            }

            if (!Schema::hasColumn('item_variant', 'hexcode')) {
                $table->string('hexcode', 7)
                      ->nullable()
                      ->comment('Hex code: #RRGGBB')
                      ->after('type');
            }

            if (!Schema::hasColumn('item_variant', 'image_path')) {
                $table->string('image_path')
                      ->nullable()
                      ->comment('Storage path for swatch image')
                      ->after('hexcode');
            }

            if (!Schema::hasColumn('item_variant', 'use_image')) {
                $table->boolean('use_image')
                      ->default(false)
                      ->after('image_path');
            }

            if (!Schema::hasColumn('item_variant', 'sku')) {
                $table->string('sku')
                      ->nullable()
                      ->after('use_image');
            }
            
            if (Schema::hasColumn('item_variant', 'price_override')) {
                $table->decimal('price_override', 10, 2)
                      ->nullable()
                      ->change()
                      ->after('sku');
            } else {
                $table->decimal('price_override', 10, 2)
                      ->nullable()
                      ->after('sku');
            }

            if (!Schema::hasColumn('item_variant', 'stock_quantity')) {
                $table->integer('stock_quantity')
                      ->default(0)
                      ->after('price_override');
            }

            if (!Schema::hasColumn('item_variant', 'display_order')) {
                $table->integer('display_order')
                      ->default(0)
                      ->after('stock_quantity');
            }
            
            if (!Schema::hasColumn('item_variant', 'is_active')) {
                $table->boolean('is_active')
                      ->default(true)
                      ->after('display_order');
            }

            $table->index(['item_id', 'type'], 'idx_item_variant_lookup');
            $table->index(['type', 'is_active'], 'idx_type_active');
            $table->index('hexcode', 'idx_hexcode');
        });
    }

    /**
     * Reverse the migrations.
     * ⚠️ WARNING: Rolling back renames & type changes in production is risky.
     * In production, prefer forward-only migrations. This is provided for local/dev rollback.
     */
    public function down()
    {
        Schema::table('item_variant', function (Blueprint $table) {
            $table->dropIndex('idx_item_variant_lookup');
            $table->dropIndex('idx_type_active');
            $table->dropIndex('idx_hexcode');
            $table->dropForeign('item_variant_variant_id_foreign');

            $table->dropColumn([
                'variant_id',
                'hexcode',
                'image_path',
                'use_image',
                'is_active'
            ]);

            if (Schema::hasColumn('item_variant', 'price_override')) {
                $table->integer('price_override')
                      ->nullable()
                      ->change();
            }

            $table->dropColumn(['sku', 'stock_quantity', 'display_order']);

            if (Schema::hasColumn('item_variant', 'type')) {
                $table->dropColumn('type');
            }

            if (Schema::hasColumn('item_variant', 'value')) {
                $table->renameColumn('value', 'color');
            }
        });
    }
};