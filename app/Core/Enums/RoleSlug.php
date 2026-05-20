<?php

declare(strict_types=1);

namespace App\Core\Enums;

/**
 * Perfis padrão do sistema — alinhados ao RBAC seed.
 */
enum RoleSlug: string
{
    case Empresa = 'empresa';
    case Representante = 'representante';
    case Revendedor = 'revendedor';
    case Operacional = 'operacional';

    /** @return list<string> */
    public static function tenantRoles(): array
    {
        return array_column(self::cases(), 'value');
    }
}
