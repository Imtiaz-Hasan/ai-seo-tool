<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Per-user LLM settings: choose a provider and supply your own key/model. Keys are
 * stored encrypted (User cast). Leaving a key field blank keeps the saved one.
 */
class SettingsController extends Controller
{
    public function edit(Request $request): View
    {
        return view('settings.edit', [
            'user' => $request->user(),
            'demoMode' => config('content.demo_mode'),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'llm_provider' => ['nullable', 'in:openai,anthropic,ollama'],
            'openai_key' => ['nullable', 'string', 'max:300'],
            'openai_model' => ['nullable', 'string', 'max:100'],
            'anthropic_key' => ['nullable', 'string', 'max:300'],
            'anthropic_model' => ['nullable', 'string', 'max:100'],
            'ollama_base_url' => ['nullable', 'url', 'max:200'],
            'ollama_model' => ['nullable', 'string', 'max:100'],
        ]);

        $user = $request->user();

        // A blank key field means "leave the stored key as-is" (so you don't have
        // to paste it again every save).
        foreach (['openai_key', 'anthropic_key'] as $keyField) {
            if (blank($data[$keyField] ?? null)) {
                unset($data[$keyField]);
            }
        }

        $user->fill($data);
        $user->llm_provider = $data['llm_provider'] ?? null;
        $user->save();

        return back()->with('status', 'Settings saved.');
    }
}
