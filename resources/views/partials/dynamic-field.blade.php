@php
    $name = 'fields[' . $field->id . ']';
    $multi = in_array($field->type, ['MULTI_SELECT', 'CHECKBOX']);
    $rules = [];
    if ($field->min_length) $rules[] = 'min:' . $field->min_length;
    if ($field->max_length) $rules[] = 'max:' . $field->max_length;
    $old = old('fields.' . $field->id, $multi ? [] : null);
@endphp

<div>
    <label class="block text-sm font-medium text-gray-700">
        {{ $field->label }}
        @if ($field->is_required) <span class="text-red-500">*</span> @endif
    </label>

    @if ($field->description)
        <p class="text-xs text-gray-500 mt-0.5">{{ $field->description }}</p>
    @endif

    @switch($field->type)
        @case('TEXTAREA')
            <textarea name="{{ $name }}" rows="4" {{ $field->is_required ? 'required' : '' }} @if($field->max_length) maxlength="{{ $field->max_length }}" @endif
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">{{ $old }}</textarea>
            @break

        @case('SELECT')
            <select name="{{ $name }}" {{ $field->is_required ? 'required' : '' }}
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                <option value="">Select…</option>
                @foreach ($field->options as $option)
                    <option value="{{ $option->value }}" {{ $old === $option->value ? 'selected' : '' }}>{{ $option->label }}</option>
                @endforeach
            </select>
            @break

        @case('RADIO')
            <div class="mt-1 space-y-1.5">
                @foreach ($field->options as $option)
                    <label class="flex items-center text-sm text-gray-700">
                        <input type="radio" name="{{ $name }}" value="{{ $option->value }}" {{ $old === $option->value ? 'checked' : '' }} {{ $field->is_required ? 'required' : '' }}
                            class="rounded-full border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2">{{ $option->label }}</span>
                    </label>
                @endforeach
            </div>
            @break

        @case('CHECKBOX')
            <div class="mt-1 space-y-1.5">
                @foreach ($field->options as $option)
                    <label class="flex items-center text-sm text-gray-700">
                        <input type="checkbox" name="{{ $name }}[]" value="{{ $option->value }}"
                            @if(is_array($old) && in_array($option->value, $old, true)) checked @endif
                            class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        <span class="ml-2">{{ $option->label }}</span>
                    </label>
                @endforeach
            </div>
            @break

        @case('MULTI_SELECT')
            <select name="{{ $name }}[]" multiple {{ $field->is_required ? 'required' : '' }}
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
                @foreach ($field->options as $option)
                    <option value="{{ $option->value }}" @if(is_array($old) && in_array($option->value, $old, true)) selected @endif>{{ $option->label }}</option>
                @endforeach
            </select>
            @break

        @case('DATE')
            <input type="date" name="{{ $name }}" value="{{ $old }}" {{ $field->is_required ? 'required' : '' }}
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            @break

        @case('FILE')
            <input type="file" name="{{ $name }}" {{ $field->is_required ? 'required' : '' }}
                class="mt-1 block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-blue-50 file:px-3 file:py-2 file:text-blue-700 file:text-sm">
            @break

        @case('NUMBER')
            <input type="number" step="any" name="{{ $name }}" value="{{ $old }}" {{ $field->is_required ? 'required' : '' }}
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            @break

        @case('EMAIL')
            <input type="email" name="{{ $name }}" value="{{ $old }}" {{ $field->is_required ? 'required' : '' }}
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            @break

        @case('PHONE')
            <input type="tel" name="{{ $name }}" value="{{ $old }}" {{ $field->is_required ? 'required' : '' }}
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            @break

        @case('URL')
            <input type="url" name="{{ $name }}" value="{{ $old }}" {{ $field->is_required ? 'required' : '' }}
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
            @break

        @case('IMEI')
        @case('SERIAL_NUMBER')
            <input type="text" name="{{ $name }}" value="{{ $old }}" {{ $field->is_required ? 'required' : '' }}
                pattern="{{ $field->validation_regex ?? '[0-9A-Za-z]{6,32}' }}" @if($field->max_length) maxlength="{{ $field->max_length }}" @endif
                placeholder="{{ $field->placeholder }}"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm font-mono">
            @break

        @default
            <input type="text" name="{{ $name }}" value="{{ $old }}" {{ $field->is_required ? 'required' : '' }}
                placeholder="{{ $field->placeholder }}" @if($field->max_length) maxlength="{{ $field->max_length }}" @endif
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm">
    @endswitch
</div>
