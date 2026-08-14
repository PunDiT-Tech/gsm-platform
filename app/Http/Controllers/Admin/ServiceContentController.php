<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\ServiceField;
use App\Models\ServiceFieldOption;
use App\Models\ServiceInformationBlock;
use App\Models\ServiceLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ServiceContentController extends Controller
{
    // ---- Dynamic fields (Stage 7) ----

    public function storeField(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'label' => ['required', 'string', 'max:255'],
            'internal_name' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:TEXT,TEXTAREA,NUMBER,EMAIL,PHONE,IMEI,SERIAL_NUMBER,SELECT,MULTI_SELECT,RADIO,CHECKBOX,DATE,FILE,URL'],
            'placeholder' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'is_required' => ['boolean'],
            'validation_regex' => ['nullable', 'string', 'max:255'],
            'min_length' => ['nullable', 'integer', 'min:0'],
            'max_length' => ['nullable', 'integer', 'min:1'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['is_required'] = $request->boolean('is_required');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $field = $service->fields()->create($data);

        if ($field->isSelectType() && $request->filled('options')) {
            foreach ($request->input('options', []) as $i => $option) {
                if ($option === null || $option === '') {
                    continue;
                }
                ServiceFieldOption::create([
                    'service_field_id' => $field->id,
                    'label' => $option,
                    'value' => Str::slug($option),
                    'sort_order' => $i,
                ]);
            }
        }

        return redirect()->route('admin.services.edit', $service)->with('status', 'Field added.')->withFragment('fields');
    }

    public function destroyField(Service $service, ServiceField $field): RedirectResponse
    {
        $field->delete();

        return redirect()->route('admin.services.edit', $service)->with('status', 'Field deleted.')->withFragment('fields');
    }

    // ---- Information blocks (Stage 8) ----

    public function storeBlock(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:INFORMATION,NOTICE,WARNING,INSTRUCTION,FAQ,LINK,DOWNLOAD,IMAGE,VIDEO'],
            'title' => ['nullable', 'string', 'max:255'],
            'content' => ['nullable', 'string'],
            'url' => ['nullable', 'url'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;

        $service->informationBlocks()->create($data);

        return redirect()->route('admin.services.edit', $service)->with('status', 'Block added.')->withFragment('blocks');
    }

    public function destroyBlock(Service $service, ServiceInformationBlock $block): RedirectResponse
    {
        $block->delete();

        return redirect()->route('admin.services.edit', $service)->with('status', 'Block deleted.')->withFragment('blocks');
    }

    // ---- Links (Stage 9) ----

    public function storeLink(Request $request, Service $service): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
            'url' => ['required', 'url', 'not_regex:/^javascript:/i'],
            'open_new_tab' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ]);

        $data['open_new_tab'] = $request->boolean('open_new_tab');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $service->links()->create($data);

        return redirect()->route('admin.services.edit', $service)->with('status', 'Link added.')->withFragment('links');
    }

    public function destroyLink(Service $service, ServiceLink $link): RedirectResponse
    {
        $link->delete();

        return redirect()->route('admin.services.edit', $service)->with('status', 'Link deleted.')->withFragment('links');
    }
}