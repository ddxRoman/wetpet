{{--
    Выбор, к каким филиалам применить настройку: ко всем своим или только к выбранным.
    Ожидает: $group (logo|schedule|phone|email|website), $manageable (филиалы, которыми управляет пользователь)
--}}
<div class="border rounded-3 p-3 mb-3 bg-light" data-branch-target>
    <div class="fw-semibold mb-2" style="font-size:14px;">Применить к</div>

    <div class="form-check">
        <input class="form-check-input" type="radio" name="target" value="all"
               id="target-all-{{ $group }}" checked>
        <label class="form-check-label" for="target-all-{{ $group }}">
            Всем моим филиалам <span class="text-muted">({{ $manageable->count() }})</span>
        </label>
    </div>

    <div class="form-check">
        <input class="form-check-input" type="radio" name="target" value="selected"
               id="target-selected-{{ $group }}">
        <label class="form-check-label" for="target-selected-{{ $group }}">Выбрать филиалы</label>
    </div>

    <div class="mt-2 d-none" data-branch-list style="max-height:220px;overflow:auto;">
        @foreach($manageable as $b)
            <div class="form-check">
                <input class="form-check-input" type="checkbox" name="branch_ids[]" value="{{ $b['key'] }}"
                       id="b-{{ $group }}-{{ $b['type'] }}-{{ $b['id'] }}">
                <label class="form-check-label" for="b-{{ $group }}-{{ $b['type'] }}-{{ $b['id'] }}">
                    {{ $b['name'] }}
                    <span class="text-muted">— {{ $b['city'] }}{{ $b['address'] !== '' ? ', ' . $b['address'] : '' }}</span>
                </label>
            </div>
        @endforeach
    </div>
</div>
