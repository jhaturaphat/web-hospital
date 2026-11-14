@if($paginator->hasPages())
<nav class="pagination" aria-label="เปลี่ยนหน้ารายการข่าว">
    @if($paginator->previousPageUrl())<a class="button button-outline" href="{{ $paginator->previousPageUrl() }}">← ก่อนหน้า</a>@endif
    <span>หน้า {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}</span>
    @if($paginator->nextPageUrl())<a class="button button-outline" href="{{ $paginator->nextPageUrl() }}">ถัดไป →</a>@endif
</nav>
@endif