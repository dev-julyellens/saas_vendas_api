<?php

use App\Core\Enums\ConsignmentOperationType;
use App\Core\Enums\ConsignmentStatus;
use App\Core\Database\Concerns\CreatesPostgresEnums;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    use CreatesPostgresEnums;

    public function up(): void
    {
        $this->migrateConsignmentStatusEnum();
        $this->upgradeConsignmentsTable();
        $this->upgradeConsignmentItemsTable();
        $this->createConsignmentOperationsTable();
    }

    private function migrateConsignmentStatusEnum(): void
    {
        if ($this->isPostgres())
        {
            $this->createPostgresEnum('consignment_status_v2', ConsignmentStatus::values());

            DB::statement('ALTER TABLE consignments ADD COLUMN status_new consignment_status_v2');

            DB::statement("
                UPDATE consignments SET status_new = CASE status::text
                    WHEN 'draft' THEN 'aberto'::consignment_status_v2
                    WHEN 'active' THEN 'aberto'::consignment_status_v2
                    WHEN 'partial_return' THEN 'parcial'::consignment_status_v2
                    WHEN 'closed' THEN 'fechado'::consignment_status_v2
                    WHEN 'cancelled' THEN 'fechado'::consignment_status_v2
                    ELSE 'aberto'::consignment_status_v2
                END
            ");

            DB::statement('ALTER TABLE consignments DROP COLUMN status');
            DB::statement('ALTER TABLE consignments RENAME COLUMN status_new TO status');
            DB::statement('ALTER TABLE consignments ALTER COLUMN status SET NOT NULL');
            DB::statement('ALTER TABLE consignments ALTER COLUMN status SET DEFAULT \'aberto\'');
            $this->dropPostgresEnum('consignment_status');
            DB::statement('ALTER TYPE consignment_status_v2 RENAME TO consignment_status');
        }
        else
        {
            Schema::table('consignments', function (Blueprint $table)
            {
                $table->string('status_tmp', 30)->default(ConsignmentStatus::Aberto->value)->after('code');
            });

            DB::table('consignments')->update(['status_tmp' => ConsignmentStatus::Aberto->value]);

            Schema::table('consignments', function (Blueprint $table)
            {
                $table->dropColumn('status');
            });

            Schema::table('consignments', function (Blueprint $table)
            {
                $table->enum('status', ConsignmentStatus::values())
                    ->default(ConsignmentStatus::Aberto->value)
                    ->after('code');
            });

            DB::statement('UPDATE consignments SET status = status_tmp');
            Schema::table('consignments', function (Blueprint $table)
            {
                $table->dropColumn('status_tmp');
            });
        }
    }

    private function upgradeConsignmentsTable(): void
    {
        Schema::table('consignments', function (Blueprint $table)
        {
            $table->timestamp('dispatched_at')->nullable()->after('consigned_at');
            $table->timestamp('collected_at')->nullable()->after('dispatched_at');
        });
    }

    private function upgradeConsignmentItemsTable(): void
    {
        Schema::table('consignment_items', function (Blueprint $table)
        {
            $table->unsignedInteger('quantity_sold')->default(0)->after('quantity');
            $table->unsignedInteger('quantity_returned')->default(0)->after('quantity_sold');
            $table->unsignedInteger('quantity_lost')->default(0)->after('quantity_returned');
            $table->unsignedInteger('quantity_damaged')->default(0)->after('quantity_lost');
            $table->unsignedInteger('quantity_divergence')->default(0)->after('quantity_damaged');
        });

        $this->addCheckConstraint(
            'consignment_items',
            'consignment_items_quantities_valid',
            'quantity_sold + quantity_returned + quantity_lost + quantity_damaged + quantity_divergence <= quantity'
        );
    }

    private function createConsignmentOperationsTable(): void
    {
        $this->createPostgresEnum('consignment_operation_type', ConsignmentOperationType::values());

        Schema::create('consignment_operations', function (Blueprint $table)
        {
            $table->uuid('id')->primary();
            $table->foreignUuid('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignUuid('consignment_id')->constrained('consignments')->cascadeOnDelete();
            $table->foreignUuid('consignment_item_id')->nullable()->constrained('consignment_items')->nullOnDelete();
            $table->foreignUuid('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->unsignedInteger('quantity');
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignUuid('performed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['company_id', 'consignment_id', 'created_at']);
            $table->index(['consignment_id', 'consignment_item_id']);

            $table->foreign(['company_id', 'consignment_id'])
                ->references(['company_id', 'id'])
                ->on('consignments')
                ->cascadeOnDelete();
        });

        if ($this->isPostgres())
        {
            DB::statement('ALTER TABLE consignment_operations ADD COLUMN operation_type consignment_operation_type NOT NULL');
        }
        else
        {
            Schema::table('consignment_operations', function (Blueprint $table)
            {
                $table->enum('operation_type', ConsignmentOperationType::values())->after('product_id');
            });
        }

        $this->addCheckConstraint('consignment_operations', 'consignment_operations_quantity_non_negative', 'quantity >= 0');

        Schema::table('stock_movements', function (Blueprint $table)
        {
            $table->foreignUuid('consignment_operation_id')->nullable()->after('reference_id')
                ->constrained('consignment_operations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table)
        {
            $table->dropConstrainedForeignId('consignment_operation_id');
        });

        Schema::dropIfExists('consignment_operations');
        $this->dropPostgresEnum('consignment_operation_type');

        Schema::table('consignment_items', function (Blueprint $table)
        {
            $table->dropColumn([
                'quantity_sold',
                'quantity_returned',
                'quantity_lost',
                'quantity_damaged',
                'quantity_divergence',
            ]);
        });

        Schema::table('consignments', function (Blueprint $table)
        {
            $table->dropColumn(['dispatched_at', 'collected_at']);
        });
    }
};
