<?php

namespace App\Http\Controllers\Signer;

use App\Enums\RequestStatus;
use App\Http\Controllers\Controller;
use App\Models\Request as UltRequest;
use App\Services\Documents\DocumentSignerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DocumentSignerController extends Controller
{
    public function __construct(private readonly DocumentSignerService $signer) {}

    public function inbox(Request $request)
    {
        $user = $request->user();

        $items = UltRequest::query()
            ->where('current_status', RequestStatus::IN_SIGNING)
            ->whereNotNull('current_signer_order_index')
            ->with(['service.signers', 'signoffs', 'student.unit.parent', 'currentUnit.parent'])
            ->orderByDesc('updated_at')
            ->orderByDesc('id')
            ->paginate(15);

        // Filter in-memory by active signer role to keep query simple and DB-agnostic.
        $items->setCollection(
            $items->getCollection()->filter(function (UltRequest $r) use ($user) {
                $r->loadMissing(['service.signers', 'signoffs']);

                $idx = (int) $r->current_signer_order_index;
                $signoff = $r->signoffs?->firstWhere('order_index', $idx);
                if ($signoff?->signer_user_id) {
                    return (int) $signoff->signer_user_id === (int) $user->id;
                }

                $signer = $r->service?->signers?->firstWhere('order_index', $idx);
                return $signer && $user->matchesSignerRole((string) $signer->role);
            })->values()
        );

        return view('signer.requests.inbox', compact('items'));
    }

    public function show(UltRequest $request)
    {
        Gate::authorize('view', $request);

        $request->load([
            'service.placeholders',
            'service.signers',
            'service.templates',
            'service.fields',
            'signoffs',
            'student',
            'currentUnit',
            'data',
            'attachments',
        ]);
        return view('signer.requests.show', ['req' => $request]);
    }

    public function decide(UltRequest $request, Request $http)
    {
        Gate::authorize('view', $request);

        $data = $http->validate([
            'decision' => ['required','in:APPROVE,REVISION,REJECT'],
            'note' => ['nullable','string','max:2000'],
            'signature_file' => ['nullable','file','max:'.(1024 * 5)], // hard cap 5MB; per-signer cap enforced in service
        ]);

        $this->signer->decide($request, $http->user(), $data['decision'], $data['note'] ?? null, $http->file('signature_file'));

        return redirect()->route('signer.requests.inbox')->with('status', 'Keputusan disimpan.');
    }
}
