@if($entity->is_other_locality)
    <span class="badge bg-secondary bg-opacity-75 position-absolute top-0 end-0 m-2"
          style="z-index: 10; font-size: 11px;"
          title="{{ $entity->city }}">Другой населенный пункт</span>
@endif
