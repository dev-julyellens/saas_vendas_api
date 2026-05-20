<?php

use App\Core\Enums\CommissionStatus;
use App\Core\Enums\ConsignmentStatus;
use App\Core\Enums\FinancialTransactionStatus;
use App\Core\Enums\FinancialTransactionType;
use App\Core\Enums\ReturnStatus;
use App\Core\Enums\SaleStatus;
use App\Core\Enums\StockMovementType;
use App\Core\Database\Concerns\CreatesPostgresEnums;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Domínio de consignação — estoque EXCLUSIVAMENTE via stock_movements.
 * Todas as tabelas possuem company_id para isolamento multi-tenant.
 */
return new class extends Migration
{
    use CreatesPostgresEnums;

    public function up(): void
    {
        $this->createDomainEnums();

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
            $table->index(['company_id', 'is_active']);
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
            $table->index(['company_id', 'representative_id']);
            $table->index(['company_id', 'is_active']);
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
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'reseller_id']);
            $table->index(['company_id', 'document']);
        });

        Schema::create('product_categories', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->uuid('parent_id')->nullable();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'slug']);
            $table->index(['company_id', 'parent_id']);
        });

        // FK auto-referenciada após a tabela existir (PostgreSQL exige PK única já criada).
        Schema::table('product_categories', function (Blueprint $table)
        {
            $table->foreign('parent_id')
                ->references('id')
                ->on('product_categories')
                ->nullOnDelete();
        });

        Schema::create('products', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('product_category_id')->nullable()->constrained('product_categories')->nullOnDelete();
            $table->string('sku');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('unit_price', 15, 2);
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'sku']);
            $table->index(['company_id', 'product_category_id']);
            $table->index(['company_id', 'is_active']);
            $table->unique(['company_id', 'id']);
        });

        $this->createStockMovementsTable();
        $this->createConsignmentsTables();
        $this->createSalesTables();
        $this->createReturnsTables();
        $this->createCommissionsAndFinancialTables();
        $this->createAuditLogsTable();
    }

    private function createDomainEnums(): void
    {
        $this->createPostgresEnum('stock_movement_type', StockMovementType::values());
        $this->createPostgresEnum('consignment_status', ConsignmentStatus::values());
        $this->createPostgresEnum('sale_status', SaleStatus::values());
        $this->createPostgresEnum('return_status', ReturnStatus::values());
        $this->createPostgresEnum('commission_status', CommissionStatus::values());
        $this->createPostgresEnum('financial_transaction_type', FinancialTransactionType::values());
        $this->createPostgresEnum('financial_transaction_status', FinancialTransactionStatus::values());
    }

    private function createStockMovementsTable(): void
    {
        Schema::create('stock_movements', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            /** null = estoque da empresa; preenchido = estoque no revendedor */
            $table->foreignUuid('reseller_id')->nullable()->constrained('resellers')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_cost', 15, 2)->nullable();
            $table->nullableUuidMorphs('reference');
            $table->text('notes')->nullable();
            $table->timestamp('occurred_at');
            $table->foreignUuid('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'product_id', 'occurred_at']);
            $table->index(['company_id', 'reseller_id', 'product_id']);
            $table->index(['company_id', 'occurred_at']);
        });

        if ($this->isPostgres())
        {
            DB::statement('ALTER TABLE stock_movements ADD COLUMN movement_type stock_movement_type NOT NULL');
        }
        else
        {
            Schema::table('stock_movements', function (Blueprint $table)
            {
                $table->enum('movement_type', StockMovementType::values())->after('reseller_id');
            });
        }

        $this->addCheckConstraint('stock_movements', 'stock_movements_quantity_positive', 'quantity > 0');
    }

    private function createConsignmentsTables(): void
    {
        Schema::create('consignments', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('reseller_id')->constrained('resellers')->cascadeOnDelete();
            $table->foreignUuid('representative_id')->nullable()->constrained('representatives')->nullOnDelete();
            $table->string('code', 50);
            $table->date('consigned_at');
            $table->date('expected_return_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'reseller_id', 'consigned_at']);
        });

        if ($this->isPostgres())
        {
            DB::statement("ALTER TABLE consignments ADD COLUMN status consignment_status NOT NULL DEFAULT 'aberto'");
        }
        else
        {
            Schema::table('consignments', function (Blueprint $table)
            {
                $table->enum('status', ConsignmentStatus::values())
                    ->default(ConsignmentStatus::Aberto->value)
                    ->after('code');
            });
        }

        Schema::table('consignments', function (Blueprint $table)
        {
            $table->unique(['company_id', 'id']);
        });

        Schema::create('consignment_items', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('consignment_id')->constrained('consignments')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'consignment_id', 'product_id']);
            $table->index(['company_id', 'product_id']);

            $table->foreign(['company_id', 'consignment_id'])
                ->references(['company_id', 'id'])
                ->on('consignments')
                ->cascadeOnDelete();
        });

        Schema::table('consignment_items', function (Blueprint $table)
        {
            $table->foreign(['company_id', 'product_id'])
                ->references(['company_id', 'id'])
                ->on('products')
                ->restrictOnDelete();
        });

        $this->addCheckConstraint('consignment_items', 'consignment_items_quantity_positive', 'quantity > 0');
    }

    private function createSalesTables(): void
    {
        Schema::create('sales', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('reseller_id')->constrained('resellers')->cascadeOnDelete();
            $table->foreignUuid('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignUuid('representative_id')->nullable()->constrained('representatives')->nullOnDelete();
            $table->foreignUuid('consignment_id')->nullable()->constrained('consignments')->nullOnDelete();
            $table->string('code', 50);
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->timestamp('sold_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'reseller_id', 'sold_at']);
            $table->index(['company_id', 'consignment_id']);
        });

        if ($this->isPostgres())
        {
            DB::statement("ALTER TABLE sales ADD COLUMN status sale_status NOT NULL DEFAULT 'pending'");
        }
        else
        {
            Schema::table('sales', function (Blueprint $table)
            {
                $table->enum('status', SaleStatus::values())
                    ->default(SaleStatus::Pending->value)
                    ->after('total');
            });
        }

        Schema::table('sales', function (Blueprint $table)
        {
            $table->unique(['company_id', 'id']);
        });

        Schema::create('sale_items', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('consignment_item_id')->nullable()->constrained('consignment_items')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2);
            $table->decimal('total', 15, 2);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'sale_id']);
            $table->index(['company_id', 'product_id']);

            $table->foreign(['company_id', 'sale_id'])
                ->references(['company_id', 'id'])
                ->on('sales')
                ->cascadeOnDelete();
        });

        $this->addCheckConstraint('sale_items', 'sale_items_quantity_positive', 'quantity > 0');
    }

    private function createReturnsTables(): void
    {
        Schema::create('returns', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('reseller_id')->constrained('resellers')->cascadeOnDelete();
            $table->foreignUuid('sale_id')->nullable()->constrained('sales')->nullOnDelete();
            $table->foreignUuid('consignment_id')->nullable()->constrained('consignments')->nullOnDelete();
            $table->string('code', 50);
            $table->text('reason')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['company_id', 'code']);
            $table->index(['company_id', 'reseller_id']);
        });

        if ($this->isPostgres())
        {
            DB::statement("ALTER TABLE returns ADD COLUMN status return_status NOT NULL DEFAULT 'pending'");
        }
        else
        {
            Schema::table('returns', function (Blueprint $table)
            {
                $table->enum('status', ReturnStatus::values())
                    ->default(ReturnStatus::Pending->value)
                    ->after('code');
            });
        }

        Schema::table('returns', function (Blueprint $table)
        {
            $table->unique(['company_id', 'id']);
        });

        Schema::create('return_items', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('return_id')->constrained('returns')->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('sale_item_id')->nullable()->constrained('sale_items')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 15, 2)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'return_id']);

            $table->foreign(['company_id', 'return_id'])
                ->references(['company_id', 'id'])
                ->on('returns')
                ->cascadeOnDelete();
        });

        $this->addCheckConstraint('return_items', 'return_items_quantity_positive', 'quantity > 0');
    }

    private function createCommissionsAndFinancialTables(): void
    {
        Schema::create('commissions', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignUuid('representative_id')->constrained('representatives')->cascadeOnDelete();
            $table->decimal('base_amount', 15, 2);
            $table->decimal('rate', 8, 4);
            $table->decimal('amount', 15, 2);
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'representative_id']);
            $table->index(['company_id', 'sale_id']);

            $table->foreign(['company_id', 'sale_id'])
                ->references(['company_id', 'id'])
                ->on('sales')
                ->cascadeOnDelete();
        });

        if ($this->isPostgres())
        {
            DB::statement("ALTER TABLE commissions ADD COLUMN status commission_status NOT NULL DEFAULT 'pending'");
        }
        else
        {
            Schema::table('commissions', function (Blueprint $table)
            {
                $table->enum('status', CommissionStatus::values())
                    ->default(CommissionStatus::Pending->value)
                    ->after('amount');
            });
        }

        Schema::create('financial_transactions', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->uuidMorphs('reference');
            $table->string('category', 50);
            $table->decimal('amount', 15, 2);
            $table->date('due_date')->nullable();
            $table->date('paid_at')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['company_id', 'category']);
            $table->index(['company_id', 'due_date']);
        });

        if ($this->isPostgres())
        {
            DB::statement("ALTER TABLE financial_transactions ADD COLUMN type financial_transaction_type NOT NULL");
            DB::statement("ALTER TABLE financial_transactions ADD COLUMN status financial_transaction_status NOT NULL DEFAULT 'pending'");
        }
        else
        {
            Schema::table('financial_transactions', function (Blueprint $table)
            {
                $table->enum('type', FinancialTransactionType::values())->after('reference_id');
                $table->enum('status', FinancialTransactionStatus::values())
                    ->default(FinancialTransactionStatus::Pending->value)
                    ->after('amount');
            });
        }

        if ($this->isPostgres())
        {
            DB::statement('CREATE INDEX IF NOT EXISTS financial_transactions_company_type_status_idx ON financial_transactions (company_id, type, status)');
        }
        else
        {
            Schema::table('financial_transactions', function (Blueprint $table)
            {
                $table->index(['company_id', 'type', 'status']);
            });
        }
    }

    private function createAuditLogsTable(): void
    {
        Schema::create('audit_logs', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignUuid('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->uuidMorphs('auditable');
            $table->string('event', 30);
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['company_id', 'auditable_type', 'auditable_id']);
            $table->index(['company_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('financial_transactions');
        Schema::dropIfExists('commissions');
        Schema::dropIfExists('return_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
        Schema::dropIfExists('consignment_items');
        Schema::dropIfExists('consignments');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('products');
        Schema::dropIfExists('product_categories');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('resellers');
        Schema::dropIfExists('representatives');

        $this->dropPostgresEnum('financial_transaction_status');
        $this->dropPostgresEnum('financial_transaction_type');
        $this->dropPostgresEnum('commission_status');
        $this->dropPostgresEnum('return_status');
        $this->dropPostgresEnum('sale_status');
        $this->dropPostgresEnum('consignment_status');
        $this->dropPostgresEnum('stock_movement_type');
    }
};
