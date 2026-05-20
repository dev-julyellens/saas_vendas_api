<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Domínio de consignação — todas as tabelas com company_id para isolamento multi-tenant.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('representatives', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('document', 20);
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->decimal('commission_rate', 8, 4)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['company_id', 'document']);
        });

        Schema::create('resellers', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('representative_id')->nullable()->constrained('representatives')->nullOnDelete();
            $table->string('name');
            $table->string('document', 20);
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['company_id', 'document']);
        });

        Schema::create('customers', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('reseller_id')->nullable()->constrained('resellers')->nullOnDelete();
            $table->string('name');
            $table->string('document', 20)->nullable();
            $table->string('email')->nullable();
            $table->string('phone', 20)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('products', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->string('sku');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['company_id', 'sku']);
        });

        Schema::create('consignment_stocks', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('reseller_id')->constrained('resellers')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(0);
            $table->unsignedInteger('quantity_sold')->default(0);
            $table->unsignedInteger('quantity_returned')->default(0);
            $table->date('consigned_at');
            $table->date('expected_return_at')->nullable();
            $table->string('status', 30)->default('active');
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['company_id', 'reseller_id', 'product_id', 'consigned_at'], 'consignment_unique');
        });

        Schema::create('sales', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('reseller_id')->constrained('resellers')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignUuid('representative_id')->nullable()->constrained('representatives')->nullOnDelete();
            $table->string('code', 50);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2);
            $table->string('status', 30)->default('pending');
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('sale_items', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
        });

        Schema::create('return_orders', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('reseller_id')->constrained('resellers')->cascadeOnDelete();
            $table->foreignUuid('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->string('code', 50);
            $table->string('status', 30)->default('pending');
            $table->text('reason')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['company_id', 'code']);
        });

        Schema::create('return_items', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('return_order_id')->constrained('return_orders')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->timestamps();
        });

        Schema::create('commissions', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignUuid('representative_id')->constrained('representatives')->cascadeOnDelete();
            $table->decimal('base_amount', 15, 2);
            $table->decimal('rate', 8, 4);
            $table->decimal('amount', 15, 2);
            $table->string('status', 30)->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('financial_transactions', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->uuidMorphs('reference');
            $table->string('type', 30);
            $table->string('category', 50);
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->string('status', 30)->default('pending');
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['company_id', 'type', 'status']);
        });

        Schema::create('audit_logs', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('auditable_type');
            $table->string('auditable_id');
            $table->string('event', 30);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['company_id', 'auditable_type', 'auditable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('return_orders');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('consignment_stocks');
        Schema::dropIfExists('products');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('resellers');
        Schema::dropIfExists('representatives');
    }
};
