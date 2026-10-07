{{--
    Другие филиалы сети из того же города.
    Ожидает: $entity (Clinic | Organization). Если запись не входит в сеть или других филиалов в городе нет — ничего не выводит.
--}}
@php
    $chainBranches = $entity->chain_id ? optional($entity->chain)->branchesInCity($entity) : collect();
@endphp

@if($chainBranches && $chainBranches->isNotEmpty())
    <div class="mb-4 mt-5">
        <h2 class="fs-5 fw-semibold mb-1">Другие филиалы в городе {{ $entity->city }}</h2>
        <div class="text-muted small mb-3">{{ $entity->chain->name }}</div>

        <div class="list-group shadow-sm">
            @foreach($chainBranches as $branch)
                <a href="{{ $branch['url'] }}" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center gap-3">
                    <span>
                        <span class="fw-semibold">{{ $branch['name'] }}</span>
                        @if($branch['address'] !== '')
                            <span class="text-muted"> — {{ $branch['address'] }}</span>
                        @endif
                    </span>
                    <span class="badge text-bg-light border flex-shrink-0">{{ $branch['type'] }}</span>
                </a>
            @endforeach
        </div>
    </div>
@endif
