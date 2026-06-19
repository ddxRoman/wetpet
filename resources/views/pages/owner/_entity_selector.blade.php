@if(isset($allUserEntities) && $allUserEntities->count() > 1)
<div class="card border-0 shadow-sm rounded-4 p-3 mb-4 bg-white">
    <div class="d-flex align-items-center mb-2">
        <span class="fs-5 me-2">🔄</span>
        <h6 class="fw-bold mb-0">Переключение между объектами (Всего: {{ $allUserEntities->count() }}):</h6>
    </div>
    
    <div class="d-flex flex-wrap gap-2" style="max-height: 160px; overflow-y: auto; padding: 2px;">
        @foreach($allUserEntities as $item)
            @php
                // Проверяем текущий активный объект на странице
                // На no-access мы можем проверять по переданному item из pendingOwners
                $isCurrent = false;
                if (isset($entityId) && isset($type)) {
                    $isCurrent = ($type === $item['type'] && $entityId == $item['id']);
                } elseif (isset($pendingOwners) && $pendingOwners->count() === 1) {
                    $firstPending = $pendingOwners->first();
                    $isCurrent = ($item['type'] === $firstPending['entity_type'] && $item['id'] == ($firstPending['owner_row']->clinic_id ?? $firstPending['owner_row']->organization_id ?? $firstPending['owner_row']->doctor_id ?? $firstPending['owner_row']->specialist_id));
                }
                
                // Ссылка всегда ведет на роут этого объекта
                $url = route('owner.' . $item['type'], $item['id']);
            @endphp

            <a href="{{ $url }}" 
               class="btn btn-sm d-flex align-items-center gap-2 rounded-pill px-3 transition 
                      @if($isCurrent) 
                          btn-primary fw-bold shadow-sm
                      @elseif(!$item['is_confirmed']) 
                          btn-outline-warning text-dark border-dashed
                      @else 
                          btn-light text-secondary
                      @endif">
               
                <span>{{ $item['icon'] }}</span>
                <span>{{ $item['name'] }}</span>
                
                @if(!$item['is_confirmed'])
                    <span class="badge bg-warning text-dark pb-1" style="font-size: 10px;">⏳ Нужны документы</span>
                @endif
            </a>
        @endforeach
    </div>
</div>
@endif