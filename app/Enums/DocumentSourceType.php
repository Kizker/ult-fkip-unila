<?php

namespace App\Enums;

enum DocumentSourceType: string
{
    case MAIN_DOCX_TEMPLATE = 'MAIN_DOCX_TEMPLATE';
    case REQUEST_PPTX = 'REQUEST_PPTX';
}

