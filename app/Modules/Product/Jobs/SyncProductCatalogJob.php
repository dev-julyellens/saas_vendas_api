<?php

namespace App\Modules\Product\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Job exemplo — integração futura com ERP/marketplace.
 */
class SyncProductCatalogJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $productId,
        public string $companyId,
    )
    {
    }

    public function handle(): void
    {
        Log::channel('integrations')->info('sync.product', [
            'product_id' => $this->productId,
            'company_id' => $this->companyId,
        ]);
    }
}
