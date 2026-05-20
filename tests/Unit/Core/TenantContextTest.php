<?php

namespace Tests\Unit\Core;

use App\Core\Tenant\TenantContext;
use PHPUnit\Framework\TestCase;

class TenantContextTest extends TestCase
{
    protected function tearDown(): void
    {
        TenantContext::reset();
        parent::tearDown();
    }

    public function test_sets_and_reads_company_id(): void
    {
        TenantContext::setCompanyId('company-uuid');
        $this->assertSame('company-uuid', TenantContext::companyId());
        $this->assertTrue(TenantContext::hasCompany());
    }

    public function test_without_scope_bypasses_flag(): void
    {
        TenantContext::setCompanyId('1');

        $bypassed = TenantContext::withoutScope(fn() => TenantContext::shouldBypassScope());

        $this->assertTrue($bypassed);
        $this->assertFalse(TenantContext::shouldBypassScope());
    }
}
