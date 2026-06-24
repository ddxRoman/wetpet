{{--
    Переключатель между организациями/объектами пользователя.
    Показывается только если у пользователя больше одного объекта.

    Ожидает: $allUserEntities (коллекция), $entityId, $type (текущий открытый объект)
--}}
@if(isset($allUserEntities) && $allUserEntities->count() > 1)


<style>
    .owner-tabs-wrap {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
        padding: 10px;
    }
    .owner-tabs-scroll {
        display: flex;
        gap: 8px;
        overflow-x: auto;
        scrollbar-width: thin;
        padding-bottom: 2px;
    }
    .owner-tab {
        display: flex;
        align-items: center;
        gap: 8px;
        flex-shrink: 0;
        padding: 10px 16px;
        border-radius: 12px;
        text-decoration: none;
        color: #4b5563;
        background: #f3f4f6;
        font-size: 14px;
        font-weight: 500;
        white-space: nowrap;
        transition: all .15s ease;
        border: 1px solid transparent;
    }
    .owner-tab:hover {
        background: #e9eaee;
        color: #111827;
    }
    .owner-tab--active {
        background: #2563eb;
        color: #fff;
        box-shadow: 0 2px 8px rgba(37,99,235,.25);
    }
    .owner-tab--active:hover {
        background: #1d4ed8;
        color: #fff;
    }
    .owner-tab--pending:not(.owner-tab--active) {
        background: #fff8e6;
        border-color: #f5d98c;
        color: #92660a;
    }
    .owner-tab--pending:not(.owner-tab--active):hover {
        background: #fdf0cc;
    }
    .owner-tab__icon { font-size: 16px; }
    .owner-tab__badge {
        font-size: 11px;
        font-weight: 600;
        padding: 2px 8px;
        border-radius: 20px;
        line-height: 1.4;
    }
    .owner-tab:not(.owner-tab--active) .owner-tab__badge--ok {
        background: #dcfce7;
        color: #15803d;
    }
    .owner-tab--active .owner-tab__badge--ok {
        background: rgba(255,255,255,.25);
        color: #fff;
    }
    .owner-tab__badge--wait {
        background: #fef3c7;
        color: #92400e;
    }
    .owner-tab--active .owner-tab__badge--wait {
        background: rgba(255,255,255,.3);
        color: #fff;
    }

    @media (max-width: 576px) {
        .owner-tab { padding: 8px 12px; font-size: 13px; }
        .owner-tab__badge--wait { font-size: 10px; }
    }
</style>
@endif
