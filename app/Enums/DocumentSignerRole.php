<?php

namespace App\Enums;

/**
 * Dynamic signer roles configured per service (document module).
 * Stored as role-name strings to match Spatie roles.
 */
enum DocumentSignerRole: string
{
    case KETUA_ORG = 'KETUA_ORG';
    case SEKRETARIS_ORG = 'SEKRETARIS_ORG';
    case SEKJUR = 'SEKJUR';
    case KAPRODI = 'KAPRODI';
    case KAJUR = 'KAJUR';
    case STAFF_ULT = 'Staf ULT';
    case DEKAN = 'DEKAN';
    case WD_AKADEMIK = 'WD_AKADEMIK';
    case WD_UMUM = 'WD_UMUM';
    case WD_KEMAHASISWAAN = 'WD_KEMAHASISWAAN';
}
