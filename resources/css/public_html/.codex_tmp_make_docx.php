<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$r = App\Models\Request::with([
  'service.templates',
  'service.placeholders',
  'service.fields',
  'service.signers',
  'signoffs.decider.unit.parent',
  'signoffs.signerUser.unit.parent',
  'data',
  'currentUnit',
  'student.unit.parent',
  'attachments',
])->find(8);

$tpl = $r->service->templates->firstWhere('type', App\Enums\ServiceTemplateType::MAIN_DOCX);
$svc = app(App\Services\Documents\DocumentAssemblerService::class);
$ref = new ReflectionClass($svc);
$mVals = $ref->getMethod('buildPlaceholderValues');
$mVals->setAccessible(true);
$vals = $mVals->invoke($svc, $r);
$mDocx = $ref->getMethod('buildDocxFromTemplate');
$mDocx->setAccessible(true);
$disk = config('ult.private_disk');
$docxPath = $mDocx->invoke($svc, $disk, $tpl->file_path, $vals, [], $r);
echo $docxPath . PHP_EOL;
